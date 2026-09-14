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



/*
|--------------------------------------------------------------------------
| HELPER FUNCTION
|--------------------------------------------------------------------------
*/

function table_count($conn, $table)
{
    $allowed = [
        'students',
        'teachers',
        'subjects',
        'marks',
        'users'
    ];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM `$table`"
    );

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int)($row['total'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$student_count = table_count($conn, 'students');
$teacher_count = table_count($conn, 'teachers');
$subject_count = table_count($conn, 'subjects');
$user_count    = table_count($conn, 'users');


/*
|--------------------------------------------------------------------------
| RECENT STUDENTS
|--------------------------------------------------------------------------
*/

$recent_students = mysqli_query(
    $conn,
    "SELECT student_id, full_name, class, admission_date
     FROM students
     ORDER BY student_id DESC
     LIMIT 5"
);


/*
|--------------------------------------------------------------------------
| CURRENT DATE
|--------------------------------------------------------------------------
*/

$current_date = date('l, d F Y');

?>

<style>

/* =========================================================
   GLOBAL DASHBOARD
========================================================= */

.dashboard-wrapper {
    background: #f5f7fb;
    min-height: calc(100vh - 70px);
}

.dashboard-content {
    padding: 30px 32px 40px;
}


/* =========================================================
   DASHBOARD HEADER
========================================================= */

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-title {
    font-size: 30px;
    font-weight: 750;
    letter-spacing: -0.5px;
    color: #172033;
    margin: 0 0 6px 0;
}

.dashboard-subtitle {
    color: #667085;
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.05px;
}

.dashboard-date {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #e5e9f0;
    padding: 10px 15px;
    border-radius: 10px;
    color: #596273;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 2px 7px rgba(16, 24, 40, 0.025);
}

.dashboard-date i {
    color: #2563eb;
}


/* =========================================================
   STATISTICS
========================================================= */

.stat-card {
    position: relative;
    background: #ffffff;
    border: 1px solid #e8ecf2;
    border-radius: 15px;
    padding: 22px;
    height: 100%;
    overflow: hidden;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;
}

.stat-card::after {
    content: "";
    position: absolute;
    right: -25px;
    bottom: -30px;
    width: 85px;
    height: 85px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.025);
    pointer-events: none;
}

.stat-card:hover {
    transform: translateY(-3px);
    border-color: #dce3ed;
    box-shadow: 0 12px 28px rgba(16, 24, 40, 0.07);
}

.stat-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-label {
    color: #667085;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
}

.stat-number {
    color: #111827;
    font-size: 30px;
    font-weight: 750;
    line-height: 1.15;
    letter-spacing: -0.5px;
}

.stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    position: relative;
    z-index: 1;
}

.icon-blue {
    background: #eaf2ff;
    color: #2563eb;
}

.icon-green {
    background: #eafaf1;
    color: #16a34a;
}

.icon-orange {
    background: #fff5e6;
    color: #ea580c;
}

.icon-purple {
    background: #f3eefe;
    color: #7c3aed;
}


/* =========================================================
   SECTION CARDS
========================================================= */

.section-card {
    background: #ffffff;
    border: 1px solid #e8ecf2;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(16, 24, 40, 0.025);
    transition: box-shadow 0.2s ease;
}

.section-card:hover {
    box-shadow: 0 6px 18px rgba(16, 24, 40, 0.045);
}

.section-header {
    min-height: 60px;
    padding: 16px 21px;
    border-bottom: 1px solid #edf0f4;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    letter-spacing: -0.1px;
}

.section-body {
    padding: 21px;
}


/* =========================================================
   QUICK ACTIONS
========================================================= */

.quick-action {
    position: relative;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 14px 13px;
    min-height: 70px;
    border: 1px solid #e8ebf0;
    border-radius: 11px;
    text-decoration: none;
    color: #374151;
    background: #ffffff;
    transition:
        transform 0.18s ease,
        border-color 0.18s ease,
        background 0.18s ease,
        box-shadow 0.18s ease;
}

.quick-action:hover {
    border-color: #cbdafc;
    background: #f8fbff;
    color: #2563eb;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 5px 14px rgba(37, 99, 235, 0.07);
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f6fa;
    color: #2563eb;
    font-size: 15px;
    transition: all 0.18s ease;
}

.quick-action:hover .quick-action-icon {
    background: #eaf2ff;
}

.quick-action-title {
    font-size: 13px;
    font-weight: 700;
    color: #263142;
    line-height: 1.25;
}

