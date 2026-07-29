<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('student');

$page_title = "Student Dashboard";

include '../includes/header.php';
include '../includes/navbar.php';

$user_id = $_SESSION['user_id'];

/* Student Information */
$stmt = mysqli_prepare(
    $conn,
    "SELECT student_id, full_name, reg_no, class
     FROM students
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

$student_id = $student['student_id'] ?? 0;

/* Average Marks */
$avg_query = mysqli_query(
    $conn,
    "SELECT AVG(marks) AS average_marks
     FROM marks
     WHERE student_id = $student_id"
);

$avg_data = mysqli_fetch_assoc($avg_query);

$average = round($avg_data['average_marks'] ?? 0, 2);

/* Subject Count */
$subject_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM marks
     WHERE student_id = $student_id"
);

$subject_data = mysqli_fetch_assoc($subject_query);

$subject_count = $subject_data['total'] ?? 0;

/* Position */
$ranking_query = mysqli_query(
    $conn,
    "SELECT student_id,
            AVG(marks) AS avg_marks
     FROM marks
     GROUP BY student_id
     ORDER BY avg_marks DESC"
);

$position = 1;
$student_position = '-';

while($row = mysqli_fetch_assoc($ranking_query)){

    if($row['student_id'] == $student_id){
        $student_position = $position;
        break;
    }

    $position++;
}
?>

<div class="container-fluid">

<div class="row">

<?php include '../includes/students_sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2>
Welcome,
<?= e($student['full_name'] ?? $_SESSION['username']); ?>
</h2>

<div class="alert alert-success">

Registration Number:
<strong><?= e($student['reg_no'] ?? 'Not Assigned'); ?></strong>

<br>

Class:
<strong><?= e($student['class'] ?? 'Not Assigned'); ?></strong>

</div>

<div class="row">

<div class="col-md-4 mb-4">

<div class="card shadow text-center">

<div class="card-body">

<h2><?= $average; ?>%</h2>

<h5>Average Marks</h5>

</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card shadow text-center">

<div class="card-body">

<h2><?= $subject_count; ?></h2>

<h5>Subjects Done</h5>

</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card shadow text-center">

<div class="card-body">

<h2><?= $student_position; ?></h2>

<h5>Class Position</h5>

</div>

</div>

</div>

</div>

<div class="card shadow mt-4">

<div class="card-header bg-primary text-white">

Quick Actions

</div>

<div class="card-body">

<a href="results.php" class="btn btn-primary m-2">
Results
</a>

<a href="report_card.php" class="btn btn-success m-2">
Report Card
</a>

<a href="timetable.php" class="btn btn-info m-2">
Timetable
</a>

<a href="fees.php" class="btn btn-warning m-2">
Fees
</a>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>