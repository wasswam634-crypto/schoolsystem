<?php

session_start();

require_once "includes/functions.php";

if(installed()){
    die("School ERP has already been installed.");
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>School ERP Installer</title>

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

<h2>

School ERP Installer

</h2>

</div>

<div class="card-body">

<h4>

Welcome

</h4>

<p>

This wizard will install the School ERP System.

</p>

<hr>

<h5>

Installation Steps

</h5>

<ul>

<li>Server Requirements</li>

<li>Database Connection</li>

<li>Database Verification</li>

<li>Import Database</li>

<li>School Information</li>

<li>Administrator Account</li>

<li>Finish</li>

</ul>

<div class="progress mt-4">

<div
class="progress-bar"
style="width:<?= progress(1); ?>%">

<?= progress(1); ?>%

</div>

</div>

<br>

<a
href="requirements.php"
class="btn btn-primary">

Start Installation

</a>

</div>

</div>

</div>

</div>

</div>

</body>

</html>