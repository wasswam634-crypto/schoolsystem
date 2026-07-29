<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


if(isset($_POST['save'])){


    $title = $_POST['title'];


    $image = '';



    if(!empty($_FILES['image']['name'])){


        $image = time().'_'.$_FILES['image']['name'];


        move_uploaded_file(

            $_FILES['image']['tmp_name'],

            "../../uploads/gallery/".$image

        );


    }



    $stmt = mysqli_prepare(

        $conn,

        "INSERT INTO gallery
        (
            title,
            image
        )
        VALUES
        (?,?)"

    );



    mysqli_stmt_bind_param(

        $stmt,

        "ss",

        $title,

        $image

    );



    mysqli_stmt_execute($stmt);



    $success = "Gallery image uploaded successfully.";

}




$gallery = mysqli_query(

    $conn,

    "SELECT *
     FROM gallery
     ORDER BY gallery_id DESC"

);

?>


<!DOCTYPE html>

<html>

<head>

<title>
Gallery Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>
Gallery Management
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>



<form method="POST" enctype="multipart/form-data">


<label class="form-label">

Image Title

</label>


<input

type="text"

name="title"

class="form-control mb-3"

required>



<label class="form-label">

Select Image

</label>


<input

type="file"

name="image"

class="form-control mb-3"

required>



<button

name="save"

class="btn btn-primary">

Upload Image

</button>



</form>



<hr>



<h3>
Existing Gallery
</h3>



<div class="row">


<?php while($row=mysqli_fetch_assoc($gallery)): ?>


<div class="col-md-4 mb-4">


<div class="card shadow">


<img

src="../../uploads/gallery/<?= e($row['image']); ?>"

class="card-img-top"

height="220">



<div class="card-body">


<h5>

<?= e($row['title']); ?>

</h5>



<a

href="edit_gallery.php?id=<?= $row['gallery_id']; ?>"

class="btn btn-warning btn-sm">

Edit

</a>



<a

href="delete_gallery.php?id=<?= $row['gallery_id']; ?>"

onclick="return confirm('Delete this image?')"

class="btn btn-danger btn-sm">

Delete

</a>



</div>


</div>


</div>



<?php endwhile; ?>


</div>



</div>


</body>

</html>