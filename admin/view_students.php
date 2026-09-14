
<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$message = get_success();
$error   = get_error();


/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$year   = trim($_GET['year'] ?? '');
$class  = trim($_GET['class'] ?? '');
$stream = trim($_GET['stream'] ?? '');


/*
|--------------------------------------------------------------------------
| BUILD STUDENT QUERY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        s.*
    FROM students s
    WHERE 1=1
";


$params = [];
$types  = "";


/*
|--------------------------------------------------------------------------
| YEAR OF ENTRY FILTER
|--------------------------------------------------------------------------
*/

if ($year !== '') {

    $sql .= " AND YEAR(s.admission_date) = ? ";

    $params[] = $year;
    $types .= "i";
}


/*
|--------------------------------------------------------------------------
| CLASS FILTER
|--------------------------------------------------------------------------
*/

if ($class !== '') {

    $sql .= " AND s.class = ? ";

    $params[] = $class;
    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| STREAM FILTER
|--------------------------------------------------------------------------
*/

if ($stream !== '') {

    $sql .= " AND s.stream = ? ";

    $params[] = $stream;
    $types .= "s";
}


$sql .= " ORDER BY s.full_name ASC";


/*
|--------------------------------------------------------------------------
| PREPARE QUERY
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare($conn, $sql);


if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| LOAD YEARS OF ENTRY
|--------------------------------------------------------------------------
*/

$years = [];

$year_query = mysqli_query(
    $conn,

    "
    SELECT DISTINCT YEAR(admission_date) AS entry_year
    FROM students
    WHERE admission_date IS NOT NULL
    ORDER BY entry_year DESC
    "
);


while ($row = mysqli_fetch_assoc($year_query)) {

    if ($row['entry_year']) {

        $years[] = $row['entry_year'];
    }
}


/*
|--------------------------------------------------------------------------
| LOAD CLASSES
|--------------------------------------------------------------------------
*/

$classes = [];

$class_query = mysqli_query(
    $conn,

    "
    SELECT DISTINCT class
    FROM students
    WHERE class IS NOT NULL
    AND class != ''
    ORDER BY class
    "
);


while ($row = mysqli_fetch_assoc($class_query)) {

    $classes[] = $row['class'];
}


/*
|--------------------------------------------------------------------------
| LOAD STREAMS
|--------------------------------------------------------------------------
*/

$streams = [];

$stream_query = mysqli_query(
    $conn,

    "
    SELECT DISTINCT stream
    FROM students
    WHERE stream IS NOT NULL
    AND stream != ''
    ORDER BY stream
    "
);


while ($row = mysqli_fetch_assoc($stream_query)) {

    $streams[] = $row['stream'];
}

?>


<!DOCTYPE html>

<html>

<head>

<title>View Students</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">


<style>

body {
    background: #f4f6f9;
}


.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 3px 12px rgba(0,0,0,.08);
}


.student-photo {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 50%;
}


.table {
    background: white;
}


</style>

</head>


<body>


<div class="container-fluid p-4">


<!-- PAGE HEADER -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>

        <i class="bi bi-people"></i>

        All Students

    </h2>


    <a
        href="students.php"
        class="btn btn-primary">

        <i class="bi bi-person-plus"></i>

        Add Student

    </a>

</div>


<!-- SUCCESS MESSAGE -->

<?php if ($message): ?>

<div class="alert alert-success alert-dismissible fade show">

    <?= e($message); ?>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

<?php endif; ?>


<!-- ERROR MESSAGE -->

<?php if ($error): ?>

<div class="alert alert-danger alert-dismissible fade show">

    <?= e($error); ?>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

<?php endif; ?>


<!-- FILTER CARD -->

<div class="card mb-4">


<div class="card-header bg-dark text-white">

    <i class="bi bi-search"></i>

    Search / Filter Students

</div>


<div class="card-body">


<form method="GET">


<div class="row g-3">


<!-- YEAR -->

<div class="col-md-4">

<label class="form-label">

    Year of Entry

