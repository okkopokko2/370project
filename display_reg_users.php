<?php

include("./includes/header.php");
include("./includes/functions.php");
include("./includes/db_conn.php");

?>


<div class="container">
    <table class="table table-boarded table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">User Name</th>
                <th scope="col">Password</th>
                
                </tr>
        </thead>
        <tbody>
            <?php
            $fetch_data= "Select * FROM reg_users"; #basically eiduita line theke amr inforamtion niye ashe database theke
            $run_fetch_data=mysqli_query($conn,$fetch_data);
            
            if(mysqli_num_rows($run_fetch_data)>0){
                
            }else{
                echo "No record found";
            }
            


            

            





            ?>

            <tr>
                <th scope="row">1</th>
                <td>Mark</td>
                <td>Otto</td>
            </tr>
        
        </tbody>
    </table>
</div>




<?php

include("./includes/footer.php");





?>