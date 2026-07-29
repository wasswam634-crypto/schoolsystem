<?php

include '../../database/connection.php';
include '../../includes/auth.php';

require_role('admin');


$id = $_GET['id'];



$result = mysqli_query(

    $conn,

    "SELECT image
     FROM gallery
     WHERE gallery_id=$id"

);



$row = mysqli_fetch_assoc($result);



if(!empty($row['image'])){


    $file = "../../uploads/gallery/".$row['image'];



    if(file_exists($file)){

        unlink($file);

    }


}




$stmt = mysqli_prepare(

    $conn,

    "DELETE FROM gallery
     WHERE gallery_id=?"

);



mysqli_stmt_bind_param(

    $stmt,

    "i",

    $id

);



mysqli_stmt_execute($stmt);



header("Location: gallery.php");

exit;

?>