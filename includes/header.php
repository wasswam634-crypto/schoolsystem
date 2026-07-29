<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php
        if(isset($page_title)){
          $school_query = mysqli_query(
    $conn,
    "SELECT school_name FROM school_settings LIMIT 1"
);

$school_info = mysqli_fetch_assoc($school_query);

$school_name = $school_info['school_name'] ?? 'School ERP';

echo $page_title . " | " . $school_name;
        }else{
            echo "School ERP Management System";
        }
        ?>
    </title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <style>
@media print {

    .navbar,
    .nav,
    .btn,
    .sidebar,
    .no-print {
        display: none !important;
    }

    body {
        margin: 0;
    }
}
</style>

</head>

<body>

<div class="wrapper">