</label>


<select
    name="year"
    class="form-select">


<option value="">

    All Years

</option>


<?php foreach ($years as $entry_year): ?>

<option
    value="<?= e($entry_year); ?>"
    <?= ($year == $entry_year) ? 'selected' : ''; ?>>

    <?= e($entry_year); ?>

</option>

<?php endforeach; ?>


</select>

</div>


<!-- CLASS -->

<div class="col-md-4">

<label class="form-label">

    Class

</label>


<select
    name="class"
    class="form-select">


<option value="">

    All Classes

</option>


<?php foreach ($classes as $class_name): ?>

<option
    value="<?= e($class_name); ?>"
    <?= ($class == $class_name) ? 'selected' : ''; ?>>

    <?= e($class_name); ?>

</option>

<?php endforeach; ?>


</select>

</div>


<!-- STREAM -->

<div class="col-md-4">

<label class="form-label">

    Stream

</label>


<select
    name="stream"
    class="form-select">


<option value="">

    All Streams

</option>


<?php foreach ($streams as $stream_name): ?>

<option
    value="<?= e($stream_name); ?>"
    <?= ($stream == $stream_name) ? 'selected' : ''; ?>>

    <?= e($stream_name); ?>

</option>

<?php endforeach; ?>


</select>

</div>


<!-- BUTTONS -->

<div class="col-12">


<button
    type="submit"
    class="btn btn-primary">

    <i class="bi bi-search"></i>

    Search

</button>


<a
    href="view_students.php"
    class="btn btn-secondary">

    <i class="bi bi-arrow-clockwise"></i>

    Clear

</a>


</div>


</div>


</form>


</div>

</div>


<!-- STUDENT TABLE -->

<div class="card">


<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">


<span>

    Student Records

</span>


<span>

    Total:

    <strong>
        <?= mysqli_num_rows($result); ?>
    </strong>

</span>


</div>


<div class="card-body">


<form
    method="POST"
    action="delete_students.php"
    onsubmit="return confirmBulkDelete();">


<div class="mb-3">


<button
    type="submit"
    name="delete_selected"
    class="btn btn-danger">

    <i class="bi bi-trash"></i>

    Delete Selected

</button>


</div>


<div class="table-responsive">


<table class="table table-bordered table-striped table-hover align-middle">


<thead class="table-dark">


<tr>


<th style="width:40px;">

<input
    type="checkbox"
    id="selectAll"
    class="form-check-input">

</th>


<th>ID</th>

<th>Photo</th>

<th>Reg No</th>

<th>Full Name</th>

<th>Gender</th>

<th>Class</th>

<th>Stream</th>

<th>Date of Birth</th>

<th>Admission Date</th>

<th>Year of Entry</th>

<th>Status</th>

<th>Nationality</th>

<th>Religion</th>

<th>Parent Name</th>

<th>Parent Contact</th>

<th>Parent Email</th>

<th>Parent Address</th>

<th>Occupation</th>

<th>Blood Group</th>

<th>Allergies</th>

<th>Medical Condition</th>

<th>Previous School</th>

<th>Notes</th>

<th>Actions</th>


</tr>


</thead>


<tbody>


<?php if (mysqli_num_rows($result) > 0): ?>


<?php while ($row = mysqli_fetch_assoc($result)): ?>


<tr>


<!-- CHECKBOX -->

<td>

<input
    type="checkbox"
    name="student_ids[]"
    value="<?= (int)$row['student_id']; ?>"
    class="student-checkbox form-check-input">

</td>


<!-- ID -->

<td>

<?= e($row['student_id']); ?>

</td>


<!-- PHOTO -->

<td>

<?php if (!empty($row['photo'])): ?>

<img
    src="../uploads/students/<?= e($row['photo']); ?>"
    class="student-photo"
    alt="Student Photo">

<?php else: ?>

<span class="text-muted">

    No photo

</span>

<?php endif; ?>

</td>


<!-- REGISTRATION -->

<td>

