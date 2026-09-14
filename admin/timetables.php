
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
| LOAD ALL ACADEMIC PERIODS
|--------------------------------------------------------------------------
*/

$periods = [];

$period_query = mysqli_query(
    $conn,

    "SELECT
        period_id,
        academic_year,
        period_name,
        status
     FROM academic_periods
     ORDER BY period_id DESC"
);

while ($row = mysqli_fetch_assoc($period_query)) {

    $periods[] = $row;
}


/*
|--------------------------------------------------------------------------
| LOAD TIMETABLES
|--------------------------------------------------------------------------
|
| One timetable per Class + Academic Period
|
*/

$timetable_query = mysqli_query(
    $conn,

    "SELECT
        t.period_id,
        t.class,
        COUNT(*) AS lessons,
        ap.academic_year,
        ap.period_name,
        ap.status

     FROM timetables t

     INNER JOIN academic_periods ap
        ON t.period_id = ap.period_id

     GROUP BY
        t.period_id,
        t.class

     ORDER BY
        ap.period_id DESC,
        t.class ASC"
);

?>

<!DOCTYPE html>

<html>

<head>

<title>Timetable Management</title>

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

.table {
    background: white;
}

.badge {
    font-size: 14px;
}

</style>

</head>


<body>


<div class="container-fluid p-4">


<!-- PAGE HEADER -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>
        📅 Timetable Management
    </h2>

    <a
        href="timetable.php"
        class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>

        Create Timetable

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


<!-- CREATE BUTTON -->

<div class="mb-3">

    <a
        href="timetable.php"
        class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>

        Create Timetable

    </a>

</div>


<!-- TIMETABLE CARD -->

<div class="card">


<div class="card-header bg-dark text-white">

    Created Timetables

</div>


<div class="card-body">


<div class="table-responsive">


<table class="table table-bordered table-striped align-middle">


<thead class="table-dark">

<tr>

    <th>Class</th>

    <th>Academic Year</th>

    <th>Term / Period</th>

    <th>Total Lessons</th>

    <th style="width: 300px;">
        Actions
    </th>

</tr>

</thead>


<tbody>


<?php if (mysqli_num_rows($timetable_query) > 0): ?>


<?php while ($row = mysqli_fetch_assoc($timetable_query)): ?>


<tr>


<td>

    <?= e($row['class']); ?>

</td>


<td>

    <?= e($row['academic_year']); ?>

</td>


<td>

    <?= e($row['period_name']); ?>

</td>


<td>

    <?= e($row['lessons']); ?>

</td>


<td>


<!-- VIEW -->

<a
    href="view_timetable.php?period_id=<?= (int)$row['period_id']; ?>&class=<?= urlencode($row['class']); ?>"
    class="btn btn-success btn-sm">

    <i class="bi bi-eye"></i>

    View

</a>


<!-- EDIT -->

<a
    href="edit_timetable.php?period_id=<?= (int)$row['period_id']; ?>&class=<?= urlencode($row['class']); ?>"
    class="btn btn-warning btn-sm">

    <i class="bi bi-pencil"></i>

    Edit

</a>


<!-- DELETE -->

<form
    method="POST"
    action="delete_timetable.php"
    class="d-inline"
    onsubmit="return confirm('Are you sure you want to delete this entire timetable?');">


    <input
        type="hidden"
        name="period_id"
        value="<?= (int)$row['period_id']; ?>">


    <input
        type="hidden"
        name="class"
        value="<?= e($row['class']); ?>">


    <button
        type="submit"
        name="delete_timetable"
        class="btn btn-danger btn-sm">

        <i class="bi bi-trash"></i>

        Delete

    </button>


</form>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

    <td
        colspan="5"
        class="text-center text-muted">

        No timetables have been created yet.

    </td>

</tr>


<?php endif; ?>


</tbody>


</table>


</div>


</div>

</div>


</div>


<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
```
