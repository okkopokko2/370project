<?php

include("./includes/header.php");
include("./includes/functions.php");

if(isset($_REQUEST['register'])){
    include("./includes/db_conn.php");
    //GETTING THE values from the html form
    
        $user_name= $_REQUEST['user_name']; #ekhane input theke value niye nijer kase niye ashbe
        $user_pass= $_REQUEST['user_pass']; #ekhane same pass niye nijer kase niye ashbe

        //inserting data into database
        $sql = "INSERT INTO reg_users (user_name, user_pass)
        VALUES ('$user_name', '$user_pass')";

        if (mysqli_query($conn, $sql)) {
            my_alert("success", "New record created successfully");
        } 
        else{
            my_alert("danger", "Error while installing the record");
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
                            <label for="" class="form-label">User Name</label>
                            <input type="text" class="form-control"
                            name="user_name">
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label">User Password</label>
                            <input type="password" class="form-control"
                            name="user_pass">
                        </div>
                        <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100" name="register">Register</button>
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