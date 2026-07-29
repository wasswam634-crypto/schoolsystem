<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


// GET ACTIVE ACADEMIC PERIOD

$period_query = mysqli_query(
    $conn,
    "SELECT period_id, academic_year, period_name
     FROM academic_periods
     WHERE status='Active'
     LIMIT 1"
);


$active_period = mysqli_fetch_assoc($period_query);


if(!$active_period){

    die("No active academic period found. Please activate an academic period.");

}



$period_id = $active_period['period_id'];





// GET STUDENTS

$result = mysqli_query(
    $conn,
    "SELECT 
        student_id,
        full_name,
        class,
        reg_no
     FROM students
     ORDER BY full_name"
);



?>


<!DOCTYPE html>

<html>

<head>

<title>
Report Cards
</title>


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


</style>


</head>



<body>


<div class="container-fluid p-4">



<div class="d-flex justify-content-between align-items-center mb-4">


<h2>

📄 Report Cards Management

</h2>


</div>




<!-- CURRENT PERIOD -->

<div class="alert alert-info">


Current Academic Period:


<strong>

<?=e($active_period['academic_year']);?>

-

<?=e($active_period['period_name']);?>

</strong>


</div>







<div class="card">



<div class="card-header bg-primary text-white">


Students


</div>





<div class="card-body">



<table class="table table-bordered table-striped">



<thead class="table-dark">


<tr>


<th>
Reg No
</th>


<th>
Student Name
</th>


<th>
Class
</th>


<th width="200">
Action
</th>


</tr>


</thead>





<tbody>



<?php while($row=mysqli_fetch_assoc($result)): ?>



<tr>



<td>

<?=e($row['reg_no']);?>

</td>




<td>

<?=e($row['full_name']);?>

</td>




<td>

<?=e($row['class']);?>

</td>





<td>


<a

href="report_card.php?id=<?=$row['student_id'];?>&period_id=<?=$period_id;?>"

class="btn btn-success btn-sm">


View Report Card


</a>



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