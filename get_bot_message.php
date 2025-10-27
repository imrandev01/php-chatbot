<?php
date_default_timezone_set('Asia/Dhaka');
require_once 'dbconfig/config.php';

// Safe lowercase helper: uses mb_strtolower if available, otherwise strtolower
if (!function_exists('safe_lower')) {
	function safe_lower(string $s): string
	{
		if (function_exists('mb_strtolower')) {
			return mb_strtolower($s);
		}
		return strtolower($s);
	}
}

// Normalize and safely read the user input
$userInput = isset($_POST['txt']) ? trim((string)$_POST['txt']) : '';
$content = "Sorry not be able to understand you";

if ($userInput !== '') {
	// Save the user message (use prepared statements and execute)
	$added_on = date('Y-m-d H:i:s');
	$insertUser = $db->prepare('INSERT INTO message(message, added_on, type) VALUES(:msg, :added_on, :type)');
	$insertUser->execute([':msg' => $userInput, ':added_on' => $added_on, ':type' => 'user']);

	// Fetch hints and try to match. The `question` column stores alternatives separated by '||'.
	$stmt = $db->query("SELECT question, reply FROM chatbot_hints");
	$found = false;
	foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
		$alternatives = preg_split('/\|\|/', $row['question']);
		foreach ($alternatives as $alt) {
			$alt = trim($alt);
			if ($alt === '') continue;
			// Lowercase in a safe way
			$altLower = safe_lower($alt);
			$inputLower = safe_lower($userInput);

			// Exact case-insensitive match
			if ($altLower === $inputLower) {
				$content = $row['reply'];
				$found = true;
				break 2;
			}
			// Fallback: substring match (helps short queries)
			if (stripos($altLower, $inputLower) !== false) {
				$content = $row['reply'];
				$found = true;
				break 2;
			}
		}
	}

	// If no match found, optionally call an AI API (OpenAI) when configured
	if ($content === "Sorry not be able to understand you") {
		$openaiKey = getenv('OPENAI_API_KEY') ?: null;
		if ($openaiKey) {
			// Build a prompt with some context (you can customize system message)
			$system = "You are a helpful assistant for a college chatbot. Reply concisely and helpfully.";
			$userMessage = $userInput ?: 'Hello';
			$payload = [
				'model' => 'gpt-3.5-turbo',
				'messages' => [
					['role' => 'system', 'content' => $system],
					['role' => 'user', 'content' => $userMessage],
				],
				'temperature' => 0.5,
				'max_tokens' => 250,
			];

			// call OpenAI
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
					if ($aiReply !== '') {
						$content = $aiReply;
					}
				}
			}
		}
	}
}
/*
********************
NO Need to do this
**$db->execute(); 
**$db->closeCursor();
*********************
*/

// Save bot reply
$added_on = date('Y-m-d H:i:s');
$insertBot = $db->prepare('INSERT INTO message(message, added_on, type) VALUES(:msg, :added_on, :type)');
$insertBot->execute([':msg' => $content, ':added_on' => $added_on, ':type' => 'bot']);

/*
********************
** NO Need to do this
** $db->execute();  
** $db->closeCursor();
**********************
*/

echo $content;
echo " ";
?>


<!--
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<style>

	<link href="style.css" rel="stylesheet">
</style>
<a href="#"><small><input name="invalid"  type="button" id="admin_btn" value="Invalid?"></small></a>

<body>

</body>
</html>-->
