<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/config.php';
include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Settings";

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| LOAD CURRENT SCHOOL SETTINGS FIRST
|--------------------------------------------------------------------------
*/

$result = mysqli_query(
    $conn,
    "SELECT * FROM school_settings LIMIT 1"
);

$settings = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| SAVE SCHOOL SETTINGS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $school_name = trim($_POST['school_name'] ?? '');
    $motto       = trim($_POST['motto'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $theme       = trim($_POST['theme'] ?? 'modern');

    /*
    | Keep existing logo unless a new one is uploaded
    */

    $logo_name = $settings['logo'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | LOGO UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['logo']) &&
        $_FILES['logo']['error'] === UPLOAD_ERR_OK
    ) {

        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $file_type = mime_content_type(
            $_FILES['logo']['tmp_name']
        );


        if (in_array($file_type, $allowed_types, true)) {

            $extension = strtolower(
                pathinfo(
                    $_FILES['logo']['name'],
                    PATHINFO_EXTENSION
                )
            );


            $new_name =
                uniqid('logo_', true) .
                '.' .
                $extension;


            $destination =
                '../uploads/logos/' .
                $new_name;


            if (
                move_uploaded_file(
                    $_FILES['logo']['tmp_name'],
                    $destination
                )
            ) {

                $logo_name = $new_name;

            } else {

                $error = "Unable to upload the school logo.";
            }

        } else {

            $error = "Invalid logo format. Please upload JPG, PNG or WEBP.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE TO DATABASE
    |--------------------------------------------------------------------------
    */

    if (empty($error)) {

        $check = mysqli_query(
            $conn,
            "SELECT id FROM school_settings LIMIT 1"
        );


        if (mysqli_num_rows($check) > 0) {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE school_settings
                 SET school_name=?,
                     motto=?,
                     address=?,
                     phone=?,
                     email=?,
                     logo=?,
                     theme=?
                 WHERE id=1"
            );


            mysqli_stmt_bind_param(
                $stmt,
                "sssssss",
                $school_name,
                $motto,
                $address,
                $phone,
                $email,
                $logo_name,
                $theme
            );


            if (mysqli_stmt_execute($stmt)) {

                $message = "Settings saved successfully.";

            } else {

                $error =
                    "Unable to save settings: " .
                    mysqli_error($conn);
            }


            mysqli_stmt_close($stmt);


        } else {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO school_settings
                (school_name, motto, address, phone, email, logo, theme)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );


            mysqli_stmt_bind_param(
                $stmt,
                "sssssss",
                $school_name,
                $motto,
                $address,
                $phone,
                $email,
                $logo_name,
                $theme
            );


            if (mysqli_stmt_execute($stmt)) {

                $message = "Settings saved successfully.";

            } else {

                $error =
                    "Unable to save settings: " .
                    mysqli_error($conn);
            }


            mysqli_stmt_close($stmt);
        }


        /*
        | Reload settings after saving
        */

        $result = mysqli_query(
            $conn,
            "SELECT * FROM school_settings LIMIT 1"
        );

        $settings = mysqli_fetch_assoc($result);
    }
}


include '../includes/header.php';
include '../includes/navbar.php';

?>


