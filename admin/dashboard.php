<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Dashboard";

include '../includes/header.php';
include '../includes/navbar.php';

/* Dashboard Statistics */
function table_count($conn, $table)
{
    $allowed = ['students', 'teachers', 'subjects', 'marks', 'users'];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM $table"
    );

    $row = mysqli_fetch_assoc($result);

    return (int)($row['total'] ?? 0);
}

$student_count = table_count($conn, 'students');


$teacher_count = table_count($conn, 'teachers');
$subject_count = table_count($conn, 'subjects');
$marks_count   = table_count($conn, 'marks');
$user_count    = table_count($conn, 'users');
?>

<div class="container-fluid">

<div class="row">

<?php include '../includes/admin_sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2 class="mb-4 fw-bold">
    School Administration Dashboard
</h2>

<!-- Statistics Cards -->
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-body text-center">
                <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                <h2><?= e($student_count); ?></h2>
                <h5>Total Students</h5>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-3x text-success mb-3"></i>
                <h2><?= e($teacher_count); ?></h2>
                <h5>Total Teachers</h5>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-body text-center">
                <i class="fas fa-book fa-3x text-warning mb-3"></i>
                <h2><?= e($subject_count); ?></h2>
                <h5>Total Subjects</h5>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-danger mb-3"></i>
                <h2><?= e($user_count); ?></h2>
                <h5>Total Users</h5>
            </div>
        </div>
    </div>

</div>

<!-- Welcome Card -->
<div class="card shadow border-0 mb-4">

    <div class="card-body">

        <h3>Welcome Administrator 👋</h3>

        <p class="text-muted">
            Welcome to the School ERP Management System.
            Use the quick actions below to manage students,
            teachers, subjects, examinations and reports.
        </p>

    </div>

</div>

<!-- Quick Actions -->
<div class="card shadow mb-4">

    <div class="card-header bg-primary text-white">
        Quick Actions
    </div>

    <div class="card-body">

        <a href="students.php" class="btn btn-primary m-2">
            Add Student
        </a>

        <a href="teachers.php" class="btn btn-success m-2">
            Add Teacher
        </a>

        <a href="subjects.php" class="btn btn-warning m-2">
            Subjects
        </a>

        <a href="timetables.php" class="btn btn-info m-2">
            Timetable
        </a>

        <a href="../admin/marks.php" class="btn btn-danger m-2">
            Results
        </a>

        <a href="settings.php" class="btn btn-secondary m-2">
            Settings
        </a>

    </div>

</div>

<div class="row">

<!-- System Information -->
<div class="col-md-6">

    <div class="card shadow mb-4">

        <div class="card-header bg-success text-white">
            System Information
        </div>

        <div class="card-body">

            <table class="table">

                <tr>
                    <th>PHP Version</th>
                    <td><?= phpversion(); ?></td>
                </tr>

                <tr>
                    <th>Database</th>
                    <td>MySQL</td>
                </tr>

                <tr>
                    <th>Server</th>
                    <td><?= $_SERVER['SERVER_NAME']; ?></td>
                </tr>

                <tr>
                    <th>Date</th>
                    <td><?= date('d M Y'); ?></td>
                </tr>

            </table>

        </div>

    </div>

</div>

<!-- Administrator Notes -->
<div class="col-md-6">

    <div class="card shadow mb-4">

        <div class="card-header bg-warning">
            Administrator Notes
        </div>

        <div class="card-body">

            <ul>

                <li>Check new student registrations.</li>
                <li>Update attendance records.</li>
                <li>Review examination results.</li>
                <li>Monitor fee payments.</li>
                <li>Publish school announcements.</li>

            </ul>

        </div>

    </div>

</div>

</div>

<div class="row">

<!-- Recent Students -->
<div class="col-lg-6">

    <div class="card shadow mb-4">

        <div class="card-header bg-success text-white">
            Recent Students
        </div>

        <div class="card-body">

            <table class="table table-striped">

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Class</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $students = mysqli_query(
                    $conn,
                    "SELECT full_name, class
                     FROM students
                     ORDER BY student_id DESC
                     LIMIT 5"
                );

                while($row = mysqli_fetch_assoc($students)):
                ?>

                <tr>
                    <td><?= e($row['full_name']); ?></td>
                    <td><?= e($row['class']); ?></td>
                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


                

        </div>

    </div>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>