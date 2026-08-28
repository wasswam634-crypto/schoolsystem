```php
<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/* GET STUDENT ID */

$id = (int) ($_GET['id'] ?? 0);


/* GET STUDENT */

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM students WHERE student_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$row = mysqli_fetch_assoc($result);


if (!$row) {

    die("Student not found.");

}


/* UPDATE STUDENT */

if (isset($_POST['update'])) {


    /* STUDENT INFORMATION */

    $reg_no = trim($_POST['reg_no'] ?? '');

    $full_name = trim($_POST['full_name'] ?? '');

    $gender = $_POST['gender'] ?? '';

    $date_of_birth = $_POST['date_of_birth'] ?? '';

    $nationality = trim($_POST['nationality'] ?? '');

    $religion = trim($_POST['religion'] ?? '');


    /* ACADEMIC INFORMATION */

    $class = trim($_POST['class'] ?? '');

    $stream = trim($_POST['stream'] ?? '');

    $admission_date = $_POST['admission_date'] ?? '';

    $status = $_POST['status'] ?? 'Active';

    $previous_school = trim($_POST['previous_school'] ?? '');

    $group_id = (int) ($_POST['group_id'] ?? 0);


    /* PARENT / GUARDIAN */

    $parent_name = trim($_POST['parent_name'] ?? '');

    $parent_contact = trim($_POST['parent_contact'] ?? '');

    $parent_email = trim($_POST['parent_email'] ?? '');

    $parent_address = trim($_POST['parent_address'] ?? '');

    $occupation = trim($_POST['occupation'] ?? '');


    /* MEDICAL INFORMATION */

    $blood_group = trim($_POST['blood_group'] ?? '');

    $allergies = trim($_POST['allergies'] ?? '');

    $medical_condition = trim($_POST['medical_condition'] ?? '');


    /* OTHER INFORMATION */

    $notes = trim($_POST['notes'] ?? '');


    /* KEEP EXISTING PHOTO */

    $photo = $row['photo'];


    /*
     * If a new photo was selected,
     * upload the new photo.
     */

    if (
        isset($_FILES['photo']) &&
        $_FILES['photo']['error'] === UPLOAD_ERR_OK
    ) {

        $photo = time() . '_' . basename($_FILES['photo']['name']);

        $upload_path = "../uploads/students/" . $photo;


        if (!move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            $upload_path
        )) {

            die("Failed to upload the new student photo.");

        }

    }


    /* UPDATE DATABASE */

    $update = mysqli_prepare(

        $conn,

        "UPDATE students SET

            reg_no = ?,
            full_name = ?,
            gender = ?,
            class = ?,
            stream = ?,
            dob = ?,
            admission_date = ?,
            status = ?,
            nationality = ?,
            religion = ?,
            parent_name = ?,
            parent_contact = ?,
            parent_email = ?,
            parent_address = ?,
            occupation = ?,
            photo = ?,
            blood_group = ?,
            allergies = ?,
            medical_condition = ?,
            previous_school = ?,
            notes = ?,
            group_id = ?

         WHERE student_id = ?"

    );


    mysqli_stmt_bind_param(

        $update,

        "ssssssssssssssssssssssi",

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
        $group_id,
        $id

    );


    if (mysqli_stmt_execute($update)) {

        redirect("view_students.php");

    } else {

        die(
            "Error updating student: "
            . mysqli_stmt_error($update)
        );

    }

}


/* GET ACTIVE GROUPS */

$groups = mysqli_query(

    $conn,

    "SELECT group_id, group_name
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name"

);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Edit Student</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>


<body>


<div class="container mt-5 mb-5">


<h2 class="mb-4">
    Edit Student
</h2>


<form
    method="POST"
    enctype="multipart/form-data"
>


<!-- ========================= -->
<!-- STUDENT INFORMATION -->
<!-- ========================= -->

<div class="card mb-4">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            Student Information
        </h5>

    </div>


    <div class="card-body row">


        <!-- REGISTRATION NUMBER -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Registration Number
            </label>

            <input
                type="text"
                name="reg_no"
                class="form-control"
                value="<?= e($row['reg_no']); ?>"
                required
            >

        </div>


        <!-- FULL NAME -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Full Name
            </label>

            <input
                type="text"
                name="full_name"
                class="form-control"
                value="<?= e($row['full_name']); ?>"
                required
            >

        </div>


        <!-- GENDER -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Gender
            </label>

            <select
                name="gender"
                class="form-control"
            >

                <option
                    value="Male"
                    <?= ($row['gender'] == 'Male') ? 'selected' : ''; ?>
                >
                    Male
                </option>

                <option
                    value="Female"
                    <?= ($row['gender'] == 'Female') ? 'selected' : ''; ?>
                >
                    Female
                </option>

            </select>

        </div>


        <!-- DATE OF BIRTH -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Date of Birth
            </label>

            <input
                type="date"
                name="date_of_birth"
                class="form-control"
                value="<?= e($row['dob']); ?>"
            >

        </div>


        <!-- NATIONALITY -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Nationality
            </label>

            <input
                type="text"
                name="nationality"
                class="form-control"
                value="<?= e($row['nationality']); ?>"
            >

        </div>


        <!-- RELIGION -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Religion
            </label>

            <input
                type="text"
                name="religion"
                class="form-control"
                value="<?= e($row['religion']); ?>"
            >

        </div>


        <!-- PHOTO -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Student Photo
            </label>

            <input
                type="file"
                name="photo"
                class="form-control"
            >

            <?php if (!empty($row['photo'])): ?>

                <div class="mt-2">

                    <small class="text-muted">
                        Current photo:
                    </small>

                    <br>

                    <img
                        src="../uploads/students/<?= e($row['photo']); ?>"
                        alt="Student Photo"
                        width="100"
                        height="100"
                        style="object-fit:cover;border-radius:10px;"
                    >

                </div>

            <?php endif; ?>

        </div>


    </div>

</div>


<!-- ========================= -->
<!-- ACADEMIC INFORMATION -->
<!-- ========================= -->

<div class="card mb-4">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">
            Academic Information
        </h5>

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
                value="<?= e($row['class']); ?>"
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
                value="<?= e($row['stream']); ?>"
                placeholder="Example: East, Science"
            >

        </div>


        <!-- CLASS / COURSE -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Class / Course
            </label>

            <select
                name="group_id"
                class="form-control"
                required
            >

                <option value="">
                    Select Group
                </option>


                <?php while ($group = mysqli_fetch_assoc($groups)): ?>

                    <option
                        value="<?= e($group['group_id']); ?>"
                        <?= ($row['group_id'] == $group['group_id']) ? 'selected' : ''; ?>
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
                value="<?= e($row['admission_date']); ?>"
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

                <option
                    value="Active"
                    <?= ($row['status'] == 'Active') ? 'selected' : ''; ?>
                >
                    Active
                </option>

                <option
                    value="Inactive"
                    <?= ($row['status'] == 'Inactive') ? 'selected' : ''; ?>
                >
                    Inactive
                </option>

            </select>

        </div>


        <!-- PREVIOUS SCHOOL -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Previous School
            </label>

            <input
                type="text"
                name="previous_school"
                class="form-control"
                value="<?= e($row['previous_school']); ?>"
            >

        </div>


    </div>

</div>


<!-- ========================= -->
<!-- PARENT / GUARDIAN -->
<!-- ========================= -->

<div class="card mb-4">

    <div class="card-header bg-warning">

        <h5 class="mb-0">
            Parent / Guardian Information
        </h5>

    </div>


    <div class="card-body row">


        <!-- NAME -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Name
            </label>

            <input
                type="text"
                name="parent_name"
                class="form-control"
                value="<?= e($row['parent_name']); ?>"
            >

        </div>


        <!-- CONTACT -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Contact
            </label>

            <input
                type="text"
                name="parent_contact"
                class="form-control"
                value="<?= e($row['parent_contact']); ?>"
            >

        </div>


        <!-- EMAIL -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Email
            </label>

            <input
                type="email"
                name="parent_email"
                class="form-control"
                value="<?= e($row['parent_email']); ?>"
            >

        </div>


        <!-- OCCUPATION -->

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Occupation
            </label>

            <input
                type="text"
                name="occupation"
                class="form-control"
                value="<?= e($row['occupation']); ?>"
            >

        </div>


        <!-- ADDRESS -->

        <div class="col-md-12 mb-3">

            <label class="form-label">
                Address
            </label>

            <textarea
                name="parent_address"
                class="form-control"
                rows="3"
            ><?= e($row['parent_address']); ?></textarea>

        </div>


    </div>

</div>


<!-- ========================= -->
<!-- MEDICAL INFORMATION -->
<!-- ========================= -->

<div class="card mb-4">

    <div class="card-header bg-danger text-white">

        <h5 class="mb-0">
            Medical Information
        </h5>

    </div>


    <div class="card-body row">


        <!-- BLOOD GROUP -->

        <div class="col-md-4 mb-3">

            <label class="form-label">
                Blood Group
            </label>

            <input
                type="text"
                name="blood_group"
                class="form-control"
                value="<?= e($row['blood_group']); ?>"
            >

        </div>


        <!-- ALLERGIES -->

        <div class="col-md-4 mb-3">

            <label class="form-label">
                Allergies
            </label>

            <input
                type="text"
                name="allergies"
                class="form-control"
                value="<?= e($row['allergies']); ?>"
            >

        </div>


        <!-- MEDICAL CONDITION -->

        <div class="col-md-4 mb-3">

            <label class="form-label">
                Medical Condition
            </label>

            <input
                type="text"
                name="medical_condition"
                class="form-control"
                value="<?= e($row['medical_condition']); ?>"
            >

        </div>


    </div>

</div>


<!-- ========================= -->
<!-- OTHER INFORMATION -->
<!-- ========================= -->

<div class="card mb-4">

    <div class="card-header">

        <h5 class="mb-0">
            Other Information
        </h5>

    </div>


    <div class="card-body">

        <label class="form-label">
            Notes
        </label>

        <textarea
            name="notes"
            class="form-control"
            rows="4"
        ><?= e($row['notes']); ?></textarea>

    </div>

</div>


<!-- ========================= -->
<!-- BUTTONS -->
<!-- ========================= -->

<div class="mb-5">

    <button
        type="submit"
        name="update"
        class="btn btn-success btn-lg"
    >

        Update Student

    </button>


    <a
        href="view_students.php"
        class="btn btn-secondary btn-lg"
    >

        Cancel

    </a>

</div>


</form>


</div>

</body>

</html>
```
