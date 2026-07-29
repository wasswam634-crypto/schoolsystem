<nav
 class="navbar navbar-expand-lg navbar-dark"
     style="background:#0B3D91;">

<div class="container">
<a class="navbar-brand d-flex align-items-center" 
href="/schlwb/index.php">


<?php if(!empty($settings['logo'])): ?>

<img

src="/schlwb/uploads/logos/<?= e($settings['logo']); ?>"

class="school-logo"

alt="School Logo">


<?php endif; ?>



<div>

<strong>

<?= e($settings['school_name']); ?>

</strong>


<br>


<small>

<?= e($settings['motto'] ?? 'Excellence in Education'); ?>

</small>


</div>


</a>


<button
class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#navbarNav">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="navbarNav">

<ul class="navbar-nav ms-auto">

<li class="nav-item">
<a class="nav-link" href="/schlwb/index.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/about.php">About</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/academics.php">Programs</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/admissions.php">Admissions</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/contact.php">Contact</a>
</li>
<li class="nav-item">
<a class="nav-link" href="/schlwb/gallery.php">Gallery</a>
</li>

<li class="nav-item">
<a class="nav-link" href="/schlwb/staff.php">Staff</a>
</li>
<li class="nav-item">
<a class="nav-link" href="/schlwb/vacancies.php">Vacancies</a>
</li>

<li class="nav-item">
<a class="btn btn-university ms-3"
href="/schlwb/portal/login.php">

Student Portal

</a>
</li>

</ul>

</div>

</div>

</nav>