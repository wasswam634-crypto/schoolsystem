<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');



$class = $_GET['class'] ?? '';

$year = $_GET['year'] ?? '';

$term = $_GET['term'] ?? '';



// Delete single lesson

if(isset($_GET['delete'])){


$id = $_GET['delete'];



$stmt=mysqli_prepare(

$conn,

"DELETE FROM timetables WHERE timetable_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"i",

$id

);



mysqli_stmt_execute($stmt);



header(
"Location: edit_timetable.php?class=$class&year=$year&term=$term"
);

exit;

}




// Save changes

if(isset($_POST['update'])){


$id = $_POST['id'];

$day=$_POST['day'];

$start=$_POST['start_time'];

$end=$_POST['end_time'];

$subject=$_POST['subject_id'];

$teacher=$_POST['teacher_id'];

$room=$_POST['room'];



$stmt=mysqli_prepare(

$conn,

"UPDATE timetables SET

day=?,

start_time=?,

end_time=?,

subject_id=?,

teacher_id=?,

room=?

WHERE timetable_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"sssissi",

$day,

$start,

$end,

$subject,

$teacher,

$room,

$id

);



mysqli_stmt_execute($stmt);



header(

"Location: edit_timetable.php?class=$class&year=$year&term=$term"

);

exit;


}






// Load subjects

$subjects=mysqli_query(

$conn,

"SELECT * FROM subjects ORDER BY subject_name"

);



// Load teachers

$teachers=mysqli_query(

$conn,

"SELECT * FROM teachers ORDER BY full_name"

);






// Load timetable lessons


$stmt=mysqli_prepare(

$conn,

"SELECT *

FROM timetables

WHERE class=?

AND academic_year=?

AND term=?

ORDER BY start_time"

);



mysqli_stmt_bind_param(

$stmt,

"sss",

$class,

$year,

$term

);



mysqli_stmt_execute($stmt);


$lessons=mysqli_stmt_get_result($stmt);



?>

<!DOCTYPE html>

<html>

<head>

<title>Edit Timetable</title>


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


<div class="card">


<div class="card-header bg-warning">


<h3>

Edit Timetable

</h3>


<p>

Class:

<?=e($class);?>

<br>

Academic Year:

<?=e($year);?>

<br>

Term:

<?=e($term);?>

</p>


</div>



<div class="card-body">





<table class="table table-bordered table-striped">


<thead class="table-dark">


<tr>

<th>Day</th>

<th>Start</th>

<th>End</th>

<th>Subject</th>

<th>Teacher</th>

<th>Room</th>

<th>Action</th>


</tr>


</thead>



<tbody>


<?php while($row=mysqli_fetch_assoc($lessons)): ?>



<form method="POST">



<tr>



<td>


<input type="hidden"

name="id"

value="<?=$row['timetable_id']?>">



<select name="day"

class="form-control">


<option <?=($row['day']=="Monday")?'selected':'';?>>

Monday

</option>


<option <?=($row['day']=="Tuesday")?'selected':'';?>>

Tuesday

</option>


<option <?=($row['day']=="Wednesday")?'selected':'';?>>

Wednesday

</option>


<option <?=($row['day']=="Thursday")?'selected':'';?>>

Thursday

</option>


<option <?=($row['day']=="Friday")?'selected':'';?>>

Friday

</option>


</select>


</td>






<td>


<input type="time"

name="start_time"

value="<?=$row['start_time']?>"

class="form-control">


</td>






<td>


<input type="time"

name="end_time"

value="<?=$row['end_time']?>"

class="form-control">


</td>






<td>


<select name="subject_id"

class="form-control">



<?php

mysqli_data_seek($subjects,0);


while($s=mysqli_fetch_assoc($subjects)):

?>


<option

value="<?=$s['subject_id']?>"

<?=($s['subject_id']==$row['subject_id'])?'selected':'';?>>



<?=$s['subject_name']?>


</option>



<?php endwhile; ?>


</select>


</td>






<td>


<select name="teacher_id"

class="form-control">



<?php


mysqli_data_seek($teachers,0);



while($t=mysqli_fetch_assoc($teachers)):


?>


<option

value="<?=$t['teacher_id']?>"

<?=($t['teacher_id']==$row['teacher_id'])?'selected':'';?>>



<?=$t['full_name']?>


</option>



<?php endwhile; ?>



</select>


</td>






<td>


<input type="text"

name="room"

value="<?=e($row['room']);?>"

class="form-control">


</td>






<td>



<button

name="update"

class="btn btn-success btn-sm">


Save


</button>




<a

href="edit_timetable.php?class=<?=$class?>&year=<?=$year?>&term=<?=$term?>&delete=<?=$row['timetable_id']?>"

onclick="return confirm('Delete this lesson?')"

class="btn btn-danger btn-sm">


Delete


</a>



</td>


</tr>



</form>



<?php endwhile; ?>



</tbody>


</table>





<a href="timetables.php"

class="btn btn-secondary">

Back

</a>



</div>


</div>


</div>


</body>


</html>