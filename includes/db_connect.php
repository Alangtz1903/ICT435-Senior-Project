
<?php
$config = require __DIR__ . '/db_config.local.php';

$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['port']
);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    exit("Database connection unavailable.");
}

$conn->set_charset("utf8mb4");
