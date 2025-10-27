<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Dhaka');

// Return JSON error helper
function json_error($msg, $code = 500) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

// Check config
if (!file_exists(__DIR__ . '/../dbconfig/config.php')) {
    json_error('Missing server config (dbconfig/config.php).');
}
require_once __DIR__ . '/../dbconfig/config.php';

// Safe lowercase helper
if (!function_exists('safe_lower')) {
    function safe_lower(string $s): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($s);
        }
        return strtolower($s);
    }
}

$userInput = isset($_POST['txt']) ? trim((string)$_POST['txt']) : '';
$reply = "Sorry not be able to understand you";

if ($userInput !== '') {
    // persist user message
    $added_on = date('Y-m-d H:i:s');
    $insertUser = $db->prepare('INSERT INTO message(message, added_on, type) VALUES(:msg, :added_on, :type)');
    $insertUser->execute([':msg' => $userInput, ':added_on' => $added_on, ':type' => 'user']);

    $stmt = $db->query("SELECT question, reply FROM chatbot_hints");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $alternatives = preg_split('/\|\|/', $row['question']);
        foreach ($alternatives as $alt) {
            $alt = trim($alt);
            if ($alt === '') continue;
            $altLower = safe_lower($alt);
            $inputLower = safe_lower($userInput);
            if ($altLower === $inputLower || stripos($altLower, $inputLower) !== false) {
                $reply = $row['reply'];
                break 2;
            }
        }
    }

    // optional OpenAI fallback
    if ($reply === "Sorry not be able to understand you") {
        $openaiKey = getenv('OPENAI_API_KEY') ?: null;
        if ($openaiKey) {
            $payload = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for a college chatbot. Reply concisely.'],
                    ['role' => 'user', 'content' => $userInput],
                ],
                'temperature' => 0.5,
                'max_tokens' => 250,
            ];

            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $openaiKey,
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $resp = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($resp && !$err) {
                $j = json_decode($resp, true);
                if (isset($j['choices'][0]['message']['content'])) {
                    $aiReply = trim($j['choices'][0]['message']['content']);
                    if ($aiReply !== '') $reply = $aiReply;
                }
            }
        }
    }

    // persist bot reply
    $added_on = date('Y-m-d H:i:s');
    $insertBot = $db->prepare('INSERT INTO message(message, added_on, type) VALUES(:msg, :added_on, :type)');
    $insertBot->execute([':msg' => $reply, ':added_on' => $added_on, ':type' => 'bot']);
}

echo json_encode(['ok' => true, 'reply' => $reply]);

?>
