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


<h1 class="text-center mb-4">
School Gallery
</h1>


<div class="row g-4">


<?php while($row=mysqli_fetch_assoc($gallery)): ?>


<div class="col-md-4">


<div class="card shadow">


<img

src="/schlwb/uploads/gallery/<?= e($row['image']); ?>"

class="img-fluid rounded shadow gallery-img"

style="height:250px;object-fit:cover;">



<div class="card-body">


<h5>

<?= e($row['title']); ?>

</h5>


</div>


</div>


</div>


<?php endwhile; ?>


</div>


</div>


<?php include 'footer.php'; ?>