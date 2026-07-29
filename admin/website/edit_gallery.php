<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$id = $_GET['id'];



$result = mysqli_query(

    $conn,

    "SELECT *
     FROM gallery
     WHERE gallery_id=$id"

);



$image = mysqli_fetch_assoc($result);




if(isset($_POST['update'])){


    $title = $_POST['title'];



    $stmt = mysqli_prepare(

        $conn,

        "UPDATE gallery

         SET title=?

         WHERE gallery_id=?"

    );



    mysqli_stmt_bind_param(

        $stmt,

        "si",

        $title,

        $id

    );



    mysqli_stmt_execute($stmt);



    header("Location: gallery.php");

    exit;


}


?>


<!DOCTYPE html>

<html>

<head>

<title>
Edit Gallery
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap.com/bootstrap/5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>


<body>


<div class="container mt-5">


<h2>
Edit Gallery Image
</h2>


<form method="POST">


<label>

Image Title

</label>


<input

type="text"

name="title"

class="form-control mb-3"

value="<?= e($image['title']); ?>"

required>



<img

src="../../uploads/gallery/<?= e($image['image']); ?>"

width="250"

class="mb-3">



<br>



<button

name="update"

class="btn btn-success">

Update

</button>


</form>


</div>


</body>

</html>