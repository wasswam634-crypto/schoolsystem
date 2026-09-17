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


            <!-- =================================================
                 DASHBOARD
            ================================================== -->

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


            <!-- STUDENTS -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/students.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-user-graduate me-2"></i>

                    Students

                </a>

            </li>


            <!-- STUDENT LIST -->

            <li class="nav-item mb-1">

                <a
                    href="../admin/view_students.php"
                    class="nav-link text-white rounded"
                >

                    <i class="fas fa-users me-2"></i>

                    Student List

                </a>

            </li>


            <!-- =================================================
                 ACADEMIC MANAGEMENT
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                Academic Management

            </div>

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
                 SCHOOL WEBSITE
            ================================================== -->

            <div class="text-uppercase text-secondary small fw-bold mt-3 mb-2">

                School Website

            </div>


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


            <!-- VIEW WEBSITE -->

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