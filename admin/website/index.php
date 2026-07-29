<?php
include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');
?>

<!DOCTYPE html>
<html>
<head>

<title>Website Management</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2 class="mb-4">Website Management</h2>

<div class="row">

<div class="col-md-4 mb-3">
<a href="homepage.php" class="btn btn-primary w-100 p-4">
Homepage Management
</a>
</div>

<div class="col-md-4 mb-3">
<a href="about.php" class="btn btn-primary w-100 p-4">
About Page
</a>
</div>

<div class="col-md-4 mb-3">
<a href="academics.php" class="btn btn-primary w-100 p-4">
Academics Page
</a>
</div>

<div class="col-md-4 mb-3">
<a href="admissions.php" class="btn btn-primary w-100 p-4">
Admissions Page
</a>
</div>

<div class="col-md-4 mb-3">
<a href="staff.php" class="btn btn-primary w-100 p-4">
Staff Management
</a>
</div>

<div class="col-md-4 mb-3">
<a href="gallery.php" class="btn btn-primary w-100 p-4">
Gallery Management
</a>
</div>

<div class="col-md-4 mb-3">
<a href="vacancies.php" class="btn btn-primary w-100 p-4">
Vacancies Management
</a>
</div>

<div class="col-md-4 mb-3">
<a href="social_links.php" class="btn btn-primary w-100 p-4">
Social Links
</a>
</div>

<div class="col-md-4 mb-3">
<a href="testimonials.php" class="btn btn-primary w-100 p-4">
Testimonials
</a>
</div>

<div class="col-md-4 mb-3">
<a href="statistics.php" class="btn btn-primary w-100 p-4">
Statistics
</a>
</div>

<div class="col-md-4 mb-3">
<a href="themes.php" class="btn btn-primary w-100 p-4">
Theme Management
</a>
</div>

<div  class="col-md-4 mb-3">
<a href="faculties.php" class="btn btn-primary w-100 p-4">
Faculties
</a>
</div>

<div  class="col-md-4 mb-3">
    <a href="news.php" class="btn btn-primary w-100 p-4">
Website News
</a>
</div>

<div class="col-md-4 mb-3">
<a href="appearance.php" class="btn btn-primary w-100 p-4">
Appearance Settings
</a>
</div>

</div>

</div>

</body>
</html>