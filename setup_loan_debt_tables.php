<?php
include("./includes/db_conn.php");

// Create loans table
$sql = "CREATE TABLE IF NOT EXISTS loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    loan_amount DECIMAL(10,2) NOT NULL,
    payment_per_month DECIMAL(10,2) NOT NULL,
    remaining_amount DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT,
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES reg_users(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Loans table created successfully<br>";
} else {
    echo "Error creating loans table: " . mysqli_error($conn) . "<br>";
}

// Create debts table
$sql = "CREATE TABLE IF NOT EXISTS debts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    debt_amount DECIMAL(10,2) NOT NULL,
    remaining_amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    due_date DATE,
    status ENUM('active', 'paid') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES reg_users(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Debts table created successfully<br>";
} else {
    echo "Error creating debts table: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?> 