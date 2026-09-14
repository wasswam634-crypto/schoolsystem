<div class="col-md-2 bg-dark text-white min-vh-100 p-0">

    <div class="p-3">

        <?php

        // =====================================================
        // SCHOOL INFORMATION
        // =====================================================

        $school_query = mysqli_query(
            $conn,
            "SELECT school_name, logo
             FROM school_settings
             LIMIT 1"
        );

        $school = mysqli_fetch_assoc($school_query);

        ?>


        <!-- =================================================
             SCHOOL LOGO
        ================================================== -->

        <?php if (!empty($school['logo'])): ?>

            <div class="text-center mb-3">

                <img
                    src="../uploads/logos/<?= e($school['logo']); ?>"
                    width="80"
                    height="80"
                    class="img-fluid rounded-circle border border-light"
                    style="object-fit: cover;"
                    alt="School Logo"
                >

            </div>

        <?php endif; ?>


        <!-- =================================================
             SCHOOL NAME
        ================================================== -->

        <h4 class="text-center mb-4">

            <?= e($school['school_name'] ?? 'School ERP'); ?>

        </h4>


        <hr class="bg-light">


        <!-- =================================================
             MAIN NAVIGATION
        ================================================== -->

        <div class="text-uppercase text-secondary small fw-bold mb-2">

            Main

        </div>


        <ul class="nav flex-column">


            <!-- DASHBOARD -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/dashboard.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-home me-2"></i>

                    Dashboard

                </a>

            </li>


            <!-- =================================================
                 STUDENT MANAGEMENT
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                Student Management

            </div>


            <!-- REGISTER / STUDENTS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/students.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-user-graduate me-2"></i>

                    Students

                </a>

            </li>


            <!-- VIEW STUDENTS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/view_students.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-users me-2"></i>

                    Student List

                </a>

            </li>


            <!-- ACADEMIC GROUPS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/academic_groups.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-layer-group me-2"></i>

                    Academic Groups

                </a>

            </li>


            <!-- =================================================
                 ACADEMIC MANAGEMENT
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                Academic Management

            </div>


            <!-- TEACHERS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/teachers.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-chalkboard-teacher me-2"></i>

                    Teachers

                </a>

            </li>


            <!-- SUBJECTS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/subjects.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-book me-2"></i>

                    Add new Subjects

                </a>

            </li>

            <li class="nav-item mb-1">

                <a
                    href="../admin/academic_subjects.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-book me-2"></i>

                    Subjects

                </a>

            </li>


            <!-- TIMETABLE -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/timetables.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-calendar-alt me-2"></i>

                    Timetable

                </a>

            </li>


            <!-- ACADEMIC PERIODS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/academic_periods.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-calendar-check me-2"></i>

                    Academic Periods

                </a>

            </li>


            <!-- MARKS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/marks.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-chart-bar me-2"></i>

                    Marks

                </a>

            </li>
             

            <li class="nav-item mb-1">

                <a
                    href="../admin/grading.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-chart-bar me-2"></i>

                    Set grading scale

                </a>

            </li>


            <!-- REPORT CARDS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/report_cards.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-file-alt me-2"></i>

                    Report Cards

                </a>

            </li>



            <!-- =================================================
                 FINANCE
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                Finance

            </div>


            <!-- FEE STRUCTURE -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/fee_structure.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-money-bill-wave me-2"></i>

                    Fee Structure

                </a>

            </li>


            <!-- GENERATE STUDENT FEES -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/generate_student_fees.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-file-invoice-dollar me-2"></i>

                    Student Fee Accounts

                </a>

            </li>


            <!-- FEE PAYMENTS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/fee_payments.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-cash-register me-2"></i>

                    Fee Payments

                </a>

            </li>



            <!-- =================================================
                 COMMUNICATION
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                Communication

            </div>


            <!-- ANNOUNCEMENTS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/announcements.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-bullhorn me-2"></i>

                    Announcements

                </a>

            </li>



            <!-- =================================================
                 SCHOOL WEBSITE
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                School Website

            </div>


            <!-- GALLERY -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/gallery.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-images me-2"></i>

                    Gallery

                </a>

            </li>


            <!-- VACANCIES -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/vacancies.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-briefcase me-2"></i>

                    Vacancies

                </a>

            </li>


            <!-- WEBSITE MANAGEMENT -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/website/index.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-globe me-2"></i>

                    Website Management

                </a>

            </li>
           

            <li class="nav-item mb-1">

                <a
                    href="<?= BASE_URL ?>index.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-globe me-2"></i>

                    view website

                </a>

            </li>



            <!-- =================================================
                 SYSTEM
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                System

            </div>


            <!-- BACKUP -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/backup.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-download me-2"></i>

                    Backup Database

                </a>

            </li>


            <!-- RESTORE -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/restore.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-upload me-2"></i>

                    Restore Database

                </a>

            </li>


            <!-- AUDIT LOGS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/audit_logs.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-history me-2"></i>

                    Audit Logs

                </a>

            </li>


            <!-- SETTINGS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/settings.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-cog me-2"></i>

                    Settings

                </a>

            </li>


            <!-- =================================================
                 LOGOUT
            ================================================== -->

            <li class="nav-item mt-4 pt-3 border-top border-secondary">

                <a
                    href="<?= BASE_URL ?>logout.php"
                    class="nav-link text-danger fw-bold rounded"
                >

                    <i class="fas fa-sign-out-alt me-2"></i>

                    Logout

                </a>

            </li>


        </ul>


    </div>

</div>


<!-- =========================================================
     SIDEBAR HOVER EFFECT
========================================================= -->

<style>

    .nav-link {

        transition:
            background-color 0.2s ease,
            padding-left 0.2s ease;

    }


    .nav-link:hover {

        background-color: rgba(255, 255, 255, 0.12);

        padding-left: 18px;

        color: #ffffff !important;

    }


    .nav-link.text-danger:hover {

        color: #ff6b6b !important;

    }


</style>