<?= e($row['reg_no']); ?>

</td>


<!-- NAME -->

<td>

<strong>

<?= e($row['full_name']); ?>

</strong>

</td>


<!-- GENDER -->

<td>

<?= e($row['gender']); ?>

</td>


<!-- CLASS -->

<td>

<?= e($row['class']); ?>

</td>


<!-- STREAM -->

<td>

<?= e($row['stream']); ?>

</td>


<!-- DOB -->

<td>

<?= e($row['dob']); ?>

</td>


<!-- ADMISSION DATE -->

<td>

<?= e($row['admission_date']); ?>

</td>


<!-- YEAR OF ENTRY -->

<td>

<?php

if (!empty($row['admission_date'])) {

    echo e(
        date(
            'Y',
            strtotime($row['admission_date'])
        )
    );

} else {

    echo '-';

}

?>

</td>


<!-- STATUS -->

<td>

<?php if ($row['status'] === 'Active'): ?>

<span class="badge bg-success">

    Active

</span>

<?php else: ?>

<span class="badge bg-secondary">

    <?= e($row['status']); ?>

</span>

<?php endif; ?>

</td>


<!-- NATIONALITY -->

<td>

<?= e($row['nationality']); ?>

</td>


<!-- RELIGION -->

<td>

<?= e($row['religion']); ?>

</td>


<!-- PARENT NAME -->

<td>

<?= e($row['parent_name']); ?>

</td>


<!-- PARENT CONTACT -->

<td>

<?= e($row['parent_contact']); ?>

</td>


<!-- PARENT EMAIL -->

<td>

<?= e($row['parent_email']); ?>

</td>


<!-- PARENT ADDRESS -->

<td>

<?= e($row['parent_address']); ?>

</td>


<!-- OCCUPATION -->

<td>

<?= e($row['occupation']); ?>

</td>


<!-- BLOOD GROUP -->

<td>

<?= e($row['blood_group']); ?>

</td>


<!-- ALLERGIES -->

<td>

<?= e($row['allergies']); ?>

</td>


<!-- MEDICAL CONDITION -->

<td>

<?= e($row['medical_condition']); ?>

</td>


<!-- PREVIOUS SCHOOL -->

<td>

<?= e($row['previous_school']); ?>

</td>


<!-- NOTES -->

<td>

<?= e($row['notes']); ?>

</td>


<!-- ACTIONS -->

<td style="white-space: nowrap;">


<a
    href="edit_student.php?id=<?= (int)$row['student_id']; ?>"
    class="btn btn-warning btn-sm">

    <i class="bi bi-pencil"></i>

    Edit

</a>


<a
    href="delete_student.php?id=<?= (int)$row['student_id']; ?>"
    class="btn btn-danger btn-sm"
    onclick="return confirm('Are you sure you want to delete this student?');">

    <i class="bi bi-trash"></i>

    Delete

</a>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
    colspan="25"
    class="text-center text-muted py-4">

    No students found matching the selected filters.

</td>

</tr>


<?php endif; ?>


</tbody>


</table>


</div>


</form>


</div>

</div>


</div>


<script>


/*
|--------------------------------------------------------------------------
| SELECT ALL
|--------------------------------------------------------------------------
*/

document
    .getElementById('selectAll')
    .addEventListener('change', function () {

        const checkboxes =
            document.querySelectorAll('.student-checkbox');


        checkboxes.forEach(function (checkbox) {

            checkbox.checked = this.checked;

        }, this);

    });


/*
|--------------------------------------------------------------------------
| BULK DELETE CONFIRMATION
|--------------------------------------------------------------------------
*/

function confirmBulkDelete()
{

    const selected =
        document.querySelectorAll(
            '.student-checkbox:checked'
        );


    if (selected.length === 0) {

        alert('Please select at least one student.');

        return false;
    }


    return confirm(
        'Are you sure you want to delete ' +
        selected.length +
        ' selected student(s)? This action cannot be undone.'
    );
}

</script>


</body>

</html>
```
