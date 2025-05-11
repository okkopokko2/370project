<?php
session_start();
include("./includes/header.php");
include("./includes/functions.php");

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if(isset($_REQUEST['login'])) {
    include("./includes/db_conn.php");
    
    $user_name = trim($_REQUEST['user_name']);
    $user_pass = $_REQUEST['user_pass'];
    
    if(empty($user_name) || empty($user_pass)) {
        my_alert("danger", "All fields are required");
    } else {
        $sql = "SELECT * FROM reg_users WHERE user_name = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $user_name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($row = mysqli_fetch_assoc($result)) {
            if(password_verify($user_pass, $row['user_pass'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['user_name'];
                my_alert("success", "Login successful!");
                header("refresh:1;url=dashboard.php");
            } else {
                my_alert("danger", "Invalid password");
            }
        } else {
            my_alert("danger", "User not found");
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
}
?>

<div class="container">
    <div class="card my-card">
        <div class="card-header bg-primary text-white text-center">
            Login
        </div>
        <div class="card-body">
            <div class="row">   
                <div class="col-12">
                    <form method="POST"> 
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Username</label>
                            <input type="text" class="form-control" id="user_name" name="user_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_pass" class="form-label">Password</label>
                            <input type="password" class="form-control" id="user_pass" name="user_pass" required>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100" name="login">Login</button>
                        </div>
                        <div class="text-center">
                            Don't have an account? <a href="register_use.php">Register here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include("./includes/footer.php");
?> 