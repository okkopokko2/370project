<?php

include("./includes/header.php");
include("./includes/functions.php");

if(isset($_REQUEST['register'])){
    include("./includes/db_conn.php");
    
    // Getting the values from the html form
    $user_name = trim($_REQUEST['user_name']);
    $user_email = trim($_REQUEST['user_email']);
    $user_pass = $_REQUEST['user_pass'];
    $confirm_pass = $_REQUEST['confirm_pass'];
    
    // Basic validation
    $errors = [];
    
    if(empty($user_name)) {
        $errors[] = "Username is required";
    }
    
    if(empty($user_email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if(empty($user_pass)) {
        $errors[] = "Password is required";
    } elseif(strlen($user_pass) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if($user_pass !== $confirm_pass) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if username or email already exists
    $check_sql = "SELECT * FROM reg_users WHERE user_name = ? OR user_email = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $user_name, $user_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        $errors[] = "Username or email already exists";
    }
    
    if(empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($user_pass, PASSWORD_DEFAULT);
        
        // Inserting data into database
        $sql = "INSERT INTO reg_users (user_name, user_email, user_pass) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $user_name, $user_email, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            my_alert("success", "Registration successful! You can now login.");
            header("refresh:2;url=login.php");
        } else {
            my_alert("danger", "Error while creating account");
        }
        
        mysqli_stmt_close($stmt);
    } else {
        foreach($errors as $error) {
            my_alert("danger", $error);
        }
    }
    
    mysqli_close($conn);
}

?>

<div class="container">
    <div class="card my-card">
        <div class="card-header bg-primary text-white text-center">
            Register User
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
                            <label for="user_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="user_email" name="user_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_pass" class="form-label">Password</label>
                            <input type="password" class="form-control" id="user_pass" name="user_pass" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_pass" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" required>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100" name="register">Register</button>
                        </div>
                        <div class="text-center">
                            Already have an account? <a href="login.php">Login here</a>
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