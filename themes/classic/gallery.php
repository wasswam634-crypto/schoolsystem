<?php

include 'header.php';
include 'navbar.php';


$gallery = mysqli_query(
    $conn,
    "SELECT *
     FROM gallery
     ORDER BY gallery_id DESC"
);

?>


<div class="container py-5">


<h2 class="text-center mb-5">
School Gallery
</h2>


<div class="row">


<?php while($row = mysqli_fetch_assoc($gallery)){ ?>


<div class="col-md-4 mb-4">


<div class="card shadow h-100">


<img

src="/schlwb/uploads/gallery/<?= e($row['image']); ?>"

class="card-img-top"

style="
height:250px;
object-fit:cover;
">


<div class="card-body">


<h5>

<?= e($row['title']); ?>

</h5>


</div>


</div>


</div>


<?php } ?>


</div>


</div>


<?php include 'footer.php'; ?>