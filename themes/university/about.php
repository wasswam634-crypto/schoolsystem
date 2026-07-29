<?php
include 'header.php';
include 'navbar.php';

$intro = get_section($conn,'about','intro');
$mission = get_section($conn,'about','mission');
$vision = get_section($conn,'about','vision');
$values = get_section($conn,'about','values');
$history = get_section($conn,'about','history');
?>

<div class="container py-5">

<h1 class="text-center mb-5">

<?= e($intro['title']); ?>

</h1>

<p class="lead text-center mb-5">

<?= nl2br(e($intro['content'])); ?>

</p>

<div class="row">

<div class="col-md-6">

<div class="card mb-4 shadow">

<div class="card-body">

<h3>
<?= e($mission['title']); ?>
</h3>

<p>
<?= nl2br(e($mission['content'])); ?>
</p>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card mb-4 shadow">

<div class="card-body">

<h3>
<?= e($vision['title']); ?>
</h3>

<p>
<?= nl2br(e($vision['content'])); ?>
</p>

</div>

</div>

</div>

</div>

<div class="card mb-4 shadow">

<div class="card-body">

<h3>
<?= e($values['title']); ?>
</h3>

<p>
<?= nl2br(e($values['content'])); ?>
</p>

</div>

</div>

<div class="card shadow">

<div class="card-body">

<h3>
<?= e($history['title']); ?>
</h3>

<p>
<?= nl2br(e($history['content'])); ?>
</p>

</div>

</div>

</div>

<?php include 'footer.php'; ?>