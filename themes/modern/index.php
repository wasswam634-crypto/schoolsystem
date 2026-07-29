<?php

include 'header.php';
include 'navbar.php';


$hero = get_section($conn,'home','hero');

$principal = get_section($conn,'home','principal_message');

$why_choose = get_section($conn,'home','why_choose_us');

$academics = get_section($conn,'home','academics');

$cta = get_section($conn,'home','call_to_action');



$statistics = mysqli_query(
    $conn,
    "SELECT * FROM website_statistics"
);



$testimonials = mysqli_query(
    $conn,
    "SELECT * FROM website_testimonials LIMIT 3"
);



$news = mysqli_query(
    $conn,
    "SELECT *
     FROM announcements
     WHERE status='active'
     ORDER BY created_at DESC
     LIMIT 3"
);



$gallery = mysqli_query(
    $conn,
    "SELECT *
     FROM gallery
     ORDER BY gallery_id DESC
     LIMIT 6"
);

?>



<!-- HERO -->

<section
style="
background:
linear-gradient(
rgba(0,0,0,.5),
rgba(0,0,0,.5)
),
url('uploads/website/<?= e($hero['image'] ?? ''); ?>');

background-size:cover;
background-position:center;

min-height:600px;

display:flex;
align-items:center;

color:white;
text-align:center;
">


<div class="container">


<h1 class="display-3 fw-bold">

<?= e($hero['title'] ?? 'Welcome To Our School'); ?>

</h1>


<p class="lead">

<?= e($hero['content'] ?? 'A Road To Excellence'); ?>

</p>



<a href="../../admissions.php"
class="btn btn-primary btn-lg">

Apply Now

</a>



<a href="../../login.php"
class="btn btn-light btn-lg">

Student Portal

</a>



</div>

</section>





<!-- SCHOOL IDENTITY -->


<section class="container py-5 text-center">


<?php if(!empty($settings['logo'])): ?>


<img
src="../../uploads/logos/<?= e($settings['logo']); ?>"
width="120"
class="mb-3 rounded-circle">


<?php endif; ?>


<h2>

<?= e($settings['school_name']); ?>

</h2>


<h5 class="text-muted">

<?= e($settings['motto']); ?>

</h5>


</section>






<!-- STATISTICS -->


<section class="container py-5">


<div class="row">


<?php while($stat=mysqli_fetch_assoc($statistics)){ ?>


<div class="col-md-3 mb-4">


<div class="card shadow text-center p-4">


<h1 style="color:var(--primary-color)">

<?= e($stat['stat_value']); ?>

</h1>


<h5>

<?= e($stat['stat_title']); ?>

</h5>


</div>


</div>


<?php } ?>


</div>


</section>







<!-- WHY CHOOSE US -->


<section class="py-5"
style="
background:var(--secondary-color);
">


<div class="container text-center text-white">


<h2>

<?= e($why_choose['title'] ?? 'Why Choose Us'); ?>

</h2>


<p>

<?= nl2br(e($why_choose['content'] ?? '')); ?>

</p>


</div>


</section>







<!-- PRINCIPAL MESSAGE -->


<section class="container py-5">


<div class="row align-items-center">


<div class="col-md-4">


<img
src="../../uploads/website/principal.jpg"
class="img-fluid rounded shadow">


</div>


<div class="col-md-8">


<h2>

<?= e($principal['title'] ?? 'Message From The Head Teacher'); ?>

</h2>


<p>

<?= nl2br(e($principal['content'] ?? '')); ?>

</p>


</div>


</div>


</section>







<!-- ACADEMICS -->


<section class="py-5">


<div class="container text-center">


<h2 style="color:var(--primary-color)">

<?= e($academics['title'] ?? 'Academic Programs'); ?>

</h2>


<p>

<?= nl2br(e($academics['content'] ?? '')); ?>

</p>


</div>


</section>







<!-- NEWS -->


<section class="container py-5">


<h2 class="text-center mb-4">

Latest News

</h2>


<div class="row">


<?php while($item=mysqli_fetch_assoc($news)){ ?>


<div class="col-md-4 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h5>

<?= e($item['title']); ?>

</h5>


<p>

<?= e(substr($item['message'],0,150)); ?>

...</p>


</div>


</div>


</div>


<?php } ?>


</div>


</section>







<!-- GALLERY -->


<



</div>


<?php  ?>


</div>


</div>


</section>







<!-- TESTIMONIALS -->


<section class="container py-5">


<h2 class="text-center mb-4">

Testimonials

</h2>



<div class="row">


<?php while($test=mysqli_fetch_assoc($testimonials)){ ?>


<div class="col-md-4 mb-4">


<div class="card shadow">


<div class="card-body">


<p>

"<?= e($test['message']); ?>"

</p>


<hr>


<strong>

<?= e($test['name']); ?>

</strong>


<br>


<small>

<?= e($test['role']); ?>

</small>


</div>


</div>


</div>


<?php } ?>


</div>


</section>







<!-- CALL TO ACTION -->


<section
class="py-5 text-center text-white"

style="
background:var(--primary-color);
">


<div class="container">


<h2>

<?= e($cta['title'] ?? 'Admissions Open'); ?>

</h2>


<p>

<?= e($cta['content'] ?? 'Join our school today'); ?>

</p>



<a href="../../admissions.php"
class="btn btn-light btn-lg">

Apply Now

</a>


</div>


</section>





<?php include 'footer.php'; ?>