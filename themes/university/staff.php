<?php

include 'header.php';
include 'navbar.php';



$staff = mysqli_query(

    $conn,

    "SELECT *
     FROM website_staff
     ORDER BY staff_id DESC"

);

?>


<div class="container py-5">


<h1 class="text-center mb-5">

Our Staff

</h1>



<div class="row">


<?php while($person=mysqli_fetch_assoc($staff)): ?>


<div class="col-md-4 mb-4">


<div class="card shadow h-100">


<?php if(!empty($person['photo'])): ?>

<img
src="/schlwb/uploads/staff/<?= e($person['photo']); ?>"
class="card-img-top"
style="
height:300px;
object-fit:cover;
"
alt="<?= e($person['name']); ?>">

<?php endif; ?>



<div class="card-body">


<h4>

<?= e($person['name']); ?>

</h4>


<h6>

<?= e($person['position']); ?>

</h6>


<p>

<?= nl2br(e($person['bio'])); ?>

</p>


</div>


</div>


</div>



<?php endwhile; ?>


</div>


</div>


<?php include 'footer.php'; ?>