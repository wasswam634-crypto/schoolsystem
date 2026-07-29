<?php
include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';
require_role('admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Students</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<h2>All Students</h2>

<table class="table table-bordered table-striped">

<tr>
<th>ID</th>
<th>Reg No</th>
<th>Name</th>
<th>Gender</th>
<th>Class</th>
<th>Stream</th>
<th>Actions</th>
</tr>

<?php

$sql = "SELECT * FROM students ORDER BY full_name";
$result = mysqli_query($conn,$sql);

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo e($row['student_id']); ?></td>

<td><?php echo e($row['reg_no']); ?></td>

<td><?php echo e($row['full_name']); ?></td>

<td><?php echo e($row['gender']); ?></td>

<td><?php echo e($row['class']); ?></td>

<td><?php echo e($row['stream']); ?></td>

<td>

<a href="edit_student.php?id=<?php echo e($row['student_id']); ?>"
class="btn btn-warning btn-sm">
Edit
</a>

<a href="delete_student.php?id=<?php echo e($row['student_id']); ?>"
class="btn btn-danger btn-sm">
Delete
</a>

</td>

</tr>

<?php
}
?>

</table>

</div>

</body>
</html>
