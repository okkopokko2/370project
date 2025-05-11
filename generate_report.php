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
$report_type = $_GET['type'] ?? 'weekly';
$current_date = date('Y-m-d');

// Calculate date ranges
if($report_type == 'weekly') {
    $start_date = date('Y-m-d', strtotime('monday this week'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));
    $title = "Weekly Expense Report (" . date('M d', strtotime($start_date)) . " - " . date('M d', strtotime($end_date)) . ")";
} else {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
    $title = "Monthly Expense Report (" . date('F Y') . ")";
}

include("./includes/db_conn.php");

// Get total expenses
$sql = "SELECT 
            SUM(amount) as total_expense,
            COUNT(*) as transaction_count
        FROM transactions 
        WHERE user_id = ? 
        AND type = 'expense'
        AND transaction_date BETWEEN ? AND ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$totals = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get loan payments for the period
$sql = "SELECT 
            SUM(loan_amount - remaining_amount) as total_loan_paid,
            SUM(loan_amount / TIMESTAMPDIFF(MONTH, start_date, end_date)) as total_monthly_payment,
            COUNT(*) as loan_count
        FROM loans 
        WHERE user_id = ? 
        AND status = 'active'
        AND start_date <= ?
        AND end_date >= ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $end_date, $start_date);
mysqli_stmt_execute($stmt);
$loan_totals = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get expenses by category
$sql = "SELECT 
            c.name as category_name,
            SUM(t.amount) as total_amount,
            COUNT(*) as transaction_count
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? 
        AND t.type = 'expense'
        AND t.transaction_date BETWEEN ? AND ?
        GROUP BY c.id
        ORDER BY total_amount DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$category_expenses = mysqli_stmt_get_result($stmt);

// Get daily expenses
$sql = "SELECT 
            DATE(transaction_date) as date,
            SUM(amount) as total_amount,
            COUNT(*) as transaction_count
        FROM transactions 
        WHERE user_id = ? 
        AND type = 'expense'
        AND transaction_date BETWEEN ? AND ?
        GROUP BY DATE(transaction_date)
        ORDER BY date";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$daily_expenses = mysqli_stmt_get_result($stmt);

mysqli_close($conn);
?>

<div class="container">
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo $title; ?></h4>
                        <a href="dashboard.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Expenses</h5>
                                    <h3 class="card-text">৳<?php echo number_format($totals['total_expense'] ?? 0, 2); ?></h3>
                                    <p class="mb-0"><?php echo $totals['transaction_count'] ?? 0; ?> transactions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Loan Payments</h5>
                                    <h3 class="card-text">৳<?php echo number_format($loan_totals['total_loan_paid'] ?? 0, 2); ?></h3>
                                    <p class="mb-0"><?php echo $loan_totals['loan_count'] ?? 0; ?> active loans</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Monthly Loan Payment</h5>
                                    <h3 class="card-text">৳<?php echo number_format($loan_totals['total_monthly_payment'] ?? 0, 2); ?></h3>
                                    <p class="mb-0">Due this month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Expenses with Loans</h5>
                                    <h3 class="card-text">৳<?php echo number_format(($totals['total_expense'] ?? 0) + ($loan_totals['total_monthly_payment'] ?? 0), 2); ?></h3>
                                    <p class="mb-0">Including monthly payments</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Expenses by Category</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Amount</th>
                                            <th>Transactions</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($category = mysqli_fetch_assoc($category_expenses)) { 
                                            $percentage = (($totals['total_expense'] ?? 0) + ($loan_totals['total_monthly_payment'] ?? 0) > 0) ? 
                                                ($category['total_amount'] / (($totals['total_expense'] ?? 0) + ($loan_totals['total_monthly_payment'] ?? 0)) * 100) : 0;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                <td>৳<?php echo number_format($category['total_amount'], 2); ?></td>
                                                <td><?php echo $category['transaction_count']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-danger" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $percentage; ?>%"
                                                             aria-valuenow="<?php echo $percentage; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo number_format($percentage, 1); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if($loan_totals['total_monthly_payment'] > 0) { ?>
                                            <tr>
                                                <td>Monthly Loan Payments</td>
                                                <td>৳<?php echo number_format($loan_totals['total_monthly_payment'], 2); ?></td>
                                                <td><?php echo $loan_totals['loan_count']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-warning" 
                                                             role="progressbar" 
                                                             style="width: <?php echo ($loan_totals['total_monthly_payment'] / (($totals['total_expense'] ?? 0) + $loan_totals['total_monthly_payment']) * 100); ?>%"
                                                             aria-valuenow="<?php echo ($loan_totals['total_monthly_payment'] / (($totals['total_expense'] ?? 0) + $loan_totals['total_monthly_payment']) * 100); ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo number_format(($loan_totals['total_monthly_payment'] / (($totals['total_expense'] ?? 0) + $loan_totals['total_monthly_payment']) * 100), 1); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Breakdown -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Daily Expenses</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Transactions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($daily = mysqli_fetch_assoc($daily_expenses)) { ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($daily['date'])); ?></td>
                                                <td>৳<?php echo number_format($daily['total_amount'], 2); ?></td>
                                                <td><?php echo $daily['transaction_count']; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include("./includes/footer.php");
?> 