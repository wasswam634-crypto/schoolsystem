<?php
include 'header.php';
include 'navbar.php';


$settings = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM school_settings LIMIT 1"
    )
);
?>

<div class="container py-5">

<h1 class="text-center mb-5">
Contact Us
</h1>

<div class="card shadow">

<div class="card-body">

<p>
<strong>Phone:</strong>
<?= e($settings['phone']); ?>
</p>

<p>
<strong>Email:</strong>
<?= e($settings['email']); ?>
</p>

<p>
<strong>Address:</strong>
<?= e($settings['address']); ?>
</p>

<?php if(!empty($settings['map_link'])){ ?>

<a
href="<?= e($settings['map_link']); ?>"
target="_blank"
class="btn btn-primary">

Open Location on Google Maps

</a>

<?php } ?>

</div>

</div>

</div>

<?php include 'footer.php'; ?>