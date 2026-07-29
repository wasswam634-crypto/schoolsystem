<?php

include 'header.php';
include 'navbar.php';


$intro = get_section($conn,'academics','intro');
$departments = get_section($conn,'academics','departments');
$programs = get_section($conn,'academics','programs');
$learning = get_section($conn,'academics','learning');
$research = get_section($conn,'academics','research');

?>


<div class="container py-5">


<h1 class="text-center mb-5">

<?= e($intro['title'] ?? 'Academics'); ?>

</h1>


<p class="lead text-center">

<?= nl2br(e($intro['content'] ?? '')); ?>

</p>



<div class="row mt-5">


<div class="col-md-6">

<div class="card shadow mb-4">

<div class="card-body">

<h3>

<?= e($departments['title'] ?? 'Departments'); ?>

</h3>


<p>

<?= nl2br(e($departments['content'] ?? '')); ?>

</p>


</div>

</div>

</div>




<div class="col-md-6">

<div class="card shadow mb-4">


<div class="card-body">


<h3>

<?= e($programs['title'] ?? 'Programs'); ?>

</h3>


<p>

<?= nl2br(e($programs['content'] ?? '')); ?>

</p>


</div>


</div>

</div>


</div>




<div class="card shadow mb-4">


<div class="card-body">


<h3>

<?= e($learning['title'] ?? 'Learning Approach'); ?>

</h3>


<p>

<?= nl2br(e($learning['content'] ?? '')); ?>

</p>


</div>


</div>




<div class="card shadow">


<div class="card-body">


<h3>

<?= e($research['title'] ?? 'Research'); ?>

</h3>


<p>

<?= nl2br(e($research['content'] ?? '')); ?>

</p>


</div>


</div>



</div>


<?php include 'footer.php'; ?>