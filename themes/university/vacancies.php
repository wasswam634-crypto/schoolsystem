<?php

include 'header.php';
include 'navbar.php';



$vacancies = mysqli_query(

    $conn,

    "SELECT *
     FROM vacancies
     WHERE status='open'
     ORDER BY vacancy_id DESC"

);

?>


<div class="container py-5">


<h1 class="text-center mb-5">

Career Opportunities

</h1>



<div class="row">


<?php while($vacancy=mysqli_fetch_assoc($vacancies)): ?>


<div class="col-md-6 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h3>

<?= e($vacancy['title']); ?>

</h3>


<h5 class="text-muted">

Department:

<?= e($vacancy['department']); ?>

</h5>



<p>

<?= nl2br(e($vacancy['description'])); ?>

</p>



<p>

<strong>
Application Deadline:
</strong>

<?= e($vacancy['deadline']); ?>

</p>



<a

href="/schlwb/contact.php"

class="btn btn-primary">

Apply / Contact School

</a>


</div>


</div>


</div>


<?php endwhile; ?>


</div>


</div>



<?php include 'footer.php'; ?>