.quick-action-description {
    font-size: 11.5px;
    color: #8a94a6;
    font-weight: 500;
    margin-top: 3px;
    line-height: 1.3;
}


/* =========================================================
   TABLE
========================================================= */

.dashboard-table {
    margin-bottom: 0;
}

.dashboard-table thead th {
    background: #f8fafc;
    color: #667085;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .45px;
    border: none;
    padding: 13px 18px;
}

.dashboard-table tbody td {
    padding: 15px 18px;
    vertical-align: middle;
    border-color: #f0f2f5;
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
}

.dashboard-table tbody tr {
    transition: background 0.15s ease;
}

.dashboard-table tbody tr:hover {
    background: #fafcff;
}

.student-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #1f2937;
}

.student-class {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid #e6ebf1;
}


/* =========================================================
   VIEW ALL BUTTON
========================================================= */

.section-header .btn-light {
    background: #f8fafc;
    border: 1px solid #e7ebf0;
    color: #475467;
    font-size: 11.5px;
    font-weight: 600;
    padding: 6px 11px;
    border-radius: 7px;
    transition: all 0.18s ease;
}

.section-header .btn-light:hover {
    background: #eef4ff;
    border-color: #d5e2ff;
    color: #2563eb;
}


/* =========================================================
   SCHOOL OVERVIEW
========================================================= */

.overview-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 0;
    border-bottom: 1px solid #f0f2f5;
}

.overview-item:last-child {
    border-bottom: none;
}

.overview-left {
    display: flex;
    align-items: center;
    gap: 11px;
}

.overview-icon {
    width: 35px;
    height: 35px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    font-size: 14px;
}

.overview-label {
    font-size: 13px;
    font-weight: 600;
    color: #667085;
}

.overview-value {
    font-size: 15px;
    font-weight: 750;
    color: #1f2937;
}

.text-purple {
    color: #7c3aed !important;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
    text-align: center;
    padding: 36px 15px;
    color: #98a2b3;
    font-size: 13px;
    font-weight: 500;
}

.empty-state i {
    display: block;
    font-size: 28px;
    margin-bottom: 10px;
    color: #c1c9d4;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1199px) {

    .quick-action {
        min-height: 68px;
    }

}

@media (max-width: 991px) {

    .dashboard-content {
        padding: 24px 20px 35px;
    }

    .dashboard-header {
        margin-bottom: 25px;
    }

}

@media (max-width: 768px) {

    .dashboard-header {
        display: block;
    }

    .dashboard-date {
        display: inline-flex;
        margin-top: 12px;
    }

    .dashboard-title {
        font-size: 25px;
    }

    .dashboard-subtitle {
        font-size: 13px;
    }

    .stat-number {
        font-size: 27px;
    }

}

@media (max-width: 576px) {

    .dashboard-content {
        padding: 20px 15px 30px;
    }

    .section-body {
        padding: 16px;
    }

    .section-header {
        padding: 15px 16px;
    }

    .stat-card {
        padding: 18px;
    }

}

</style>


<!-- =========================================================
     DASHBOARD WRAPPER
========================================================= -->

