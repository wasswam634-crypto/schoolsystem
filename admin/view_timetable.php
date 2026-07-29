<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


if(!isset($_GET['class']) || !isset($_GET['period_id'])){

    die("Missing timetable information");

}
// GET SCHOOL SETTINGS

$settings_query = mysqli_query(
    $conn,
    "SELECT * FROM school_settings LIMIT 1"
);

$settings = mysqli_fetch_assoc($settings_query);



$class = $_GET['class'];

$period_id = (int)$_GET['period_id'];



/*
GET ACADEMIC PERIOD
*/

$period_stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM academic_periods
     WHERE period_id=?"
);


mysqli_stmt_bind_param(
    $period_stmt,
    "i",
    $period_id
);


mysqli_stmt_execute($period_stmt);


$period_result = mysqli_stmt_get_result($period_stmt);


$period = mysqli_fetch_assoc($period_result);



if(!$period){

    die("Academic period not found");

}



/*
GET TIMETABLE LESSONS
*/

$stmt = mysqli_prepare(
    $conn,

    "SELECT

        timetables.*,

        subjects.subject_name,

        teachers.full_name AS teacher_name


     FROM timetables


     LEFT JOIN subjects

     ON timetables.subject_id = subjects.subject_id


     LEFT JOIN teachers

     ON timetables.teacher_id = teachers.teacher_id


     WHERE timetables.class=?

     AND timetables.period_id=?


     ORDER BY

     FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday'),

     start_time"

);



mysqli_stmt_bind_param(
    $stmt,
    "si",
    $class,
    $period_id
);



mysqli_stmt_execute($stmt);



$result = mysqli_stmt_get_result($stmt);

$timetable = [];


while($row = mysqli_fetch_assoc($result)){


    $time = $row['start_time']." - ".$row['end_time'];


    $timetable[$time][$row['day']] = [

        'subject_name' => $row['subject_name'],

        'full_name' => $row['teacher_name'],

        'room' => $row['room']

    ];


}


?>



<!DOCTYPE html>

<html>


<head>


<title>View Timetable</title>



<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">



<style>


body{

background:#f5f6fa;

}



.card{

border:none;

border-radius:15px;

box-shadow:0 3px 12px rgba(0,0,0,.08);

}



td{

height:100px;

vertical-align:middle;

text-align:center;

}



.lesson{

font-weight:bold;

}



.teacher{

font-size:13px;

color:#555;

}



.room{

font-size:12px;

color:#198754;

}

@media print{


.btn{

display:none;

}


body{

background:white;

}


.card{

box-shadow:none;



border:none;

}


.card-header{

color:black!important;

background:white!important;

}


}


</style>



</head>



<body>



<div class="container-fluid p-4">



<div class="card">



<div class="card-header bg-primary text-white text-center">


<h2>

<?=e($settings['school_name']);?>

</h2>


<h4>

<?=e($class);?> Timetable

</h4>


<p class="mb-0">

<?=e($period['academic_year']);?>

|

<?=e($period['period_name']);?>

</p>


</div>




<div class="card-body">





<a href="timetables.php"

class="btn btn-secondary mb-3">

Back

</a>


<button

onclick="window.print()"

class="btn btn-dark mb-3">

Print Timetable

</button>






<table class="table table-bordered">



<thead class="table-dark">


<tr>


<th>Time</th>

<th>Monday</th>

<th>Tuesday</th>

<th>Wednesday</th>

<th>Thursday</th>

<th>Friday</th>


</tr>


</thead>





<tbody>


<?php foreach($timetable as $time=>$days): ?>


<tr>



<td>

<?=e($time);?>

</td>





<?php

$week=[

"Monday",

"Tuesday",

"Wednesday",

"Thursday",

"Friday"

];


foreach($week as $day):

?>



<td>



<?php if(isset($days[$day])): ?>


<div class="lesson">


<?=e($days[$day]['subject_name']);?>

</div>


<div class="teacher">

<?=e($days[$day]['full_name']);?>

</div>



<div class="room">

Room:
<?=e($days[$day]['room']);?>

</div>



<?php else: ?>


-


<?php endif; ?>


</td>



<?php endforeach; ?>



</tr>



<?php endforeach; ?>



</tbody>



</table>



</div>


</div>



</div>



</body>


</html>