<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

if(isset($_POST['upload'])){

    $title = trim($_POST['title']);

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $file_type = mime_content_type(
            $_FILES['image']['tmp_name']
        );

        if(in_array($file_type, $allowed_types, true)){

            $extension = pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            );

            $filename =
                uniqid('gallery_', true)
                . '.'
                . $extension;

            $destination =
                '../uploads/gallery/'
                . $filename;

            if(move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $destination
            )){

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO gallery(title,image)
                     VALUES(?,?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ss",
                    $title,
                    $filename
                );

                mysqli_stmt_execute($stmt);

                $success =
                    "Image uploaded successfully.";
            }
        }
    }
}

$page_title = "Gallery Management";

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">

<div class="row">

<?php include '../includes/admin_sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2 class="mb-4">
Gallery Management
</h2>

<?php if(isset($success)): ?>

<div class="alert alert-success">
    <?= e($success); ?>
</div>

<?php endif; ?>

<div class="card shadow mb-4">

<div class="card-header bg-primary text-white">
Upload Image
</div>

<div class="card-body">

<form
method="POST"
enctype="multipart/form-data">

<div class="mb-3">

<label>Image Title</label>

<input
type="text"
name="title"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Select Image</label>

<input
type="file"
name="image"
class="form-control"
accept=".jpg,.jpeg,.png,.webp"
required>

</div>

<button
name="upload"
class="btn btn-primary">

Upload Image

</button>

</form>

</div>

</div>

<div class="row">

<?php

$images = mysqli_query(
    $conn,
    "SELECT *
     FROM gallery
     ORDER BY uploaded_at DESC"
);

while($row = mysqli_fetch_assoc($images)){

?>

<div class="col-md-4 mb-4">

<div class="card shadow">

<img
src="../uploads/gallery/<?= e($row['image']); ?>"
class="card-img-top"
style="height:250px; object-fit:cover;">

<div class="card-body">

<h5>
<?= e($row['title']); ?>
</h5>

<p class="text-muted">
<?= e($row['uploaded_at']); ?>
</p>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>