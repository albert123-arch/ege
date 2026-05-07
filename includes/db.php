<?php
require_once __DIR__ . '/config.php';

if (!class_exists('mysqli')) {
	http_response_code(500);
	die('Database connection error: mysqli extension is not enabled.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$mysqli->set_charset('utf8mb4');
} catch (Throwable $exception) {
	http_response_code(500);
	die('Database connection error: ' . $exception->getMessage());
}
// it goes here because of the error handling in the catch block, so that we can provide a more user-friendly message instead of a generic error page.