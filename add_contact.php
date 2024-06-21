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

// Process POST data from AJAX request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lastName = htmlspecialchars($_POST['lastName'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');

    // Check if there's a client with the same name
    $client_id = null;
    $sql = "SELECT id FROM clients WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->bind_result($client_id);
    $stmt->fetch();
    $stmt->close();

    // If client_id is null, add a new client
    if (!$client_id) {
        $client_code = generateClientCode($name, $conn); // Generate unique client code
        $sql = "INSERT INTO clients (name, client_code) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $client_code);
        if ($stmt->execute()) {
            $client_id = $stmt->insert_id;
        } else {
            echo "Error adding client: " . $stmt->error;
            exit();
        }
        $stmt->close();
    }

    // Insert new contact linked to the client
    $sql = "INSERT INTO contacts (last_name, name, email, client_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $lastName, $name, $email, $client_id);
    if ($stmt->execute()) {
        echo "Contact added successfully";
    } else {
        echo "Error adding contact: " . $stmt->error;
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>