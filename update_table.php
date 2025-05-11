<?php
include("./includes/db_conn.php");

// SQL command to add email column
$sql = "ALTER TABLE reg_users 
        ADD COLUMN user_email VARCHAR(255) NOT NULL AFTER user_name";

// Execute the query
if (mysqli_query($conn, $sql)) {
    echo "Table updated successfully";
} else {
    echo "Error updating table: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 