<div class="dashboard-wrapper">

    <div class="row g-0">


        <!-- =================================================
             ADMIN SIDEBAR
        ================================================== -->

        <?php include '../includes/admin_sidebar.php'; ?>


        <!-- =================================================
             MAIN CONTENT
        ================================================== -->

        <main class="col-md-10">

            <div class="dashboard-content">


                <!-- =================================================
                     DASHBOARD HEADER
                ================================================== -->

                <div class="dashboard-header">

                    <div>

                        <h1 class="dashboard-title">
                            Dashboard
                        </h1>

                        <p class="dashboard-subtitle">
                            Welcome back, Administrator. Here's an overview of your school.
                        </p>

                    </div>


                    <div class="dashboard-date">

                        <i class="far fa-calendar-alt me-2"></i>

                        <?= e($current_date); ?>

                    </div>

                </div>



                <!-- =================================================
                     STATISTICS
                ================================================== -->

                <div class="row g-3 mb-4">


                    <!-- STUDENTS -->

                    <div class="col-xl-3 col-md-6">

                        <div class="stat-card">

                            <div class="stat-top">

                                <div>

                                    <div class="stat-label">
                                        Total Students
                                    </div>

                                    <div class="stat-number">
                                        <?= e($student_count); ?>
                                    </div>

                                </div>


                                <div class="stat-icon icon-blue">

                                    <i class="fas fa-user-graduate"></i>

                                </div>

                            </div>

                        </div>

                    </div>



                    <!-- TEACHERS -->

                    <div class="col-xl-3 col-md-6">

                        <div class="stat-card">

                            <div class="stat-top">

                                <div>

                                    <div class="stat-label">
                                        Teachers
                                    </div>

                                    <div class="stat-number">
                                        <?= e($teacher_count); ?>
                                    </div>

                                </div>


                                <div class="stat-icon icon-green">

                                    <i class="fas fa-chalkboard-teacher"></i>

                                </div>

                            </div>

                        </div>

                    </div>



                    <!-- SUBJECTS -->

                    <div class="col-xl-3 col-md-6">

                        <div class="stat-card">

                            <div class="stat-top">

                                <div>

                                    <div class="stat-label">
                                        Subjects
                                    </div>

                                    <div class="stat-number">
                                        <?= e($subject_count); ?>
                                    </div>

                                </div>


                                <div class="stat-icon icon-orange">

                                    <i class="fas fa-book"></i>

                                </div>

                            </div>

                        </div>

                    </div>



                    <!-- USERS -->

                    <div class="col-xl-3 col-md-6">

                        <div class="stat-card">

                            <div class="stat-top">

                                <div>

                                    <div class="stat-label">
                                        System Users
                                    </div>

                                    <div class="stat-number">
                                        <?= e($user_count); ?>
                                    </div>

                                </div>


                                <div class="stat-icon icon-purple">

                                    <i class="fas fa-users"></i>

                                </div>

                            </div>

                        </div>

                    </div>


                </div>



                <!-- =================================================
                     QUICK ACTIONS
                ================================================== -->

                <div class="section-card mb-4">


                    <div class="section-header">

                        <h5 class="section-title">
                            Quick Actions
                        </h5>

                    </div>


                    <div class="section-body">

                        <div class="row g-3">


                            <!-- STUDENTS -->

                            <div class="col-xl-2 col-md-4 col-sm-6">

                                <a href="students.php"
                                   class="quick-action">

                                    <div class="quick-action-icon">

                                        <i class="fas fa-user-plus"></i>

                                    </div>

                                    <div>

                                        <div class="quick-action-title">
                                            Students
                                        </div>

                                        <div class="quick-action-description">
                                            Manage students
                                        </div>

                                    </div>

                                </a>

                            </div>



                            <!-- TEACHERS -->

                            <div class="col-xl-2 col-md-4 col-sm-6">

                                <a href="teachers.php"
                                   class="quick-action">

                                    <div class="quick-action-icon">

                                        <i class="fas fa-user-tie"></i>

                                    </div>

                                    <div>

                                        <div class="quick-action-title">
                                            Teachers
                                        </div>

                                        <div class="quick-action-description">
                                            Manage teachers
                                        </div>

                                    </div>

                                </a>

                            </div>



                            <!-- SUBJECTS -->

                            <div class="col-xl-2 col-md-4 col-sm-6">

                                <a href="academic_subjects.php"
                                   class="quick-action">

                                    <div class="quick-action-icon">

                                        <i class="fas fa-book-open"></i>

                                    </div>

                                    <div>

                                        <div class="quick-action-title">
                                            Subjects
                                        </div>

                                        <div class="quick-action-description">
                                            Manage subjects
                                        </div>

                                    </div>

                                </a>

                            </div>



                            <!-- TIMETABLE -->

                            <div class="col-xl-2 col-md-4 col-sm-6">

                                <a href="timetables.php"
                                   class="quick-action">

                                    <div class="quick-action-icon">

                                        <i class="fas fa-calendar-alt"></i>

                                    </div>

                                    <div>

                                        <div class="quick-action-title">
                                            Timetable
                                        </div>

                                        <div class="quick-action-description">
                                            Manage timetable
                                        </div>

                                    </div>

                                </a>

                            </div>



                            <!-- RESULTS -->

                            <div class="col-xl-2 col-md-4 col-sm-6">

                                <a href="marks.php"
                                   class="quick-action">

                                    <div class="quick-action-icon">

                                        <i class="fas fa-chart-line"></i>

                                    </div>

                                    <div>

                                        <div class="quick-action-title">
                                            Results
                                        </div>

                                        <div class="quick-action-description">
                                            Manage marks
                                        </div>

                                    </div>

                                </a>

                            </div>



                            <!-- PAYMENTS -->

                            <div class="col-xl-2 col-md-4 col-sm-6">

                                <a href="fee_payments.php"
                                   class="quick-action">

                                    <div class="quick-action-icon">

                                        <i class="fas fa-cash-register"></i>

                                    </div>

                                    <div>

                                        <div class="quick-action-title">
                                            Payments
                                        </div>

                                        <div class="quick-action-description">
                                            Manage fees
                                        </div>

                                    </div>

                                </a>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- =================================================
                     LOWER DASHBOARD
                ================================================== -->

                <div class="row g-4">


                    <!-- =================================================
                         RECENT STUDENTS
                    ================================================== -->

                    <div class="col-lg-8">

                        <div class="section-card">


                            <div class="section-header">

                                <h5 class="section-title">
                                    Recently Registered Students
                                </h5>


                                <a href="view_students.php"
                                   class="btn btn-sm btn-light">

                                    View All

                                </a>

                            </div>



                            <div class="table-responsive">

                                <table class="table dashboard-table">


                                    <thead>

                                        <tr>

                                            <th>
                                                Student
                                            </th>

                                            <th>
                                                Class
                                            </th>

                                            <th>
                                                Admission Date
                                            </th>

                                        </tr>

                                    </thead>



                                    <tbody>


                                    <?php if (
                                        $recent_students &&
                                        mysqli_num_rows($recent_students) > 0
                                    ): ?>


                                        <?php while (
                                            $row = mysqli_fetch_assoc($recent_students)
                                        ): ?>


                                            <tr>


                                                <td>

                                                    <div class="student-name">

                                                        <?= e(
                                                            $row['full_name']
                                                        ); ?>

                                                    </div>

                                                </td>



                                                <td>

                                                    <span class="student-class">

                                                        <?= e(
                                                            $row['class']
                                                        ); ?>

                                                    </span>

                                                </td>



                                                <td>

                                                    <?=
                                                        !empty($row['admission_date'])
                                                        ? e(
                                                            date(
                                                                'd M Y',
                                                                strtotime(
                                                                    $row['admission_date']
                                                                )
                                                            )
                                                        )
                                                        : '—';
                                                    ?>

                                                </td>


                                            </tr>


                                        <?php endwhile; ?>


                                    <?php else: ?>


                                        <tr>

                                            <td colspan="3">

                                                <div class="empty-state">

                                                    <i class="fas fa-user-graduate"></i>

                                                    <div>
                                                        No students registered yet.
                                                    </div>

                                                </div>

                                            </td>

                                        </tr>


                                    <?php endif; ?>


                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>



                    <!-- =================================================
                         SCHOOL OVERVIEW
                    ================================================== -->

                    <div class="col-lg-4">

                        <div class="section-card">


                            <div class="section-header">

                                <h5 class="section-title">
                                    School Overview
                                </h5>

                            </div>



                            <div class="section-body">


                                <!-- STUDENTS -->

                                <div class="overview-item">

                                    <div class="overview-left">

                                        <div class="overview-icon text-primary">

                                            <i class="fas fa-user-graduate"></i>

                                        </div>

                                        <span class="overview-label">
                                            Students
                                        </span>

                                    </div>


                                    <span class="overview-value">

                                        <?= e($student_count); ?>

                                    </span>

                                </div>



                                <!-- TEACHERS -->

                                <div class="overview-item">

                                    <div class="overview-left">

                                        <div class="overview-icon text-success">

                                            <i class="fas fa-chalkboard-teacher"></i>

                                        </div>

                                        <span class="overview-label">
                                            Teachers
                                        </span>

                                    </div>


                                    <span class="overview-value">

                                        <?= e($teacher_count); ?>

                                    </span>

                                </div>



                                <!-- SUBJECTS -->

                                <div class="overview-item">

                                    <div class="overview-left">

                                        <div class="overview-icon text-warning">

                                            <i class="fas fa-book"></i>

                                        </div>

                                        <span class="overview-label">
                                            Subjects
                                        </span>

                                    </div>


                                    <span class="overview-value">

                                        <?= e($subject_count); ?>

                                    </span>

                                </div>



                                <!-- USERS -->

                                <div class="overview-item">

                                    <div class="overview-left">

                                        <div class="overview-icon text-purple">

                                            <i class="fas fa-users"></i>

                                        </div>

                                        <span class="overview-label">
                                            System Users
                                        </span>

                                    </div>


                                    <span class="overview-value">

                                        <?= e($user_count); ?>

                                    </span>

                                </div>


                            </div>

                        </div>

                    </div>


                </div>


            </div>

        </main>

    </div>

</div>


<?php include '../includes/footer.php'; ?>