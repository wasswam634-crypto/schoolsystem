<?php

include 'header.php';
include 'navbar.php';



$hero = get_section($conn,'home','hero');


// Statistics

$stats = mysqli_query(
    $conn,
    "SELECT *
     FROM website_statistics"
);


// News

$announcements = mysqli_query(
    $conn,
    "SELECT *
     FROM announcements
     WHERE status='active'
     ORDER BY created_at DESC
     LIMIT 3"
);


// Gallery

$gallery = mysqli_query(
    $conn,
    "SELECT *
     FROM gallery
     ORDER BY uploaded_at DESC
     LIMIT 6"
);


// Testimonials

$testimonials = mysqli_query(
    $conn,
    "SELECT *
     FROM website_testimonials
     LIMIT 3"
);

?>



<!-- HERO SECTION -->

<?php
$heroImage = !empty($hero['image'])
    ? "/schlwb/uploads/website/" . e($hero['image'])
    : "/schlwb/assets/images/university.jpg";
?>

<!-- HERO SECTION -->

<section
class="hero text-center"
style="
background:
linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),
url('<?= $heroImage; ?>');
background-size:cover;
background-position:center;
background-repeat:no-repeat;
padding:140px 0;
color:white;
">

<div class="container">

<?php if(!empty($settings['logo'])): ?>

<img
src="/schlwb/uploads/logos/<?= e($settings['logo']); ?>"
width="130"
class="mb-4"
alt="School-Logo">

<?php endif; ?>

<h1 class="display-3 fw-bold">
<?= e($hero['title'] ?? $settings['school_name']); ?>
</h1>

<p class="lead">
<?= e($hero['content'] ?? $settings['motto']); ?>
</p>

<a
href="/schlwb/admissions.php"
class="btn btn-warning btn-lg mt-3">

Apply Now

</a>

<a
href="/schlwb/portal/login.php"
class="btn btn-light btn-lg mt-3 ms-2">

Student Portal

</a>

</div>

</section>




<!-- STATISTICS SECTION -->


<section class="bg-light py-5">


<div class="container">


<div class="row text-center">


<?php while($row=mysqli_fetch_assoc($stats)): ?>


<div class="col-md-3 mb-4">


<div class="card shadow-sm p-4">


<h2 class="fw-bold text-primary">


<?= e($row['stat_value']); ?>


</h2>



<h5>


<?= e($row['stat_title']); ?>


</h5>


</div>


</div>


<?php endwhile; ?>


</div>


</div>


</section>





<!-- NEWS SECTION -->


<section class="py-5">


<div class="container">


<h2 class="section-title text-center mb-5">

Latest News

</h2>



<div class="row">


<?php while($row=mysqli_fetch_assoc($announcements)): ?>


<div class="col-md-4 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h4>

<?= e($row['title']); ?>

</h4>


<p>

<?= nl2br(e(substr($row['message'],0,150))); ?>

...

</p>


</div>


</div>


</div>


<?php endwhile; ?>


</div>


</div>


</section>


</div>


</section>





<!-- TESTIMONIAL SECTION -->


<section class="py-5">


<div class="container">


<h2 class="section-title text-center mb-5">

Testimonials

</h2>



<div class="row">



<?php while($row=mysqli_fetch_assoc($testimonials)): ?>


<div class="col-md-4 mb-4">


<div class="card shadow text-center h-100 p-4">



<?php if(!empty($row['photo'])): ?>


<img

src="/schlwb/uploads/testimonials/<?= e($row['photo']); ?>"

class="rounded-circle mx-auto mb-3"

width="100"

height="100"

style="object-fit:cover;">



<?php endif; ?>



<h5>

<?= e($row['name']); ?>

</h5>



<small class="text-muted">

<?= e($row['role']); ?>

</small>



<p class="mt-3">

<?= nl2br(e($row['message'])); ?>

</p>



</div>


</div>


<?php endwhile; ?>


</div>


</div>


</section>





<!-- CALL TO ACTION -->


<section

class="py-5 text-center text-white"

style="background:#0d47a1;">



<div class="container">


<h2>

Ready to Join

<?= e($settings['school_name']); ?>

?

</h2>



<p class="lead">

Admissions are open. Start your journey with us today.

</p>



<a

href="/schlwb/admissions.php"

class="btn btn-warning btn-lg">

Apply Today

</a>


</div>


</section>





<?php include 'footer.php'; ?>