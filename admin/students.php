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
    "SELECT group_id, group_name
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name ASC"
);


// =====================================================
// SAVE STUDENT
// =====================================================

if (isset($_POST['save'])) {

    // Student information
    $reg_no = trim($_POST['reg_no'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? '';

    // Academic information
    $class = trim($_POST['class'] ?? '');
    $stream = trim($_POST['stream'] ?? '');
    $group_id = (int) ($_POST['group_id'] ?? 0);

    // Academic group
    $group_id = (int) ($_POST['group_id'] ?? 0);

    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $admission_date = $_POST['admission_date'] ?? '';

    $status = $_POST['status'] ?? 'Active';

    // Other student information
    $nationality = trim($_POST['nationality'] ?? '');
    $religion = trim($_POST['religion'] ?? '');

    // Parent information
    $parent_name = trim($_POST['parent_name'] ?? '');
    $parent_contact = trim($_POST['parent_contact'] ?? '');
    $parent_email = trim($_POST['parent_email'] ?? '');
    $parent_address = trim($_POST['parent_address'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');

    // Medical information
    $blood_group = trim($_POST['blood_group'] ?? '');
    $allergies = trim($_POST['allergies'] ?? '');
    $medical_condition = trim($_POST['medical_condition'] ?? '');

    // Other
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


        // =============================================
        // PHOTO UPLOAD
        // =============================================

        $photo = null;

        if (
            isset($_FILES['photo']) &&
            $_FILES['photo']['error'] === UPLOAD_ERR_OK
        ) {

            $upload_directory = "../uploads/students/";

            // Create folder if it doesn't exist
            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0777, true);
            }


            $original_name = basename($_FILES['photo']['name']);

            $extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );


            // Generate unique filename
            $photo = time() . '_' . uniqid() . '.' . $extension;


            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                $upload_directory . $photo
            );
        }


        // =============================================
        // INSERT STUDENT
        // =============================================
 
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
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
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

    $message = "Student registered successfully.";

} else {

    $error = mysqli_error($conn);

}

            mysqli_stmt_close($stmt);
        }
    }


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

<option value="Male">
    Male
</option>

<option value="Female">
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
    placeholder="Example: P4, S2, Year 1"
>

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
    placeholder="Example: East, Science"
>

</div>


<!-- ACADEMIC GROUP -->

<div class="col-md-6 mb-3">

<label class="form-label">
    Academic Group
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
>

    <?= e($group['group_name']); ?>

</option>

<?php endwhile; ?>


</select>

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

<option value="Active">
    Active
</option>

<option value="Inactive">
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
>

</div>


<div class="col-md-12 mb-3">

<label class="form-label">
    Address
</label>

<textarea
    name="parent_address"
    class="form-control"
></textarea>

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
></textarea>

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