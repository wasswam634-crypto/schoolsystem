<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

if(isset($_POST['save'])){

    $date = $_POST['attendance_date'];

    foreach($_POST['status'] as $student_id => $status){

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO attendance
            (student_id, attendance_date, status, recorded_by)
            VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "issi",
            $student_id,
            $date,
            $status,
            $_SESSION['user_id']
        );

        mysqli_stmt_execute($stmt);
    }

    $success = "Attendance saved successfully.";
}

$page_title = "Attendance";

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">
<div class="row">

<?php include '../includes/sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2>Attendance Management</h2>

<div class="card shadow">

<div class="card-header bg-primary text-white">
Mark Attendance
</div>

<?php if(isset($success)): ?>

<div class="alert alert-success">
    <?= e($success); ?>
</div>

<?php endif; ?>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Date</label>

<input
type="date"
name="attendance_date"
class="form-control"
value="<?= date('Y-m-d'); ?>"
required>

</div>

<table class="table table-bordered">

<thead>

<tr>
<th>Student</th>
<th>Status</th>
</tr>

</thead>

<tbody>

<?php
$students = mysqli_query(
    $conn,
    "SELECT student_id, full_name
     FROM students
     ORDER BY full_name ASC"
);

while($student = mysqli_fetch_assoc($students)){
?>

<tr>

<td>
<?= e($student['full_name']); ?>
</td>

<td>

<select
name="status[<?= $student['student_id']; ?>]"
class="form-control">

<option value="Present">Present</option>
<option value="Absent">Absent</option>
<option value="Sick">Sick</option>
<option value="Excused">Excused</option>

</select>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<button
type="submit"
name="save"
class="btn btn-primary">

Save Attendance

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>