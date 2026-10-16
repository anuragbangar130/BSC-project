<?php
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    http_response_code(500);
    die('Database configuration missing.');
}

$conn = pg_connect($databaseUrl);

if (!$conn) {
    http_response_code(500);
    die('Database connection failed.');
}
?>
