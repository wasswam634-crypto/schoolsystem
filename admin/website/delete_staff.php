<?php

include '../../database/connection.php';
include '../../includes/auth.php';

require_role('admin');


$id=$_GET['id'];



$result=mysqli_query(

$conn,

"SELECT photo
 FROM website_staff
 WHERE staff_id=$id"

);



$row=mysqli_fetch_assoc($result);



if(!empty($row['photo'])){


$file="../../uploads/website/".$row['photo'];


if(file_exists($file)){

unlink($file);

}

}



$stmt=mysqli_prepare(

$conn,

"DELETE FROM website_staff
WHERE staff_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"i",

$id

);



mysqli_stmt_execute($stmt);



header("Location: staff.php");

exit;

?>