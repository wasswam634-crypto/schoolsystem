<?php
include 'header.php';
include 'navbar.php';


$intro = get_section($conn,'admissions','intro');
$requirements = get_section($conn,'admissions','requirements');
$documents = get_section($conn,'admissions','documents');
$procedure = get_section($conn,'admissions','procedure');
$fees = get_section($conn,'admissions','fees');
$contact = get_section($conn,'admissions','contact');
?>

<div class="container py-5">

<h1 class="text-center mb-5">
    <?= e($intro['title']); ?>
</h1>

<p class="lead text-center mb-5">
    <?= nl2br(e($intro['content'])); ?>
</p>

<div class="card shadow mb-4">
<div class="card-body">
<h3><?= e($requirements['title']); ?></h3>
<p><?= nl2br(e($requirements['content'])); ?></p>
</div>
</div>

<div class="card shadow mb-4">
<div class="card-body">
<h3><?= e($documents['title']); ?></h3>
<p><?= nl2br(e($documents['content'])); ?></p>
</div>
</div>

<div class="card shadow mb-4">
<div class="card-body">
<h3><?= e($procedure['title']); ?></h3>
<p><?= nl2br(e($procedure['content'])); ?></p>
</div>
</div>

<div class="card shadow mb-4">
<div class="card-body">
<h3><?= e($fees['title']); ?></h3>
<p><?= nl2br(e($fees['content'])); ?></p>
</div>
</div>

<div class="card shadow mb-4">
<div class="card-body">
<h3><?= e($contact['title']); ?></h3>
<p><?= nl2br(e($contact['content'])); ?></p>
</div>
</div>

</div>

<?php include 'footer.php'; ?>