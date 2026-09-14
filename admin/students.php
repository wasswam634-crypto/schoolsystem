<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = null;
$error = null;


// =====================================================
// GET ACTIVE ACADEMIC GROUPS
// =====================================================

$groups = mysqli_query(
    $conn,
    "SELECT group_id, group_name, group_type
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name ASC"
);


// =====================================================
// SAVE STUDENT
// =====================================================

if (isset($_POST['save'])) {

    // =================================================
    // STUDENT INFORMATION
    // =================================================

    $reg_no = trim($_POST['reg_no'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $admission_date = $_POST['admission_date'] ?? '';

    // =================================================
    // ACADEMIC INFORMATION
    // =================================================

    $class = trim($_POST['class'] ?? '');
    $stream = trim($_POST['stream'] ?? '');

    // Academic group
    $group_id = (int) ($_POST['group_id'] ?? 0);

    $status = $_POST['status'] ?? 'Active';

    // =================================================
    // OTHER STUDENT INFORMATION
    // =================================================

    $nationality = trim($_POST['nationality'] ?? '');
    $religion = trim($_POST['religion'] ?? '');

    // =================================================
    // PARENT INFORMATION
    // =================================================

    $parent_name = trim($_POST['parent_name'] ?? '');
    $parent_contact = trim($_POST['parent_contact'] ?? '');
    $parent_email = trim($_POST['parent_email'] ?? '');
    $parent_address = trim($_POST['parent_address'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');

    // =================================================
    // MEDICAL INFORMATION
    // =================================================

    $blood_group = trim($_POST['blood_group'] ?? '');
    $allergies = trim($_POST['allergies'] ?? '');
    $medical_condition = trim($_POST['medical_condition'] ?? '');

    // =================================================
    // OTHER
    // =================================================

    $previous_school = trim($_POST['previous_school'] ?? '');
    $notes = trim($_POST['notes'] ?? '');


    // =================================================
    // BASIC VALIDATION
    // =================================================

    if ($reg_no === '') {

        $error = "Registration number is required.";

    } elseif ($full_name === '') {

        $error = "Full name is required.";

    } elseif ($group_id <= 0) {

        $error = "Please select an academic group.";

    } else {


        // =================================================
        // VERIFY ACADEMIC GROUP EXISTS AND IS ACTIVE
        // =================================================

        $group_stmt = mysqli_prepare(
            $conn,
            "SELECT group_id, group_name
             FROM academic_groups
             WHERE group_id = ?
             AND status = 'Active'
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $group_stmt,
            "i",
            $group_id
        );

        mysqli_stmt_execute($group_stmt);

        $group_result = mysqli_stmt_get_result($group_stmt);

        $selected_group = mysqli_fetch_assoc($group_result);

        mysqli_stmt_close($group_stmt);


        if (!$selected_group) {

            $error = "The selected academic group does not exist or is inactive.";

        } else {


            // =================================================
            // CHECK DUPLICATE REGISTRATION NUMBER
            // =================================================

            $check_stmt = mysqli_prepare(
                $conn,
                "SELECT student_id
                 FROM students
                 WHERE reg_no = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $check_stmt,
                "s",
                $reg_no
            );

            mysqli_stmt_execute($check_stmt);

            $check_result = mysqli_stmt_get_result($check_stmt);

            $existing_student = mysqli_fetch_assoc($check_result);

            mysqli_stmt_close($check_stmt);


            if ($existing_student) {

                $error = "A student with registration number "
                       . e($reg_no)
                       . " already exists.";

            } else {


                // =================================================
                // PHOTO UPLOAD
                // =================================================

                $photo = null;

                if (
                    isset($_FILES['photo']) &&
                    $_FILES['photo']['error'] === UPLOAD_ERR_OK
                ) {

                    $upload_directory = "../uploads/students/";

                    // Create directory if it does not exist
                    if (!is_dir($upload_directory)) {

                        mkdir(
                            $upload_directory,
                            0777,
                            true
                        );
                    }


                    $original_name = basename(
                        $_FILES['photo']['name']
                    );

                    $extension = strtolower(
                        pathinfo(
                            $original_name,
                            PATHINFO_EXTENSION
                        )
                    );


                    // Allowed image extensions
                    $allowed_extensions = [
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'webp'
                    ];


                    if (
                        !in_array(
                            $extension,
                            $allowed_extensions,
                            true
                        )
                    ) {

                        $error = "Invalid photo format. "
                               . "Allowed formats: JPG, JPEG, PNG, GIF and WEBP.";

                    } else {


                        // Generate unique filename
                        $photo =
                            time()
                            . '_'
                            . uniqid()
                            . '.'
                            . $extension;


                        if (
                            !move_uploaded_file(
                                $_FILES['photo']['tmp_name'],
                                $upload_directory . $photo
                            )
                        ) {

                            $error = "Failed to upload student photo.";

                        }

                    }

                }


                // =================================================
                // INSERT STUDENT
                // =================================================

                if (!$error) {

                    $stmt = mysqli_prepare(
                        $conn,

                        "INSERT INTO students (
                            reg_no,
                            full_name,
                            gender,
                            class,
                            stream,
                            dob,
                            admission_date,
                            status,
                            nationality,
                            religion,
                            parent_name,
                            parent_contact,
                            parent_email,
                            parent_address,
                            occupation,
                            photo,
                            blood_group,
                            allergies,
                            medical_condition,
                            previous_school,
                            notes,
                            group_id
                        )
                        VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                        )"
                    );


                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssssssssssssssssssssi",
                        $reg_no,
                        $full_name,
                        $gender,
                        $class,
                        $stream,
                        $date_of_birth,
                        $admission_date,
                        $status,
                        $nationality,
                        $religion,
                        $parent_name,
                        $parent_contact,
                        $parent_email,
                        $parent_address,
                        $occupation,
                        $photo,
                        $blood_group,
                        $allergies,
                        $medical_condition,
                        $previous_school,
                        $notes,
                        $group_id
                    );


                    if (mysqli_stmt_execute($stmt)) {

                        // Log activity
                        log_activity(
                            $conn,
                            $_SESSION['user_id'],
                            "Registered student "
                            . $full_name
                            . " ("
                            . $reg_no
                            . ") in academic group "
                            . $selected_group['group_name']
                        );


                        $message =
                            "Student registered successfully "
                            . "under "
                            . $selected_group['group_name']
                            . ".";

                    } else {

                        $error =
                            "Failed to register student: "
                            . mysqli_error($conn);

                    }


                    mysqli_stmt_close($stmt);
                }

            }

        }

    }

}


// =====================================================
// GET ACTIVE ACADEMIC GROUPS AGAIN
// =====================================================
// This is important because a new group may have been
// created before the page was submitted.

$groups = mysqli_query(
    $conn,
    "SELECT group_id, group_name, group_type
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name ASC"
);

?>

<!DOCTYPE html>

<html>

<head>

<title>Student Registration</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

</head>


<body>


<div class="container mt-5">


<h2 class="mb-4">
    Student Registration
</h2>


<!-- SUCCESS MESSAGE -->

<?php if ($message): ?>

<div class="alert alert-success">

    <?= e($message); ?>

</div>

<?php endif; ?>


<!-- ERROR MESSAGE -->

<?php if ($error): ?>

<div class="alert alert-danger">

    <?= e($error); ?>

</div>

<?php endif; ?>


<div class="mb-3">

<a
    href="export_students_excel.php"
    class="btn btn-success"
>
    Export Excel
</a>


<button
    onclick="window.print()"
    class="btn btn-primary"
>
    Print Students
</button>

</div>


<form
    method="POST"
    enctype="multipart/form-data"
>


<!-- =================================================
     STUDENT INFORMATION
================================================= -->

<div class="card mb-4">

<div class="card-header bg-primary text-white">

    Student Information

</div>


<div class="card-body row">


<div class="col-md-6 mb-3">

<label class="form-label">
    Registration Number
</label>

<input
    type="text"
    name="reg_no"
    class="form-control"
    value="<?= e($_POST['reg_no'] ?? '') ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Full Name
</label>

<input
    type="text"
    name="full_name"
    class="form-control"
    value="<?= e($_POST['full_name'] ?? '') ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Gender
</label>

<select
    name="gender"
    class="form-control"
>

<option value="Male"
    <?= (($_POST['gender'] ?? '') === 'Male')
        ? 'selected'
        : '' ?>>
    Male
</option>

<option value="Female"
    <?= (($_POST['gender'] ?? '') === 'Female')
        ? 'selected'
        : '' ?>>
    Female
</option>

</select>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Date of Birth
</label>

<input
    type="date"
    name="date_of_birth"
    class="form-control"
    value="<?= e($_POST['date_of_birth'] ?? '') ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Nationality
</label>

<input
    type="text"
    name="nationality"
    class="form-control"
    value="<?= e($_POST['nationality'] ?? '') ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Religion
</label>

<input
    type="text"
    name="religion"
    class="form-control"
    value="<?= e($_POST['religion'] ?? '') ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Student Photo
</label>

<input
    type="file"
    name="photo"
    class="form-control"
    accept="image/*"
>

</div>


</div>

</div>


<!-- =================================================
     ACADEMIC INFORMATION
================================================= -->

<div class="card mb-4">

<div class="card-header bg-success text-white">

    Academic Information

</div>


<div class="card-body row">


<!-- CLASS -->

<div class="col-md-6 mb-3">

<label class="form-label">
    Class
</label>

<input
    type="text"
    name="class"
    class="form-control"
    value="<?= e($_POST['class'] ?? '') ?>"
    placeholder="Example: P4, S2, Year 1"
>


<small class="text-muted">
    Enter the student's class or level.
</small>

</div>


<!-- STREAM -->

<div class="col-md-6 mb-3">

<label class="form-label">
    Stream
</label>

<input
    type="text"
    name="stream"
    class="form-control"
    value="<?= e($_POST['stream'] ?? '') ?>"
    placeholder="Example: East, Science"
>

</div>


<!-- ACADEMIC GROUP -->

<div class="col-md-6 mb-3">

<label class="form-label">

    Academic Group

    <span class="text-danger">*</span>

</label>


<select
    name="group_id"
    class="form-control"
    required
>

<option value="">
    -- Select Academic Group --
</option>


<?php while ($group = mysqli_fetch_assoc($groups)): ?>

<option
    value="<?= (int) $group['group_id']; ?>"
    <?= (
        (int)($_POST['group_id'] ?? 0)
        === (int)$group['group_id']
    )
        ? 'selected'
        : ''
    ?>
>

    <?= e($group['group_name']); ?>

    -
    <?= e($group['group_type']); ?>

</option>

<?php endwhile; ?>


</select>


<small class="text-muted">

    This determines which fee structure and fee account
    applies to the student.

</small>

</div>


<!-- ADMISSION DATE -->

<div class="col-md-6 mb-3">

<label class="form-label">
    Admission Date
</label>

<input
    type="date"
    name="admission_date"
    class="form-control"
    value="<?= e($_POST['admission_date'] ?? '') ?>"
>

</div>


<!-- STATUS -->

<div class="col-md-6 mb-3">

<label class="form-label">
    Status
</label>

<select
    name="status"
    class="form-control"
>

<option value="Active"
    <?= (($_POST['status'] ?? 'Active') === 'Active')
        ? 'selected'
        : '' ?>>
    Active
</option>

<option value="Inactive"
    <?= (($_POST['status'] ?? '') === 'Inactive')
        ? 'selected'
        : '' ?>>
    Inactive
</option>

</select>

</div>


<!-- PREVIOUS SCHOOL -->

<div class="col-md-12 mb-3">

<label class="form-label">
    Previous School
</label>

<input
    type="text"
    name="previous_school"
    class="form-control"
    value="<?= e($_POST['previous_school'] ?? '') ?>"
>

</div>


</div>

</div>


<!-- =================================================
     PARENT / GUARDIAN INFORMATION
================================================= -->

<div class="card mb-4">

<div class="card-header bg-warning">

    Parent / Guardian Information

</div>


<div class="card-body row">


<div class="col-md-6 mb-3">

<label class="form-label">
    Name
</label>

<input
    type="text"
    name="parent_name"
    class="form-control"
    value="<?= e($_POST['parent_name'] ?? '') ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Contact
</label>

<input
    type="text"
    name="parent_contact"
    class="form-control"
    value="<?= e($_POST['parent_contact'] ?? '') ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Email
</label>

<input
    type="email"
    name="parent_email"
    class="form-control"
    value="<?= e($_POST['parent_email'] ?? '') ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">
    Occupation
</label>

<input
    type="text"
    name="occupation"
    class="form-control"
    value="<?= e($_POST['occupation'] ?? '') ?>"
>

</div>


<div class="col-md-12 mb-3">

<label class="form-label">
    Address
</label>

<textarea
    name="parent_address"
    class="form-control"
><?= e($_POST['parent_address'] ?? '') ?></textarea>

</div>


</div>

</div>


<!-- =================================================
     MEDICAL INFORMATION
================================================= -->

<div class="card mb-4">

<div class="card-header bg-danger text-white">

    Medical Information

</div>


<div class="card-body row">


<div class="col-md-4 mb-3">

<label class="form-label">
    Blood Group
</label>

<input
    type="text"
    name="blood_group"
    class="form-control"
    value="<?= e($_POST['blood_group'] ?? '') ?>"
>

</div>


<div class="col-md-4 mb-3">

<label class="form-label">
    Allergies
</label>

<input
    type="text"
    name="allergies"
    class="form-control"
    value="<?= e($_POST['allergies'] ?? '') ?>"
>

</div>


<div class="col-md-4 mb-3">

<label class="form-label">
    Medical Condition
</label>

<input
    type="text"
    name="medical_condition"
    class="form-control"
    value="<?= e($_POST['medical_condition'] ?? '') ?>"
>

</div>


</div>

</div>


<!-- =================================================
     OTHER INFORMATION
================================================= -->

<div class="card mb-4">

<div class="card-header">

    Other Information

</div>


<div class="card-body">

<label class="form-label">
    Notes
</label>

<textarea
    name="notes"
    class="form-control"
><?= e($_POST['notes'] ?? '') ?></textarea>

</div>

</div>


<!-- SAVE -->

<button
    type="submit"
    class="btn btn-primary btn-lg"
    name="save"
>

    Save Student

</button>


</form>


</div>


</body>

</html>

