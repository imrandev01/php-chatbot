<?php
// Example DB config template — do NOT commit secrets to the repo.
// Copy this file to dbconfig/config.php and fill values on your server.

declare(strict_types=1);

$dbHost = '127.0.0.1';
$dbName = 'chatbot';
$dbUser = 'root';
$dbPass = '';

$appEnv = 'production';

# Optional: provide an OpenAI API key to enable AI-generated replies for unmatched queries.
# Do NOT commit your real key to the repo. Set this value on the server in dbconfig/config.php instead.
# Example:
# define('OPENAI_API_KEY', 'sk-...');

if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);

try {
    $db = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    throw new PDOException('Database connection failed. Check configuration.');
}
