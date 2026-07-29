<?php

$social_links = mysqli_query(
    $conn,
    "SELECT * FROM website_social_links"
);

?>

<footer
style="
background:#003366;
color:white;
margin-top:60px;
">

<div class="container py-5">

<div class="row">

<!-- University Information -->
<div class="col-md-4 mb-4">

<h4>
<?= e($settings['school_name']); ?>
</h4>

<p>
<?= e($settings['motto']); ?>
</p>

<?php if(!empty($settings['logo'])): ?>

<img
src="/schlwb/uploads/logos/<?= e($settings['logo']); ?>"
width="100">

<?php endif; ?>

</div>

<!-- Quick Links -->
<div class="col-md-4 mb-4">

<h5>Quick Links</h5>

<ul class="list-unstyled">

<li>
<a
href="/schlwb/about.php"
class="text-white text-decoration-none">
About Us
</a>
</li>

<li>
<a
href="/schlwb/academics.php"
class="text-white text-decoration-none">
Academic Programmes
</a>
</li>

<li>
<a
href="/schlwb/admissions.php"
class="text-white text-decoration-none">
Admissions
</a>
</li>

<li>
<a
href="/schlwb/contact.php"
class="text-white text-decoration-none">
Contact Us
</a>
</li>

<li>
<a
href="/schlwb/portal/login.php"
class="text-white text-decoration-none">
Student Portal
</a>
</li>

</ul>

</div>

<!-- Contact Information -->
<div class="col-md-4 mb-4">

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

<h5 class="mt-4">
Follow Us
</h5>

<?php while($social = mysqli_fetch_assoc($social_links)): ?>

<p>

<a
href="<?= e($social['url']); ?>"
target="_blank"
class="text-white text-decoration-none">

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