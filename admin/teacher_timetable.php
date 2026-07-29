<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');



$teacher_id = $_GET['teacher_id'] ?? '';

$teacher_name = '';

$timetable = [];



// Load teachers

$teachers = mysqli_query(

$conn,

"SELECT teacher_id, full_name 
FROM teachers
ORDER BY full_name"

);




// If teacher selected

if($teacher_id){



$stmt = mysqli_prepare(

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


WHERE t.teacher_id=?


ORDER BY day,start_time"

);



mysqli_stmt_bind_param(

$stmt,

"i",

$teacher_id

);



mysqli_stmt_execute($stmt);


$result=mysqli_stmt_get_result($stmt);



while($row=mysqli_fetch_assoc($result)){


$timetable[]=$row;


}



if(count($timetable)>0){

$teacher_name=$timetable[0]['full_name'];

}


}


?>



<!DOCTYPE html>

<html>


<head>


<title>Teacher Timetable</title>


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

👨‍🏫 Teacher Timetable

</h2>



<div class="card mt-4">


<div class="card-body">


<form method="GET">


<div class="row">


<div class="col-md-6">


<label>Select Teacher</label>


<select name="teacher_id"

class="form-control">


<option value="">

Choose Teacher

</option>



<?php while($t=mysqli_fetch_assoc($teachers)): ?>


<option

value="<?=$t['teacher_id']?>"

<?=($teacher_id==$t['teacher_id'])?'selected':'';?> >


<?=$t['full_name']?>


</option>


<?php endwhile; ?>


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






<?php if($teacher_id): ?>


<div class="card mt-4">


<div class="card-header bg-success text-white">


<h4>

<?=$teacher_name?>

</h4>


</div>




<div class="card-body">


<table class="table table-bordered">


<thead class="table-dark">


<tr>


<th>Day</th>

<th>Time</th>

<th>Class</th>

<th>Subject</th>

<th>Room</th>


</tr>


</thead>



<tbody>



<?php foreach($timetable as $row): ?>


<tr>


<td>

<?=$row['day']?>


</td>



<td>

<?=$row['start_time']?>

-

<?=$row['end_time']?>


</td>



<td>

<?=$row['class']?>


</td>



<td>

<?=$row['subject_name']?>


</td>



<td>

<?=$row['room']?>


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