<div class="container-fluid">

    <div class="row">

        <?php include '../includes/admin_sidebar.php'; ?>


        <div class="col-md-10 p-4">


            <!-- =====================================================
                 PAGE TITLE
            ====================================================== -->

            <div class="mb-4">

                <h2 class="fw-bold mb-1">
                    Settings
                </h2>

                <p class="text-muted mb-0">
                    Manage school settings and system configuration.
                </p>

            </div>


            <!-- =====================================================
                 MESSAGES
            ====================================================== -->

            <?php if ($message): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <i class="fas fa-check-circle me-2"></i>

                    <?= e($message); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="alert alert-danger alert-dismissible fade show">

                    <i class="fas fa-exclamation-circle me-2"></i>

                    <?= e($error); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>


            <!-- =====================================================
                 SCHOOL SETTINGS
            ====================================================== -->

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-0 fw-bold">

                        <i class="fas fa-school me-2 text-primary"></i>

                        School Settings

                    </h5>

                </div>


                <div class="card-body">

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >


                        <div class="row">


                            <!-- SCHOOL NAME -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    School Name
                                </label>

                                <input
                                    type="text"
                                    name="school_name"
                                    class="form-control"
                                    value="<?= e($settings['school_name'] ?? ''); ?>"
                                >

                            </div>


                            <!-- MOTTO -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Motto
                                </label>

                                <input
                                    type="text"
                                    name="motto"
                                    class="form-control"
                                    value="<?= e($settings['motto'] ?? ''); ?>"
                                >

                            </div>


                            <!-- ADDRESS -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Address
                                </label>

                                <input
                                    type="text"
                                    name="address"
                                    class="form-control"
                                    value="<?= e($settings['address'] ?? ''); ?>"
                                >

                            </div>


                            <!-- PHONE -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    value="<?= e($settings['phone'] ?? ''); ?>"
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= e($settings['email'] ?? ''); ?>"
                                >

                            </div>


                            <!-- THEME -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Theme
                                </label>

                                <select
                                    name="theme"
                                    class="form-select"
                                >

                                    <option
                                        value="modern"
                                        <?= ($settings['theme'] ?? '') === 'modern'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        Modern Theme
                                    </option>


                                    <option
                                        value="classic"
                                        <?= ($settings['theme'] ?? '') === 'classic'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        Classic Theme
                                    </option>


                                    <option
                                        value="university"
                                        <?= ($settings['theme'] ?? '') === 'university'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        University Theme
                                    </option>

                                </select>

                            </div>


                            <!-- LOGO -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    School Logo
                                </label>

                                <input
                                    type="file"
                                    name="logo"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <small class="text-muted">
                                    JPG, PNG or WEBP
                                </small>

                            </div>


                            <!-- CURRENT LOGO -->

                            <?php if (!empty($settings['logo'])): ?>

                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-semibold">
                                        Current Logo
                                    </label>

                                    <div>

                                        <img
                                            src="../uploads/logos/<?= e($settings['logo']); ?>"
                                            width="100"
                                            height="100"
                                            class="img-thumbnail"
                                            style="object-fit: cover;"
                                            alt="School Logo"
                                        >

                                    </div>

                                </div>

                            <?php endif; ?>


                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-save me-2"></i>

                            Save Settings

                        </button>


                    </form>

                </div>

            </div>


            <!-- =====================================================
                 CONFIGURATION
            ====================================================== -->

            <div class="mb-3">

                <h4 class="fw-bold">
                    Configuration
                </h4>

                <p class="text-muted">
                    Manage the parts of the system that normally do not
                    require frequent changes.
                </p>

            </div>


            <div class="row g-4">


                <!-- =================================================
                     ACADEMIC GROUPS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-primary me-3">

                                    <i class="fas fa-layer-group"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Academic Groups
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Manage the academic groups used by
                                the school.

                            </p>


                            <a
                                href="../admin/academic_groups.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                Open Academic Groups

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ADD NEW SUBJECTS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-primary me-3">

                                    <i class="fas fa-book"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Add new Subjects
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Add and manage the subjects available
                                in the system.

                            </p>


                            <a
                                href="../admin/subjects.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                Open Add new Subjects

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SUBJECTS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-primary me-3">

                                    <i class="fas fa-book-open"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Subjects
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Manage subject assignments for
                                academic groups.

                            </p>


                            <a
                                href="../admin/academic_subjects.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                Open Subjects

                            </a>

                        </div>

                    </div>

                </div>


                 <!-- =================================================
                     STUDENT FEE ACCOUNTS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-success me-3">

                                    <i class="fas fa-file-invoice-dollar"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Teachers
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Manage teacher information and assignments.

                            </p>


                            <a
                                href="../admin/teachers.php"
                                class="btn btn-outline-success btn-sm"
                            >

                                Open Teachers page

                            </a>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     ACADEMIC PERIODS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-primary me-3">

                                    <i class="fas fa-calendar-check"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Academic Periods
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Configure the academic periods used
                                by the school.

                            </p>


                            <a
                                href="../admin/academic_periods.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                Open Academic Periods

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SET GRADING SCALE
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-primary me-3">

                                    <i class="fas fa-chart-bar"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Set grading scale
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Configure the grading scales used
                                when processing results.

                            </p>


                            <a
                                href="../admin/grading.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                Open Set grading scale

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SET REPORT CARD TEMPLATE
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-primary me-3">

                                    <i class="fas fa-file-alt"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Set report card template
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Configure the report card templates
                                used by the school.

                            </p>


                            <a
                                href="../admin/report_profiles.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                Open Set report card template

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     FEE STRUCTURE
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-success me-3">

                                    <i class="fas fa-money-bill-wave"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Fee Structure
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Configure the school's fee structure
                                and charges.

                            </p>


                            <a
                                href="../admin/fee_structure.php"
                                class="btn btn-outline-success btn-sm"
                            >

                                Open Fee Structure

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     STUDENT FEE ACCOUNTS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-success me-3">

                                    <i class="fas fa-file-invoice-dollar"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Student Fee Accounts
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Manage student fee accounts and
                                assigned fees.

                            </p>


                            <a
                                href="../admin/generate_student_fees.php"
                                class="btn btn-outline-success btn-sm"
                            >

                                Open Student Fee Accounts

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     BACKUP DATABASE
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-secondary me-3">

                                    <i class="fas fa-download"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Backup Database
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Create a backup of the school's
                                database.

                            </p>


                            <a
                                href="../admin/backup.php"
                                class="btn btn-outline-secondary btn-sm"
                            >

                                Open Backup Database

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     RESTORE DATABASE
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-secondary me-3">

                                    <i class="fas fa-upload"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Restore Database
                                </h5>

                            </div>


                            <p class="text-muted small">

                                Restore the school's database from
                                a previous backup.

                            </p>


                            <a
                                href="../admin/restore.php"
                                class="btn btn-outline-secondary btn-sm"
                            >

                                Open Restore Database

                            </a>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     AUDIT LOGS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="fs-3 text-secondary me-3">

                                    <i class="fas fa-history"></i>

                                </div>

                                <h5 class="mb-0 fw-bold">
                                    Audit Logs
                                </h5>

                            </div>


                            <p class="text-muted small">

                                View system activity and administrative
                                actions.

                            </p>


                            <a
                                href="../admin/audit_logs.php"
                                class="btn btn-outline-secondary btn-sm"
                            >

                                Open Audit Logs

                            </a>

                        </div>

                    </div>

                </div>


            </div>


        </div>

    </div>

</div>


<style>

    .card {

        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;

    }


    .card:hover {

        transform: translateY(-2px);

        box-shadow:
            0 0.5rem 1rem rgba(0, 0, 0, 0.10) !important;

    }


</style>


<?php

include '../includes/footer.php';

?>