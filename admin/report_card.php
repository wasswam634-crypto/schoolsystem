<?php

error_reporting(E_ALL);
ini_set('display_errors',1);


include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';


require_role(['admin','teacher','student']);



if(!isset($_GET['id'])){

    die("Student ID missing");

}


$student_id = (int)$_GET['id'];


if(!isset($_GET['period_id'])){

    die("Academic period missing");

}


$period_id = (int)$_GET['period_id'];





/* =========================
GET STUDENT
========================= */


$stmt=mysqli_prepare(
$conn,
"SELECT *
 FROM students
 WHERE student_id=?"
);


mysqli_stmt_bind_param(
$stmt,
"i",
$student_id
);


mysqli_stmt_execute($stmt);


$student_result=mysqli_stmt_get_result($stmt);


$student=mysqli_fetch_assoc($student_result);



if(!$student){

    die("Student not found");

}





/* =========================
GET PERIOD
========================= */


$period_stmt=mysqli_prepare(
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


$period_result=mysqli_stmt_get_result($period_stmt);


$period=mysqli_fetch_assoc($period_result);



if(!$period){

    die("Academic period not found");

}





/* =========================
GET RESULTS
========================= */


$result_stmt=mysqli_prepare(
$conn,

"SELECT

subjects.subject_name,
marks.marks

FROM marks


JOIN subjects

ON marks.subject_id=subjects.subject_id


WHERE marks.student_id=?

AND marks.period_id=?


ORDER BY subjects.subject_name"

);



mysqli_stmt_bind_param(
$result_stmt,
"ii",
$student_id,
$period_id
);



mysqli_stmt_execute($result_stmt);



$result=mysqli_stmt_get_result($result_stmt);






/* =========================
POSITION
========================= */


$rank_sql="

SELECT

student_id,

AVG(marks) average


FROM marks


WHERE period_id=$period_id


GROUP BY student_id


ORDER BY average DESC

";


$rank_result=mysqli_query(
$conn,
$rank_sql
);



$position=1;

$student_position="N/A";


while($row=mysqli_fetch_assoc($rank_result)){


    if($row['student_id']==$student_id){

        $student_position=$position;

        break;

    }


    $position++;

}





$total=0;

$count=0;



?>

<!DOCTYPE html>

<html>

<head>

<title>
Student Report Card
</title>


<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<style>


body{

background:#f2f4f7;

font-family:Arial;

}


.report-card{

background:white;

max-width:900px;

margin:auto;

padding:30px;

border-radius:15px;

box-shadow:0 5px 20px #ccc;

}


.school-header{

text-align:center;

}


.school-logo{

width:100px;

height:100px;

object-fit:contain;

}


.results th{

background:#212529;

color:white;

text-align:center;

}


.results td{

text-align:center;

}


.summary{

margin-top:20px;

border-top:2px solid #ddd;

padding-top:15px;

}



@media print{

.btn{

display:none;

}

.report-card{

box-shadow:none;

}

}


</style>


</head>


<body>


<div class="container mt-4">



<button
onclick="window.print()"
class="btn btn-primary mb-3">

Print Report Card

</button>




<div class="report-card">



<div class="school-header">


<?php


$school_query=mysqli_query(
$conn,
"SELECT * FROM school_settings LIMIT 1"
);


$school=mysqli_fetch_assoc($school_query);


?>



<?php if(!empty($school['logo'])): ?>

<img 
src="../uploads/logos/<?=e($school['logo']);?>"
class="school-logo">


<?php endif; ?>



<h2>

<?=e($school['school_name']);?>

</h2>


<p>

<?=e($school['address'] ?? '');?>

<br>

<?=e($school['phone'] ?? '');?>

</p>



<h3>

STUDENT REPORT CARD

</h3>


<p>

Academic Year:

<b>
<?=e($period['academic_year']);?>
</b>


<br>


Period:

<b>
<?=e($period['period_name']);?>
</b>


</p>



</div>



<hr>



<div class="card p-3">


<b>
Student Name:
</b>

<?=e($student['full_name']);?>


<br>


<b>
Registration No:
</b>

<?=e($student['reg_no']);?>


<br>


<b>
Class:
</b>

<?=e($student['class']);?>


</div>





<h4 class="mt-4">

Academic Performance

</h4>



<table class="table table-bordered results">


<tr>

<th>
Subject
</th>

<th>
Marks
</th>

<th>
Grade
</th>

<th>
Remark
</th>


</tr>



<?php while($row=mysqli_fetch_assoc($result)): ?>


<?php


$total += $row['marks'];

$count++;


$grade=grade_from_marks($row['marks']);



if($row['marks']>=80){

$remark="Excellent";

}
elseif($row['marks']>=70){

$remark="Very Good";

}
elseif($row['marks']>=60){

$remark="Good";

}
elseif($row['marks']>=50){

$remark="Fair";

}
else{

$remark="Needs Improvement";

}


?>



<tr>


<td>

<?=e($row['subject_name']);?>

</td>


<td>

<?=e($row['marks']);?>

</td>


<td>

<?=e($grade);?>

</td>


<td>

<?=e($remark);?>

</td>


</tr>



<?php endwhile; ?>


</table>




<?php


$average = ($count>0)
?
$total/$count
:
0;


?>



<div class="summary">


<h5>

Total Marks:
<?=e($total);?>

</h5>


<h5>

Average:
<?=round($average,2);?>%

</h5>


<h5>

Subjects:
<?=e($count);?>

</h5>


<h5>

Position:
<?=e($student_position);?>

</h5>



</div>




<div class="mt-5">


<div class="row text-center">


<div class="col">

____________________

<br>

Class Teacher

</div>


<div class="col">

____________________

<br>

Head Teacher

</div>


</div>


</div>



</div>


</div>


</body>

</html>