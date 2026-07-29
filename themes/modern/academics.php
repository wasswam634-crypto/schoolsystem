<?php

include 'header.php';
include 'navbar.php';



$intro = get_section(
    $conn,
    'academics',
    'intro'
);



$departments = get_section(
    $conn,
    'academics',
    'departments'
);



$programs = get_section(
    $conn,
    'academics',
    'programs'
);



$learning = get_section(
    $conn,
    'academics',
    'learning'
);



$research = get_section(
    $conn,
    'academics',
    'research'
);



?>



<div class="container py-5">



<!-- PAGE INTRO -->

<section class="text-center mb-5">


<h1 class="display-4 fw-bold">

<?= e($intro['title'] ?? 'Academic Programs'); ?>

</h1>


<p class="lead">

<?= nl2br(e($intro['content'] ?? '')); ?>

</p>


</section>





<!-- ACADEMIC SECTIONS -->


<div class="row">





<!-- DEPARTMENTS -->

<div class="col-md-6 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h3 class="text-primary">

<?= e($departments['title'] ?? 'Departments'); ?>

</h3>



<p>

<?= nl2br(e($departments['content'] ?? '')); ?>

</p>


</div>


</div>


</div>







<!-- PROGRAMS -->


<div class="col-md-6 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h3 class="text-primary">

<?= e($programs['title'] ?? 'Academic Programs'); ?>

</h3>



<p>

<?= nl2br(e($programs['content'] ?? '')); ?>

</p>


</div>


</div>


</div>







<!-- LEARNING -->


<div class="col-md-6 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h3 class="text-primary">

<?= e($learning['title'] ?? 'Learning Approach'); ?>

</h3>



<p>

<?= nl2br(e($learning['content'] ?? '')); ?>

</p>


</div>


</div>


</div>







<!-- RESEARCH -->


<div class="col-md-6 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h3 class="text-primary">

<?= e($research['title'] ?? 'Research'); ?>

</h3>



<p>

<?= nl2br(e($research['content'] ?? '')); ?>

</p>


</div>


</div>


</div>





</div>


</div>




<?php include 'footer.php'; ?>