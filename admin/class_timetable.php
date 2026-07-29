<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$class = $_GET['class'] ?? '';

$year = $_GET['year'] ?? '';

$term = $_GET['term'] ?? '';



$timetable=[];



if($class && $year && $term){



$stmt=mysqli_prepare(

$conn,

"SELECT

t.*,

s.subject_name,

te.full_name


FROM timetables t


LEFT JOIN subjects s

ON t.subject_id=s.subject_id


LEFT JOIN teachers te

ON t.teacher_id=te.teacher_id


WHERE t.class=?

AND t.academic_year=?

AND t.term=?


ORDER BY day,start_time"

);



mysqli_stmt_bind_param(

$stmt,

"sss",

$class,

$year,

$term

);



mysqli_stmt_execute($stmt);



$result=mysqli_stmt_get_result($stmt);



while($row=mysqli_fetch_assoc($result)){


$timetable[]=$row;


}


}


?>



<!DOCTYPE html>

<html>


<head>


<title>Class Timetable</title>


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



<h2>

🎓 Class Timetable

</h2>



<div class="card mt-4">


<div class="card-body">


<form method="GET">


<div class="row">


<div class="col-md-4">


<label>Class</label>


<input

type="text"

name="class"

class="form-control"

placeholder="Example S1 East"

value="<?=e($class);?>">


</div>




<div class="col-md-3">


<label>Academic Year</label>


<input

type="text"

name="year"

class="form-control"

placeholder="2026"

value="<?=e($year);?>">


</div>




<div class="col-md-3">


<label>Term</label>


<select name="term"

class="form-control">


<option <?=($term=="Term 1")?'selected':'';?>>

Term 1

</option>


<option <?=($term=="Term 2")?'selected':'';?>>

Term 2

</option>


<option <?=($term=="Term 3")?'selected':'';?>>

Term 3

</option>


<option <?=($term=="Semester 1")?'selected':'';?>>

Semester 1

</option>


<option <?=($term=="Semester 2")?'selected':'';?>>

Semester 2

</option>


</select>


</div>




<div class="col-md-2 d-flex align-items-end">


<button class="btn btn-primary">

View

</button>


</div>



</div>


</form>


</div>


</div>







<?php if(count($timetable)>0): ?>



<div class="card mt-4">


<div class="card-header bg-primary text-white">


<h4>

<?=e($class);?> Timetable

</h4>


</div>



<div class="card-body">


<button

onclick="window.print()"

class="btn btn-dark mb-3">

Print

</button>





<table class="table table-bordered text-center">


<thead class="table-dark">


<tr>


<th>Day</th>

<th>Time</th>

<th>Subject</th>

<th>Teacher</th>

<th>Room</th>


</tr>


</thead>



<tbody>


<?php foreach($timetable as $row): ?>


<tr>


<td>

<?=$row['day'];?>

</td>


<td>

<?=$row['start_time'];?>

-

<?=$row['end_time'];?>

</td>



<td>

<?=$row['subject_name'];?>

</td>



<td>

<?=$row['full_name'];?>

</td>



<td>

<?=$row['room'];?>

</td>



</tr>



<?php endforeach; ?>


</tbody>


</table>


</div>


</div>



<?php endif; ?>




</div>


</body>


</html>