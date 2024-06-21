<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost";
$username = "root";  
$password = "Tino";  
$dbname = "client_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate a unique client code based on the client's name
function generateClientCode($name, $conn) {
    // Extract the first three alphabetic characters from the name
    preg_match_all('/[a-zA-Z]/', $name, $matches);
    $letters = strtoupper(implode('', array_slice($matches[0], 0, 3)));
    
    if (strlen($letters) < 3) {
        $letters = str_pad($letters, 3, 'X');
    }

    // Generate a unique three-digit number
    do {
        $number = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        $client_code = $letters . $number;
        $stmt = $conn->prepare("SELECT client_code FROM clients WHERE client_code = ?");
        $stmt->bind_param("s", $client_code);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);

    $stmt->close();
    return $client_code;
}

// Sanitize and validate form input
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$client_code = generateClientCode($name, $conn);
$num_contacts = (int)$_POST['num_contacts'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO clients (name, client_code, num_contacts) VALUES (?, ?, ?)");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ssi", $name, $client_code, $num_contacts);

// Execute the statement
if ($stmt->execute()) {
    echo "New client created successfully with client code: $client_code";
} else {
    echo "Error: " . $stmt->error;
}

// Close the connection
$stmt->close();
$conn->close();
?>
