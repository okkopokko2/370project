<?php
include("./includes/db_conn.php");

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    user_id INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES reg_users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Categories table created successfully<br>";
} else {
    echo "Error creating categories table: " . mysqli_error($conn) . "<br>";
}

// Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    category_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES reg_users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Transactions table created successfully<br>";
} else {
    echo "Error creating transactions table: " . mysqli_error($conn) . "<br>";
}

// Insert default categories
$default_categories = [
    ['Salary', 'income'],
    ['Freelance', 'income'],
    ['Investments', 'income'],
    ['Food', 'expense'],
    ['Transportation', 'expense'],
    ['Housing', 'expense'],
    ['Utilities', 'expense'],
    ['Entertainment', 'expense'],
    ['Healthcare', 'expense'],
    ['Shopping', 'expense']
];

$stmt = mysqli_prepare($conn, "INSERT INTO categories (name, type) VALUES (?, ?)");
foreach ($default_categories as $category) {
    mysqli_stmt_bind_param($stmt, "ss", $category[0], $category[1]);
    mysqli_stmt_execute($stmt);
}
mysqli_stmt_close($stmt);

echo "Default categories added successfully";

mysqli_close($conn);
?> 