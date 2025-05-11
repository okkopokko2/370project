<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS expense";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "expense");

// Create table
$sql = "CREATE TABLE IF NOT EXISTS reg_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(50) NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    user_pass VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?> 