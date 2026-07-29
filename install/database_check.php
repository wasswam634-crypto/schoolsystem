<?php
session_start();

require_once "includes/functions.php";

if(installed()){
    die("School ERP has already been installed.");
}

if(
    !isset($_SESSION['db_host']) ||
    !isset($_SESSION['db_name'])
){
    redirect("database.php");
}

$conn=mysqli_connect(
    $_SESSION['db_host'],
    $_SESSION['db_user'],
    $_SESSION['db_pass'],
    $_SESSION['db_name']
);

if(!$conn){
    die("Unable to connect to database.");
}

$result=mysqli_query($conn,"SHOW TABLES");

$table_count=mysqli_num_rows($result);

$empty=($table_count==0);

if(isset($_POST['continue']) && $empty){

    redirect("import.php");

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Database Verification</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="assets/installer.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow-lg">

<div class="card-header bg-primary text-white">

<h3>

Step 3 : Verify Database

</h3>

</div>

<div class="card-body">

<div class="progress mb-4">

<div
class="progress-bar"
style="width:48%;">

48%

</div>

</div>

<?php if($empty): ?>

<div class="alert alert-success">

<h5>

✔ Database Ready

</h5>

<p>

The selected database is empty.

School ERP can now be installed safely.

</p>

</div>

<?php else: ?>

<div class="alert alert-danger">

<h5>

⚠ Database Already Contains Tables

</h5>

<p>

The selected database already contains

<strong>

<?= $table_count; ?>

</strong>

table(s).

Installing School ERP here may overwrite existing data.

</p>

<p>

Please create a new empty database before continuing.

</p>

</div>

<?php endif; ?>

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>

<th>

Detected Tables

</th>

</tr>

</thead>

<tbody>

<?php

mysqli_data_seek($result,0);

if($table_count>0){

while($row=mysqli_fetch_array($result)){

echo "<tr>";

echo "<td>".$row[0]."</td>";

echo "</tr>";

}

}else{

echo "<tr>";

echo "<td>No tables found.</td>";

echo "</tr>";

}

?>

</tbody>

</table>

</div>

<div class="d-flex justify-content-between">

<a
href="database.php"
class="btn btn-secondary">

Back

</a>

<?php if($empty): ?>

<form method="POST">

<button
type="submit"
name="continue"
class="btn btn-primary">

Import Database

</button>

</form>

<?php else: ?>

<button
class="btn btn-danger"
disabled>

Database Not Empty

</button>

<?php endif; ?>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>