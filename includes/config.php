<?php
// Load sensitive information from environment variables
$host = getenv('DB_HOST') ?: 'localhost'; // Use localhost if not set
$user = getenv('DB_USER') ?: 'root'; // Use root if not set (replace with proper username in production)
$pass = getenv('DB_PASS') ?: ''; // Replace with actual password
$dbname = getenv('DB_NAME') ?: 'job_portal'; // Replace with actual DB name

// Create a connection to the database using MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // Log the error (do not display it to the user)
    error_log("Connection failed: " . $conn->connect_error);
    // Display a generic error message to the user
    die("Sorry, we're having trouble connecting to the database right now.");
}

// If you're using SSL encryption for MySQL, you can enable it like this (Optional):
// $conn->ssl_set(NULL, NULL, "/path/to/ca-cert.pem", NULL, NULL);  // Provide path to SSL certs if required
// $conn->real_connect($host, $user, $pass, $dbname);

// Use UTF-8 for consistent character encoding (helps prevent SQL injection from encoding issues)
$conn->set_charset("utf8mb4");

// Additional optional: Enable MySQLi error reporting (in development mode)
if ($_SERVER['SERVER_NAME'] !== 'your-production-domain.com') {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}
?>
