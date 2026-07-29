<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('teacher');

$page_title = "Teacher Dashboard";

include '../includes/header.php';
include '../includes/navbar.php';

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT full_name, subject_speciality
     FROM teachers
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$teacher = mysqli_fetch_assoc($result);

/* Statistics */
$students_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM students"
    )
)['total'];

$subjects_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM subjects"
    )
)['total'];
?>

<div class="container-fluid">

<div class="row">

<?php include '../includes/teachers_sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2 class="mb-4">
    Welcome,
    <?= e($teacher['full_name'] ?? $_SESSION['username']); ?>
</h2>

<div class="alert alert-info">
    Subject Speciality:
    <strong>
        <?= e($teacher['subject_speciality'] ?? 'Not Assigned'); ?>
    </strong>
</div>

<div class="row">

    <div class="col-md-6 col-lg-3 mb-4">

        <div class="card shadow text-center">

            <div class="card-body">

                <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>

                <h2><?= $students_count; ?></h2>

                <h5>Students</h5>

            </div>

        </div>

    </div>

    <div class="col-md-6 col-lg-3 mb-4">

        <div class="card shadow text-center">

            <div class="card-body">

                <i class="fas fa-book fa-3x text-success mb-3"></i>

                <h2><?= $subjects_count; ?></h2>

                <h5>Subjects</h5>

            </div>

        </div>

    </div>

</div>

<div class="card shadow mt-4">

    <div class="card-header bg-primary text-white">
        Quick Actions
    </div>

    <div class="card-body">

        <a href="marks.php" class="btn btn-primary m-2">
            Enter Marks
        </a>

        <a href="attendance.php" class="btn btn-success m-2">
            Attendance
        </a>

        <a href="timetable.php" class="btn btn-info m-2">
            Timetable
        </a>

        <a href="profile.php" class="btn btn-warning m-2">
            Profile
        </a>

    </div>

</div>

<div class="card shadow mt-4">

    <div class="card-header bg-success text-white">
        Teacher Notes
    </div>

    <div class="card-body">

        <ul>

            <li>Submit student marks before examinations close.</li>

            <li>Update attendance records daily.</li>

            <li>Review student performance trends.</li>

            <li>Check timetable updates regularly.</li>

        </ul>

    </div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>