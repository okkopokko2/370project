<?php
function my_alert($color, $msg)
{
?>

    <div style="position:absolute; right:20px; top:20px;"class="alert 
    alert-<?php echo $color?> alert-dismissible fade show" role="alert">
        New Record Created successfully
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

<?php
}


?>