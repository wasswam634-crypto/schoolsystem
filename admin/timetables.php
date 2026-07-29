<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = "";
$deleted = $_GET['deleted'] ?? "";


/*
|--------------------------------------------------------------------------
| LOAD ALL ACADEMIC PERIODS
|--------------------------------------------------------------------------
*/

$periods = [];

$period_query = mysqli_query(
    $conn,
    "SELECT
        period_id,
        academic_year,
        period_name,
        status
     FROM academic_periods
     ORDER BY period_id DESC"
);

while($row = mysqli_fetch_assoc($period_query)){
    $periods[] = $row;
}


/*
|--------------------------------------------------------------------------
| LOAD TIMETABLES
|--------------------------------------------------------------------------
|
| One timetable per Class + Academic Period
|
*/

$timetable_query = mysqli_query(
    $conn,
    "SELECT
        t.period_id,
        t.class,
        COUNT(*) AS lessons,
        ap.academic_year,
        ap.period_name,
        ap.status

     FROM timetables t

     INNER JOIN academic_periods ap
        ON t.period_id = ap.period_id

     GROUP BY
        t.period_id,
        t.class

     ORDER BY
        ap.period_id DESC,
        t.class ASC"
);

?>

<!DOCTYPE html>
<html>

<head>

<title>Timetable Management</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 3px 12px rgba(0,0,0,.08);
}

.table{
    background:white;
}

.badge{
    font-size:14px;
}

</style>

</head>

<body>

<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>
📅 Timetable Management
</h2>

<a href="timetable.php" class="btn btn-primary">
<i class="bi bi-plus-circle"></i>
Create Timetable
</a>

</div>

<?php if($deleted): ?>

<div class="alert alert-success">

Timetable deleted successfully.

</div>

<?php endif; ?>

<a href="timetable.php"

class="btn btn-primary">

+ Create Timetable

</a>


</div>







<div class="card">


<div class="card-header bg-dark text-white">

Created Timetables

</div>



<div class="card-body">


<table class="table table-bordered table-striped">


<thead class="table-dark">


<tr>


<th>Class</th>

<th>Academic Year</th>

<th>Term</th>

<th>Total Lessons</th>

<th width="300">

Actions

</th>


</tr>


</thead>




<tbody>



<?php while($row=mysqli_fetch_assoc($timetable_query)): ?>



<tr>


<td>

<?=e($row['class']);?>

</td>



<td>

<?=e($row['academic_year']);?>

</td>



<td>

<?=e($row['period_name']);?>

</td>




<td>

<?=e($row['lessons']);?>

</td>





<td>



<a 
href="view_timetable.php?period_id=<?=$row['period_id'];?>&class=<?=urlencode($row['class']);?>"
class="btn btn-success btn-sm">

View

</a>





<a 
href="edit_timetable.php?period_id=<?=$row['period_id'];?>&class=<?=urlencode($row['class']);?>"
class="btn btn-warning btn-sm">




<a 
href="delete_timetable.php?period_id=<?=$row['period_id'];?>&class=<?=urlencode($row['class']);?>"



</td>


</tr>



<?php endwhile; ?>



</tbody>


</table>




</div>


</div>



</div>



</body>


</html>