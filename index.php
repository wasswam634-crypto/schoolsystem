<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database/connection.php';
include 'includes/theme_loader.php';
include 'includes/functions.php';


// Get requested page
$page = $_GET['page'] ?? 'index';


// Allowed pages for security
$allowed_pages = [
    'index',
    'about',
    'academics',
    'admissions',
    'staff',
    'gallery',
    'contact',
    'vacancies'
];


// Prevent loading unwanted files
if (!in_array($page, $allowed_pages)) {
    $page = 'index';
}


// Load selected theme page
$file = "themes/$theme/$page.php";


if (file_exists($file)) {
    include $file;
} else {
    include "themes/$theme/index.php";
}

?>