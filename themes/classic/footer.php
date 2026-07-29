<?php

$social_links = mysqli_query(
    $conn,
    "SELECT * FROM website_social_links"
);

?>

<footer
style="
background-color: var(--primary-color);
color:white;
margin-top:50px;
border-top:5px solid var(--secondary-color);
">

<div class="container py-5">

<div class="row">

<div class="col-md-4">

<h4>
<?= e($settings['school_name']); ?>
</h4>

<p>
<?= e($settings['motto']); ?>
</p>

<?php if(!empty($settings['logo'])): ?>

<img
src="/schlwb/uploads/logos/<?= e($settings['logo']); ?>"
width="80">

<?php endif; ?>

</div>

<div class="col-md-4">

<h5>Contact Information</h5>

<p>
<strong>Phone:</strong>
<?= e($settings['phone'] ?? ''); ?>
</p>

<p>
<strong>Email:</strong>
<?= e($settings['email'] ?? ''); ?>
</p>

<p>
<strong>Address:</strong>
<?= e($settings['address'] ?? ''); ?>
</p>

</div>

<div class="col-md-4">

<h5>Follow Us</h5>

<?php while($social = mysqli_fetch_assoc($social_links)): ?>

<p>

<a
href="<?= e($social['url']); ?>"
target="_blank"
style="color:white;text-decoration:none;">

<?= e($social['platform']); ?>

</a>

</p>

<?php endwhile; ?>

</div>

</div>

<hr>

<div class="text-center">

© <?= date('Y'); ?>

<?= e($settings['school_name']); ?>

All Rights Reserved.

</div>

</div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>