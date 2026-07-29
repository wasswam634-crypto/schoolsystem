<?php
include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = null;
$error = null;


if(isset($_POST['save'])){

    $reg_no = trim($_POST['reg_no'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? '';

    $class = trim($_POST['class'] ?? '');
    $stream = trim($_POST['stream'] ?? '');

    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $admission_date = $_POST['admission_date'] ?? '';

    $status = $_POST['status'] ?? 'Active';

    $nationality = trim($_POST['nationality'] ?? '');
    $religion = trim($_POST['religion'] ?? '');

    $parent_name = trim($_POST['parent_name'] ?? '');
    $parent_contact = trim($_POST['parent_contact'] ?? '');
    $parent_email = trim($_POST['parent_email'] ?? '');
    $parent_address = trim($_POST['parent_address'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');

    $blood_group = trim($_POST['blood_group'] ?? '');
    $allergies = trim($_POST['allergies'] ?? '');
    $medical_condition = trim($_POST['medical_condition'] ?? '');

    $previous_school = trim($_POST['previous_school'] ?? '');
    $notes = trim($_POST['notes'] ?? '');


    // PHOTO UPLOAD

    $photo = null;

    if(isset($_FILES['photo']) && $_FILES['photo']['name'] != ''){

        $photo = time().'_'.$_FILES['photo']['name'];

        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            "../uploads/students/".$photo
        );
    }



$stmt = mysqli_prepare($conn,

"INSERT INTO students(

reg_no,
full_name,
gender,
class,
stream,
dob,
admission_date,
status,
nationality,
religion,
parent_name,
parent_contact,
parent_email,
parent_address,
occupation,
photo,
blood_group,
allergies,
medical_condition,
previous_school,
notes

)

VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"

);



mysqli_stmt_bind_param(

$stmt,

"sssssssssssssssssssss",

$reg_no,
$full_name,
$gender,
$class,
$stream,
$date_of_birth,
$admission_date,
$status,
$nationality,
$religion,
$parent_name,
$parent_contact,
$parent_email,
$parent_address,
$occupation,
$photo,
$blood_group,
$allergies,
$medical_condition,
$previous_school,
$notes

);



if(mysqli_stmt_execute($stmt)){

$message="Student registered successfully.";

}else{

$error=mysqli_error($conn);

}


}

?>



<!DOCTYPE html>

<html>

<head>

<title>Student Registration</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2 class="mb-4">
Student Registration
</h2>



<?php if($message): ?>

<div class="alert alert-success">

<?=e($message);?>

</div>

<?php endif; ?>


<?php if($error): ?>

<div class="alert alert-danger">

<?=e($error);?>

</div>

<?php endif; ?>



<div class="mb-3">


<a href="export_students_excel.php"
class="btn btn-success">

Export Excel

</a>


<button onclick="window.print()"
class="btn btn-primary">

Print Students

</button>


</div>





<form method="POST" enctype="multipart/form-data">



<!-- STUDENT INFORMATION -->

<div class="card mb-4">

<div class="card-header bg-primary text-white">

Student Information

</div>


<div class="card-body row">


<div class="col-md-6 mb-3">

<label>Registration Number</label>

<input type="text"
name="reg_no"
class="form-control"
required>

</div>



<div class="col-md-6 mb-3">

<label>Full Name</label>

<input type="text"
name="full_name"
class="form-control"
required>

</div>




<div class="col-md-6 mb-3">

<label>Gender</label>

<select name="gender"
class="form-control">

<option>Male</option>

<option>Female</option>

</select>


</div>



<div class="col-md-6 mb-3">

<label>Date of Birth</label>

<input type="date"
name="date_of_birth"
class="form-control">

</div>



<div class="col-md-6 mb-3">

<label>Nationality</label>

<input type="text"
name="nationality"
class="form-control">

</div>


<div class="col-md-6 mb-3">

<label>Religion</label>

<input type="text"
name="religion"
class="form-control">

</div>


<div class="col-md-6 mb-3">

<label>Student Photo</label>

<input type="file"
name="photo"
class="form-control">

</div>


</div>

</div>





<!-- ACADEMIC INFORMATION -->

<div class="card mb-4">

<div class="card-header bg-success text-white">

Academic Information

</div>


<div class="card-body row">


<div class="col-md-6 mb-3">

<label>Class</label>

<input type="text"
name="class"
class="form-control"
placeholder="Example: P4, S2, Year 1">

</div>



<div class="col-md-6 mb-3">

<label>Stream</label>

<input type="text"
name="stream"
class="form-control"
placeholder="Example: East, Science">

</div>



<div class="col-md-6 mb-3">

<label>Admission Date</label>

<input type="date"
name="admission_date"
class="form-control">

</div>



<div class="col-md-6 mb-3">

<label>Status</label>

<select name="status"
class="form-control">

<option>Active</option>

<option>Inactive</option>

</select>


</div>


<div class="col-md-12 mb-3">

<label>Previous School</label>

<input type="text"
name="previous_school"
class="form-control">


</div>


</div>

</div>






<!-- PARENT DETAILS -->


<div class="card mb-4">

<div class="card-header bg-warning">

Parent / Guardian Information

</div>


<div class="card-body row">


<div class="col-md-6 mb-3">

<label>Name</label>

<input type="text"
name="parent_name"
class="form-control">

</div>


<div class="col-md-6 mb-3">

<label>Contact</label>

<input type="text"
name="parent_contact"
class="form-control">

</div>



<div class="col-md-6 mb-3">

<label>Email</label>

<input type="email"
name="parent_email"
class="form-control">

</div>


<div class="col-md-6 mb-3">

<label>Occupation</label>

<input type="text"
name="occupation"
class="form-control">

</div>


<div class="col-md-12 mb-3">

<label>Address</label>

<textarea name="parent_address"
class="form-control"></textarea>

</div>



</div>

</div>



<div class="mb-3">

<label class="form-label">
Class / Course
</label>


<select 
name="group_id"
class="form-control"
required>


<option value="">
Select Group
</option>


<?php

$groups=mysqli_query(

$conn,

"SELECT group_id, group_name

FROM academic_groups

WHERE status='Active'

ORDER BY group_name"

);


while($group=mysqli_fetch_assoc($groups)){

?>

<option value="<?= $group['group_id']; ?>">

<?= e($group['group_name']); ?>

</option>


<?php } ?>


</select>

</div>


<!-- MEDICAL INFORMATION -->


<div class="card mb-4">


<div class="card-header bg-danger text-white">

Medical Information

</div>


<div class="card-body row">


<div class="col-md-4 mb-3">

<label>Blood Group</label>

<input type="text"
name="blood_group"
class="form-control">

</div>



<div class="col-md-4 mb-3">

<label>Allergies</label>

<input type="text"
name="allergies"
class="form-control">

</div>




<div class="col-md-4 mb-3">

<label>Medical Condition</label>

<input type="text"
name="medical_condition"
class="form-control">

</div>


</div>

</div>






<div class="card mb-4">

<div class="card-header">

Other Information

</div>


<div class="card-body">


<label>Notes</label>

<textarea name="notes"
class="form-control"></textarea>


</div>


</div>





<button class="btn btn-primary btn-lg"
name="save">

Save Student

</button>



</form>


</div>


</body>

</html>