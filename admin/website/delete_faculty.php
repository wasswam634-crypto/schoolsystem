<?php

include '../../database/connection.php';
include '../../includes/auth.php';

require_role('admin');


$id=$_GET['id'];



$stmt=mysqli_prepare(

$conn,

"DELETE FROM faculties
WHERE faculty_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"i",

$id

);



mysqli_stmt_execute($stmt);



header("Location: faculties.php");

exit;

?>