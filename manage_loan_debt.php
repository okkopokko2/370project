<?php
session_start();
include("./includes/header.php");
include("./includes/functions.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

include("./includes/db_conn.php");

switch($action) {
    case 'add_loan':
        $loan_amount = floatval($_POST['loan_amount']);
        $payment_per_month = floatval($_POST['payment_per_month']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $description = trim($_POST['description']);
        
        $sql = "INSERT INTO loans (user_id, loan_amount, payment_per_month, remaining_amount, start_date, end_date, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "idddsss", $user_id, $loan_amount, $payment_per_month, $loan_amount, $start_date, $end_date, $description);
        
        if(mysqli_stmt_execute($stmt)) {
            my_alert("success", "Loan added successfully!");
        } else {
            my_alert("danger", "Error adding loan");
        }
        break;

    case 'add_debt':
        $debt_amount = floatval($_POST['debt_amount']);
        $due_date = $_POST['due_date'];
        $description = trim($_POST['description']);
        
        $sql = "INSERT INTO debts (user_id, debt_amount, remaining_amount, due_date, description) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iddss", $user_id, $debt_amount, $debt_amount, $due_date, $description);
        
        if(mysqli_stmt_execute($stmt)) {
            my_alert("success", "Debt added successfully!");
        } else {
            my_alert("danger", "Error adding debt");
        }
        break;

    case 'make_loan_payment':
        $loan_id = intval($_POST['loan_id']);
        $payment_amount = floatval($_POST['payment_amount']);
        
        // Get current loan details
        $sql = "SELECT * FROM loans WHERE id = ? AND user_id = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $loan_id, $user_id);
        mysqli_stmt_execute($stmt);
        $loan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if($loan) {
            $new_remaining = $loan['remaining_amount'] - $payment_amount;
            $status = ($new_remaining <= 0) ? 'completed' : 'active';
            
            $sql = "UPDATE loans SET remaining_amount = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "dsi", $new_remaining, $status, $loan_id);
            
            if(mysqli_stmt_execute($stmt)) {
                my_alert("success", "Payment made successfully!");
            } else {
                my_alert("danger", "Error processing payment");
            }
        }
        break;

    case 'make_debt_payment':
        $debt_id = intval($_POST['debt_id']);
        $payment_amount = floatval($_POST['payment_amount']);
        
        // Get current debt details
        $sql = "SELECT * FROM debts WHERE id = ? AND user_id = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $debt_id, $user_id);
        mysqli_stmt_execute($stmt);
        $debt = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if($debt) {
            $new_remaining = $debt['remaining_amount'] - $payment_amount;
            $status = ($new_remaining <= 0) ? 'paid' : 'active';
            
            $sql = "UPDATE debts SET remaining_amount = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "dsi", $new_remaining, $status, $debt_id);
            
            if(mysqli_stmt_execute($stmt)) {
                my_alert("success", "Payment made successfully!");
            } else {
                my_alert("danger", "Error processing payment");
            }
        }
        break;
}

mysqli_close($conn);
header("Location: dashboard.php");
exit();
?> 