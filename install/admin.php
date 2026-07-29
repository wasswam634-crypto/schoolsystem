<?php
session_start();

if(file_exists("lock.php")){
    die("The system has already been installed.");
}

if(!isset($_SESSION['school_name'])){
    header("Location: school.php");
    exit;
}

$error="";

if(isset($_POST['continue'])){

    $fullname=trim($_POST['fullname']);
    $username=trim($_POST['username']);
    $email=trim($_POST['email']);

    $password=$_POST['password'];
    $confirm=$_POST['confirm_password'];

    if($password!=$confirm){

        $error="Passwords do not match.";

    }else{

        $_SESSION['admin_fullname']=$fullname;
        $_SESSION['admin_username']=$username;
        $_SESSION['admin_email']=$email;
        $_SESSION['admin_password']=password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        header("Location: install.php");
        exit;

    }

}
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Administrator Account</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>

Step 4 : Administrator Account

</h3>

</div>

<div class="card-body">

<?php if($error): ?>

<div class="alert alert-danger">

<?= $error; ?>

</div>

<?php endif; ?>

<form method="POST">

<div class="mb-3">

<label>

Administrator Full Name

</label>

<input
type="text"
name="fullname"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Username

</label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Email

</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="row">

<div class="col-md-6">

<label>

Password

</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="col-md-6">

<label>

Confirm Password

</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

</div>

<br>

<div class="d-flex justify-content-between">

<a
href="school.php"
class="btn btn-secondary">

Back

</a>

<button
type="submit"
name="continue"
class="btn btn-primary">

Install System

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