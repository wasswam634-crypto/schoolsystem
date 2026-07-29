<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message = null;
$error = null;



if(isset($_POST['save'])){


$full_name = trim($_POST['full_name'] ?? '');

$phone = trim($_POST['phone'] ?? '');

$email = trim($_POST['email'] ?? '');

$subject = trim($_POST['subject'] ?? '');



$stmt = mysqli_prepare(

$conn,

"INSERT INTO teachers
(full_name, phone, email, subject_speciality)
VALUES (?,?,?,?)"

);



mysqli_stmt_bind_param(

$stmt,

"ssss",

$full_name,

$phone,

$email,

$subject

);



if(mysqli_stmt_execute($stmt)){

$message="Teacher saved successfully.";

}else{

$error=mysqli_error($conn);

}



}



?>



<!DOCTYPE html>

<html>

<head>


<title>Teacher Management</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">



<style>


body{

background:#f5f6fa;

}


.card{

border:none;

border-radius:12px;

box-shadow:0 3px 12px rgba(0,0,0,.08);

}



.card-header{

font-weight:bold;

}


</style>


</head>



<body>



<div class="container-fluid p-4">



<h2 class="mb-4">

Teacher Management

</h2>




<?php if($message): ?>

<div class="alert alert-success">

<?=e($message)?>

</div>

<?php endif; ?>



<?php if($error): ?>

<div class="alert alert-danger">

<?=e($error)?>

</div>

<?php endif; ?>





<div class="mb-3">


<a href="export_teachers_excel.php"

class="btn btn-success">

Export Excel

</a>



<button onclick="window.print()"

class="btn btn-primary">

Print Teachers

</button>



</div>






<!-- REGISTRATION CARD -->


<div class="card mb-4">


<div class="card-header bg-primary text-white">

Register Teacher

</div>



<div class="card-body">



<form method="POST">



<div class="row">



<div class="col-md-6 mb-3">


<label class="form-label">

Full Name

</label>


<input type="text"

name="full_name"

class="form-control"

required>


</div>





<div class="col-md-6 mb-3">


<label class="form-label">

Phone

</label>


<input type="text"

name="phone"

class="form-control">


</div>






<div class="col-md-6 mb-3">


<label class="form-label">

Email

</label>


<input type="email"

name="email"

class="form-control"

required>


</div>






<div class="col-md-6 mb-3">


<label class="form-label">

Subject Speciality

</label>


<input type="text"

name="subject"

class="form-control"

placeholder="Example: Mathematics, Physics"

required>


</div>



</div>




<button class="btn btn-primary"

name="save">

Save Teacher

</button>




</form>



</div>


</div>







<!-- TEACHER TABLE -->



<div class="card">



<div class="card-header bg-success text-white">

Registered Teachers

</div>



<div class="card-body">



<div class="table-responsive">



<table class="table table-bordered table-striped align-middle">



<thead class="table-dark">


<tr>

<th>#</th>

<th>Name</th>

<th>Phone</th>

<th>Email</th>

<th>Subject Speciality</th>

<th>Action</th>


</tr>


</thead>



<tbody>



<?php


$teachers=mysqli_query(

$conn,

"SELECT * FROM teachers ORDER BY full_name"

);


$count=1;


while($row=mysqli_fetch_assoc($teachers)):

?>



<tr>


<td>

<?=$count++;?>

</td>


<td>

<?=e($row['full_name']);?>

</td>



<td>

<?=e($row['phone']);?>

</td>



<td>

<?=e($row['email']);?>

</td>



<td>

<?=e($row['subject_speciality']);?>

</td>



<td>


<a href="edit_teacher.php?id=<?=$row['teacher_id'];?>"

class="btn btn-sm btn-warning">

Edit

</a>



<a href="delete_teacher.php?id=<?=$row['teacher_id'];?>"

class="btn btn-sm btn-danger"

onclick="return confirm('Delete this teacher?')">

Delete

</a>


</td>



</tr>



<?php endwhile; ?>



</tbody>


</table>



</div>


</div>


</div>





</div>



</body>


</html>