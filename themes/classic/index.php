<?php

include 'header.php';
include 'navbar.php';



$hero = get_section($conn,'home','hero');

$principal = get_section($conn,'home','principal_message');

$why_choose = get_section($conn,'home','why_choose');

$academics = get_section($conn,'home','academics');

$cta = get_section($conn,'home','cta');





// STATISTICS

$statistics = mysqli_query(
    $conn,
    "SELECT *
     FROM website_statistics"
);





// NEWS / ANNOUNCEMENTS

$news = mysqli_query(
    $conn,
    "SELECT *
     FROM announcements
     WHERE status='active'
     ORDER BY created_at DESC
     LIMIT 3"
);





// TESTIMONIALS

$testimonials = mysqli_query(
    $conn,
    "SELECT *
     FROM website_testimonials
     LIMIT 3"
);


?>



<!-- SCHOOL HERO -->

<section class="container py-5">


<div class="row align-items-center shadow rounded p-4"
style="
background:white;
border-left:8px solid var(--primary-color);
">


<div class="col-lg-8">


<h1 class="display-5 fw-bold">

<?= e($hero['title'] ?? 'Welcome'); ?>

</h1>



<p class="lead">

<?= nl2br(e($hero['content'] ?? '')); ?>

</p>



<a href="/schlwb/admissions.php"

class="btn btn-primary px-4">

Apply Now

</a>



</div>





<div class="col-lg-4 text-center">


<?php if(!empty($settings['logo'])): ?>

<img

src="/schlwb/uploads/logos/<?= e($settings['logo']); ?>"

class="img-fluid rounded-circle shadow"

style="
max-width:160px;
">

<?php endif; ?>



<h3 class="mt-3">

<?= e($settings['school_name']); ?>

</h3>


<p>

<?= e($settings['motto']); ?>

</p>


</div>


</div>


</section>






<!-- STATISTICS -->

<!-- STATISTICS -->

<section class="container py-5">


<div class="row text-center">


<?php while($stat=mysqli_fetch_assoc($statistics)): ?>


<div class="col-md-3 mb-4">


<div class="card shadow h-100 p-4">


<?php if(!empty($stat['icon'])): ?>

<i class="<?= e($stat['icon']); ?> fa-2x mb-3"
style="
color:var(--primary-color);
"></i>

<?php endif; ?>



<h2 class="fw-bold"

style="
color:var(--primary-color);
">

<?= e($stat['stat_value']); ?>

</h2>



<p>

<?= e($stat['stat_title']); ?>

</p>



</div>


</div>


<?php endwhile; ?>


</div>


</section>






<!-- WHY CHOOSE US -->


<section class="container py-5">


<div class="card shadow p-5 text-center">


<h2>

<?= e($why_choose['title'] ?? 'Why Choose Us'); ?>

</h2>



<p class="lead">

<?= nl2br(e($why_choose['content'] ?? '')); ?>

</p>


</div>


</section>








<!-- PRINCIPAL MESSAGE -->


<section class="container py-5">


<div class="row align-items-center">


<div class="col-lg-4 text-center">


<?php if(!empty($principal['image'])): ?>


<img

src="/schlwb/uploads/website/<?= e($principal['image']); ?>"

class="img-fluid rounded shadow"

>


<?php endif; ?>


</div>





<div class="col-lg-8">


<h2>

<?= e($principal['title'] ?? 'Principal Message'); ?>

</h2>



<p class="lead">

<?= nl2br(e($principal['content'] ?? '')); ?>

</p>


</div>



</div>


</section>








<!-- ACADEMICS -->


<section class="container py-5">


<div class="card shadow p-5"


style="
border-top:5px solid var(--primary-color);
">


<h2 class="text-center">


<?= e($academics['title'] ?? 'Academics'); ?>


</h2>



<p class="text-center lead">


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


<?php while($item=mysqli_fetch_assoc($news)): ?>


<div class="col-md-4 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h5>

<?= e($item['title']); ?>

</h5>



<p>

<?= e(substr($item['message'],0,150)); ?>

...

</p>



</div>


</div>


</div>


<?php endwhile; ?>


</div>


</section>








<!-- TESTIMONIALS -->


<section class="container py-5">


<h2 class="text-center mb-4">

Testimonials

</h2>



<div class="row">


<?php while($test=mysqli_fetch_assoc($testimonials)): ?>


<div class="col-md-4 mb-4">


<div class="card shadow p-4 h-100 text-center">


<p>

"<?= e($test['message']); ?>"

</p>



<h5>

<?= e($test['name']); ?>

</h5>



<small>

<?= e($test['role']); ?>

</small>


</div>


</div>


<?php endwhile; ?>


</div>


</section>








<!-- CALL TO ACTION -->


<section

class="text-center text-white py-5"


style="
background:var(--primary-color);
">


<div class="container">


<h2>

<?= e($cta['title'] ?? 'Admissions Open'); ?>

</h2>



<p class="lead">

<?= e($cta['content'] ?? ''); ?>

</p>




<a href="/schlwb/admissions.php"

class="btn btn-light px-5">

Apply Now

</a>


</div>


</section>


<?php include 'footer.php'?>