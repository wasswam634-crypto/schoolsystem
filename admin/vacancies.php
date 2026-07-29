<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

if(isset($_POST['save'])){

    $title = trim($_POST['title']);
    $department = trim($_POST['department']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO vacancies
        (title, department, description, deadline)
        VALUES (?,?,?,?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $title,
        $department,
        $description,
        $deadline
    );

    mysqli_stmt_execute($stmt);

    $success = "Vacancy added successfully.";
}

$page_title = "Vacancies";

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">
<div class="row">

<?php include '../includes/admin_sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2 class="mb-4">Vacancy Management</h2>

<?php if(isset($success)): ?>
<div class="alert alert-success">
    <?= e($success); ?>
</div>
<?php endif; ?>

<div class="card shadow mb-4">

<div class="card-header bg-primary text-white">
Create Vacancy
</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Job Title</label>
<input type="text"
       name="title"
       class="form-control"
       required>
</div>

<div class="mb-3">
<label>Department</label>
<input type="text"
       name="department"
       class="form-control"
       required>
</div>

<div class="mb-3">
<label>Description</label>
<textarea name="description"
          rows="5"
          class="form-control"
          required></textarea>
</div>

<div class="mb-3">
<label>Application Deadline</label>
<input type="date"
       name="deadline"
       class="form-control"
       required>
</div>

<button name="save"
        class="btn btn-primary">
    Publish Vacancy
</button>

</form>

</div>
</div>

<div class="card shadow">

<div class="card-header bg-success text-white">
Current Vacancies
</div>

<div class="card-body">

<table class="table table-striped">

<tr>
<th>Title</th>
<th>Department</th>
<th>Deadline</th>
<th>Status</th>
</tr>

<?php

$result = mysqli_query(
    $conn,
    "SELECT *
     FROM vacancies
     ORDER BY created_at DESC"
);

while($row = mysqli_fetch_assoc($result)){
?>

<tr>

<td><?= e($row['title']); ?></td>
<td><?= e($row['department']); ?></td>
<td><?= e($row['deadline']); ?></td>
<td><?= e($row['status']); ?></td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>