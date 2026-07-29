<?php

if(!function_exists('e')){
    require_once __DIR__ . '/../../includes/functions.php';
}

if (!isset($conn)) {
    include __DIR__ . '/../../database/connection.php';
}


$settings = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM school_settings LIMIT 1"
    )
);

?>


<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="/schlwb/themes/classic/style.css">


<title>
<?= e($settings['school_name'] ?? 'School Website'); ?>
</title>



<style>

:root{

--primary-color: <?= e($settings['primary_color'] ?? '#0B3D91'); ?>;

--secondary-color: <?= e($settings['secondary_color'] ?? '#F39C12'); ?>;

--background-color: <?= e($settings['background_color'] ?? '#FFFFFF'); ?>;

--text-color: <?= e($settings['text_color'] ?? '#333333'); ?>;

}

body{

font-family:var(--font-family);

}



.btn-primary{

background-color:var(--primary-color)!important;

border-color:var(--primary-color)!important;

}



.navbar{

background-color:var(--primary-color)!important;

}



h1,h2,h3{

color:var(--primary-color);

}



</style>



<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>