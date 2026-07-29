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
Current Vacancies
</h1>



<?php while($job=mysqli_fetch_assoc($vacancies)){ ?>


<div class="card shadow mb-4">


<div class="card-body">


<h3>
<?= e($job['title']); ?>
</h3>


<p>
<strong>Department:</strong>
<?= e($job['department']); ?>
</p>


<p>
<?= nl2br(e($job['description'])); ?>
</p>


<p>
<strong>Application Deadline:</strong>
<?= e($job['deadline']); ?>
</p>


</div>


</div>


<?php } ?>


</div>


<?php include 'footer.php'; ?>