<?php
if (!isset($conn)) {
    include __DIR__ . '/../../database/connection.php';
}

if (!function_exists('e')) {
    include __DIR__ . '/../../includes/functions.php';
}

$result = mysqli_query($conn, "SELECT * FROM school_settings LIMIT 1");
$settings = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= e($settings['school_name'] ?? 'School'); ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="/schlwb/themes/university/style.css">
<style>



:root{

--primary-color: <?= e($settings['primary_color'] ?? '#0B3D91'); ?>;

--secondary-color: <?= e($settings['secondary_color'] ?? '#F39C12'); ?>;

--background-color: <?= e($settings['background_color'] ?? '#FFFFFF'); ?>;

--text-color: <?= e($settings['text_color'] ?? '#333333'); ?>;

}


.btn-primary{
    background:var(--secondary-color);
}


body{
    font-family:Arial,sans-serif;
    background:#f8f9fa;
}






.hero{
    background:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),
    url('/schlwb/assets/images/university.jpg');
    background-size:cover;
    background-position:center;
    color:white;
    padding:130px 0;
}

.section-title{
    font-weight:bold;
    color:#0d47a1;
    margin-bottom:25px;
}

footer{
     background:var(--primary-color);
    color:white;
    padding:40px 0;
    margin-top:50px;
}

.card{
    border:none;
    box-shadow:0 3px 10px rgba(0,0,0,.1);
}

</style>

</head>

<body>


</a>

<button
class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#navbar">

<span class="navbar-toggler-icon"></span>

</button>

<div
class="collapse navbar-collapse"
id="navbar">

<ul class="navbar-nav ms-auto">

<li class="nav-item">
<a class="nav-link" href="/schlwb/">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/about.php">About</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/academics.php">Academics</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/admissions.php">Admissions</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/staff.php">Staff</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/gallery.php">Gallery</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/vacancies.php">Vacancies</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/contact.php">Contact</a>
</li>

</ul>

</div>

</div>

</nav>