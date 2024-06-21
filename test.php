<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test PHP
echo "PHP is working";

// Database connection test
$servername = "localhost";
$username = "root";
$password = "Tino";
$dbname = "client_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";
$conn->close();
?>
