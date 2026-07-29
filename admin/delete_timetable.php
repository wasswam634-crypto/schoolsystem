<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$deleted = $_GET['deleted'] ?? '';



$class = $_GET['class'] ?? '';

$year = $_GET['year'] ?? '';

$term = $_GET['term'] ?? '';





if($class && $year && $term){



$stmt = mysqli_prepare(

$conn,

"DELETE FROM timetables

WHERE class=?

AND academic_year=?

AND term=?"

);



mysqli_stmt_bind_param(

$stmt,

"sss",

$class,

$year,

$term

);



if(mysqli_stmt_execute($stmt)){



header(
"Location: timetables.php?deleted=1"
);

exit;



}else{



echo "Failed to delete timetable.";


}



}else{



echo "Invalid timetable details.";



}


?>