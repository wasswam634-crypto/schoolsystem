<?php
include 'header.php';
include 'navbar.php';

$staff = mysqli_query(
    $conn,
    "SELECT *
     FROM website_staff"
);
?>

<div class="container py-5">

<h1 class="text-center mb-5">
Our Staff
</h1>
 
<div class="row">
<?php while($member = mysqli_fetch_assoc($staff)){ ?>

<div class="col-md-4 mb-4">

<div class="card shadow">

<?php if(!empty($member['photo'])){ ?>

<img 
src="/schlwb/uploads/staff/<?= e($member['photo']); ?>"
class="img-fluid rounded"
>

<?php } ?>

<div class="card-body">

<h4>
<?= e($member['name']); ?>
</h4>

<p class="text-muted">
<?= e($member['position']); ?>
</p>

<p>
<?= e($member['bio']); ?>
</p>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

<?php include 'footer.php'; ?>