<?php
session_start();

require_once "includes/functions.php";

if(installed()){
    die("School ERP has already been installed.");
}

$error="";

if(isset($_POST['test'])){

    $host=trim($_POST['host']);
    $database=trim($_POST['database']);
    $username=trim($_POST['username']);
    $password=$_POST['password'];

    $conn=@mysqli_connect(
        $host,
        $username,
        $password,
        $database
    );

    if($conn){

        $_SESSION['db_host']=$host;
        $_SESSION['db_name']=$database;
        $_SESSION['db_user']=$username;
        $_SESSION['db_pass']=$password;

        redirect("database_check.php");

    }else{

        $error=mysqli_connect_error();

    }

}
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Database Configuration</title>

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

Step 2 : Database Configuration

</h3>

</div>

<div class="card-body">

<div class="progress mb-4">

<div
class="progress-bar"
style="width:<?= progress(2); ?>%">

<?= progress(2); ?>%

</div>

</div>

<?php if($error): ?>

<div class="alert alert-danger">

<?= $error; ?>

</div>

<?php endif; ?>

<form method="POST">

<div class="mb-3">

<label>

Database Host

</label>

<input
type="text"
name="host"
class="form-control"
value="localhost"
required>

</div>

<div class="mb-3">

<label>

Database Name

</label>

<input
type="text"
name="database"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Database Username

</label>

<input
type="text"
name="username"
class="form-control"
value="root"
required>

</div>

<div class="mb-3">

<label>

Database Password

</label>

<input
type="password"
name="password"
class="form-control">

</div>

<div class="d-flex justify-content-between">

<a
href="requirements.php"
class="btn btn-secondary">

Back

</a>

<button
type="submit"
name="test"
class="btn btn-primary">

Test Connection

</button>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>