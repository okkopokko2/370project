<?php
session_start();
include("./includes/header.php");
include("./includes/functions.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Handle form submissions
if(isset($_POST['add_transaction'])) {
    include("./includes/db_conn.php");
    
    $amount = floatval($_POST['amount']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $transaction_date = $_POST['transaction_date'];
    $type = $_POST['type'];
    
    $sql = "INSERT INTO transactions (user_id, category_id, amount, description, transaction_date, type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iidsss", $user_id, $category_id, $amount, $description, $transaction_date, $type);
    
    if(mysqli_stmt_execute($stmt)) {
        my_alert("success", "Transaction added successfully!");
    } else {
        my_alert("danger", "Error adding transaction");
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Handle transaction updates
if(isset($_POST['update_transaction'])) {
    include("./includes/db_conn.php");
    
    $transaction_id = intval($_POST['transaction_id']);
    $amount = floatval($_POST['amount']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $transaction_date = $_POST['transaction_date'];
    $type = $_POST['type'];
    
    $sql = "UPDATE transactions 
            SET amount = ?, category_id = ?, description = ?, transaction_date = ?, type = ? 
            WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "dssssii", $amount, $category_id, $description, $transaction_date, $type, $transaction_id, $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        my_alert("success", "Transaction updated successfully!");
    } else {
        my_alert("danger", "Error updating transaction");
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Handle transaction deletion
if(isset($_POST['delete_transaction'])) {
    include("./includes/db_conn.php");
    
    $transaction_id = intval($_POST['transaction_id']);
    
    $sql = "DELETE FROM transactions WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $transaction_id, $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        my_alert("success", "Transaction deleted successfully!");
    } else {
        my_alert("danger", "Error deleting transaction");
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Get transactions for the current month
include("./includes/db_conn.php");
$current_month = date('Y-m');
$sql = "SELECT t.*, c.name as category_name 
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ? AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?
        ORDER BY t.transaction_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $user_id, $current_month);
mysqli_stmt_execute($stmt);
$transactions = mysqli_stmt_get_result($stmt);

// Calculate totals
$sql = "SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions 
        WHERE user_id = ? AND DATE_FORMAT(transaction_date, '%Y-%m') = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $user_id, $current_month);
mysqli_stmt_execute($stmt);
$totals = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get categories
$sql = "SELECT * FROM categories ORDER BY type, name";
$categories = mysqli_query($conn, $sql);

mysqli_close($conn);
?>

<div class="container">
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h4>
                        <a href="logout.php" class="btn btn-light">Logout</a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Income</h5>
                                    <h3 class="card-text">৳<?php echo number_format($totals['total_income'] ?? 0, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Expenses</h5>
                                    <h3 class="card-text">৳<?php echo number_format($totals['total_expense'] ?? 0, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Balance</h5>
                                    <h3 class="card-text">৳<?php echo number_format(($totals['total_income'] ?? 0) - ($totals['total_expense'] ?? 0), 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Reports</h5>
                                    <div class="d-grid gap-2">
                                        <a href="generate_report.php?type=weekly" class="btn btn-light btn-sm">
                                            <i class="fas fa-calendar-week me-2"></i>Weekly Report
                                        </a>
                                        <a href="generate_report.php?type=monthly" class="btn btn-light btn-sm">
                                            <i class="fas fa-calendar-alt me-2"></i>Monthly Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Transaction Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Add New Transaction</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-select" required>
                                        <option value="income">Income</option>
                                        <option value="expense">Expense</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select" required>
                                        <?php while($category = mysqli_fetch_assoc($categories)) { ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Amount</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="add_transaction" class="btn btn-primary">Add Transaction</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Transactions List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Transactions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($transaction = mysqli_fetch_assoc($transactions)) { ?>
                                            <tr>
                                                <td>
                                                    <span class="view-mode"><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></span>
                                                    <input type="date" class="form-control edit-mode" style="display: none;" 
                                                           value="<?php echo $transaction['transaction_date']; ?>">
                                                </td>
                                                <td>
                                                    <span class="view-mode"><?php echo htmlspecialchars($transaction['category_name']); ?></span>
                                                    <select class="form-select edit-mode" style="display: none;">
                                                        <?php 
                                                        mysqli_data_seek($categories, 0);
                                                        while($category = mysqli_fetch_assoc($categories)) { 
                                                            $selected = ($category['id'] == $transaction['category_id']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?php echo $category['id']; ?>" <?php echo $selected; ?>>
                                                                <?php echo htmlspecialchars($category['name']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="view-mode"><?php echo htmlspecialchars($transaction['description']); ?></span>
                                                    <input type="text" class="form-control edit-mode" style="display: none;" 
                                                           value="<?php echo htmlspecialchars($transaction['description']); ?>">
                                                </td>
                                                <td>
                                                    <span class="view-mode">৳<?php echo number_format($transaction['amount'], 2); ?></span>
                                                    <input type="number" step="0.01" class="form-control edit-mode" style="display: none;" 
                                                           value="<?php echo $transaction['amount']; ?>">
                                                </td>
                                                <td>
                                                    <span class="view-mode">
                                                        <span class="badge bg-<?php echo $transaction['type'] == 'income' ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($transaction['type']); ?>
                                                        </span>
                                                    </span>
                                                    <select class="form-select edit-mode" style="display: none;">
                                                        <option value="income" <?php echo $transaction['type'] == 'income' ? 'selected' : ''; ?>>Income</option>
                                                        <option value="expense" <?php echo $transaction['type'] == 'expense' ? 'selected' : ''; ?>>Expense</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="btn-group view-mode">
                                                        <button type="button" class="btn btn-sm btn-primary edit-btn" 
                                                                data-bs-toggle="tooltip" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                                            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                                            <button type="submit" name="delete_transaction" class="btn btn-sm btn-danger" 
                                                                    data-bs-toggle="tooltip" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="btn-group edit-mode" style="display: none;">
                                                        <button type="button" class="btn btn-sm btn-success save-btn" 
                                                                data-bs-toggle="tooltip" title="Save">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-secondary cancel-btn" 
                                                                data-bs-toggle="tooltip" title="Cancel">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
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

<!-- Edit Transaction Form (Hidden) -->
<form id="editTransactionForm" method="POST" style="display: none;">
    <input type="hidden" name="transaction_id" id="edit_transaction_id">
    <input type="hidden" name="amount" id="edit_amount">
    <input type="hidden" name="category_id" id="edit_category_id">
    <input type="hidden" name="description" id="edit_description">
    <input type="hidden" name="transaction_date" id="edit_transaction_date">
    <input type="hidden" name="type" id="edit_type">
    <input type="hidden" name="update_transaction" value="1">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit button click handler
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
            row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'block');
        });
    });

    // Cancel button click handler
    document.querySelectorAll('.cancel-btn').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'block');
            row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
        });
    });

    // Save button click handler
    document.querySelectorAll('.save-btn').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const form = document.getElementById('editTransactionForm');
            
            // Get values from edit mode inputs
            form.querySelector('#edit_transaction_id').value = row.dataset.transactionId;
            form.querySelector('#edit_amount').value = row.querySelector('.edit-mode[type="number"]').value;
            form.querySelector('#edit_category_id').value = row.querySelector('.edit-mode[type="select"]').value;
            form.querySelector('#edit_description').value = row.querySelector('.edit-mode[type="text"]').value;
            form.querySelector('#edit_transaction_date').value = row.querySelector('.edit-mode[type="date"]').value;
            form.querySelector('#edit_type').value = row.querySelector('.edit-mode[type="select"]:last-child').value;
            
            // Submit the form
            form.submit();
        });
    });
});
</script>

<?php
include("./includes/footer.php");
?> 