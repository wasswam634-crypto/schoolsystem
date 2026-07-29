<nav class="navbar navbar-expand-xl navbar-dark
 sticky-top"
style="background:var(--primary-color);">


<div class="container">



<!-- SCHOOL BRAND -->

<a 
class="navbar-brand d-flex align-items-center"
href="/schlwb/index.php">


<?php if(!empty($settings['logo'])): ?>


<img

src="/schlwb/uploads/logos/<?= e($settings['logo']); ?>"

class="school-logo"

alt="School Logo">


<?php endif; ?>



<div>


<strong class="school-title">

<?= e($settings['school_name'] ?? 'School Name'); ?>

</strong>



<br>



<small>

<?= e($settings['motto'] ?? 'Excellence in Education'); ?>

</small>



</div>


</a>





<!-- MOBILE TOGGLE -->


<button

class="navbar-toggler"

type="button"

data-bs-toggle="collapse"

data-bs-target="#classicNavbar">


<span class="navbar-toggler-icon"></span>


</button>







<!-- MENU -->


<div

class="collapse navbar-collapse"

id="classicNavbar">



<ul class="navbar-nav ms-auto align-items-lg-center">



<li class="nav-item">

<a class="nav-link"

href="/schlwb/index.php">

Home

</a>

</li>




<li class="nav-item">

<a class="nav-link"

href="/schlwb/about.php">

About

</a>

</li>




<li class="nav-item">

<a class="nav-link"

href="/schlwb/academics.php">

Academics

</a>

</li>




<li class="nav-item">

<a class="nav-link"

href="/schlwb/admissions.php">

Admissions

</a>

</li>




<li class="nav-item">

<a class="nav-link"

href="/schlwb/staff.php">

Staff

</a>

</li>




<li class="nav-item">

<a class="nav-link"

href="/schlwb/gallery.php">

Gallery

</a>

</li>




<li class="nav-item">

<a class="nav-link"

href="/schlwb/vacancies.php">

Vacancies

</a>

</li>




<li class="nav-item">

<a class="nav-link"

href="/schlwb/contact.php">

Contact

</a>

</li>





<li class="nav-item ms-lg-3">


<a

href="/schlwb/portal/login.php"

class="classic-portal">


 Portal


</a>


</li>




</ul>


</div>


</div>


</nav>