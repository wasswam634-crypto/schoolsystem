<?php
include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$row = mysqli_fetch_assoc($result);

if(!$row){
    die("Student not found.");
}

if(isset($_POST['update'])){

    $reg_no = trim($_POST['reg_no'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $class = trim($_POST['class'] ?? '');
    $stream = trim($_POST['stream'] ?? '');

    $update = mysqli_prepare(
        $conn,
        "UPDATE students SET reg_no = ?, full_name = ?, gender = ?, class = ?, stream = ? WHERE student_id = ?"
    );
    mysqli_stmt_bind_param($update, "sssssi", $reg_no, $full_name, $gender, $class, $stream, $id);
    mysqli_stmt_execute($update);

    redirect("view_students.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Student</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<h2>Edit Student</h2>

<form method="POST">

<div class="mb-3">
<label>Registration Number</label>
<input type="text"
name="reg_no"
class="form-control"
value="<?php echo e($row['reg_no']); ?>">
</div>

<div class="mb-3">
<label>Full Name</label>
<input type="text"
name="full_name"
class="form-control"
value="<?php echo e($row['full_name']); ?>">
</div>

<div class="mb-3">
<label>Gender</label>

<select name="gender" class="form-control">

<option <?php if($row['gender']=="Male") echo "selected"; ?>>
Male
</option>

<option <?php if($row['gender']=="Female") echo "selected"; ?>>
Female
</option>

</select>

</div>

<div class="mb-3">
<label>Class</label>
<input type="text"
name="class"
class="form-control"
value="<?php echo e($row['class']); ?>">
</div>

<div class="mb-3">
<label>Stream</label>
<input type="text"
name="stream"
class="form-control"
value="<?php echo e($row['stream']); ?>">
</div>

<button type="submit"
name="update"
class="btn btn-success">
Update Student
</button>

</form>

</div>

</body>
</html>
