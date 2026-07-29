<?php
session_start();

if(file_exists("lock.php")){
    die("The system has already been installed.");
}

if(
    !isset($_SESSION['db_host']) ||
    !isset($_SESSION['db_name'])
){
    header("Location: database.php");
    exit;
}

if(isset($_POST['continue'])){

    $_SESSION['school_name']=trim($_POST['school_name']);
    $_SESSION['motto']=trim($_POST['motto']);
    $_SESSION['address']=trim($_POST['address']);
    $_SESSION['phone']=trim($_POST['phone']);
    $_SESSION['email']=trim($_POST['email']);
    $_SESSION['theme']=trim($_POST['theme']);

    if(
        isset($_FILES['logo']) &&
        $_FILES['logo']['error']==0
    ){

        if(!is_dir("../uploads/logos")){
            mkdir("../uploads/logos",0777,true);
        }

        $extension=pathinfo(
            $_FILES['logo']['name'],
            PATHINFO_EXTENSION
        );

        $filename=uniqid("logo_").".".$extension;

        move_uploaded_file(
            $_FILES['logo']['tmp_name'],
            "../uploads/logos/".$filename
        );

        $_SESSION['logo']=$filename;

    }

    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>School Information</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>

Step 3 : School Information

</h3>

</div>

<div class="card-body">

<form
method="POST"
enctype="multipart/form-data">

<div class="mb-3">

<label>

School Name

</label>

<input
type="text"
name="school_name"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

School Motto

</label>

<input
type="text"
name="motto"
class="form-control">

</div>

<div class="mb-3">

<label>

Address

</label>

<textarea
name="address"
class="form-control"
rows="3"></textarea>

</div>

<div class="row">

<div class="col-md-6">

<label>

Phone

</label>

<input
type="text"
name="phone"
class="form-control">

</div>

<div class="col-md-6">

<label>

Email

</label>

<input
type="email"
name="email"
class="form-control">

</div>

</div>

<br>

<div class="mb-3">

<label>

Default Theme

</label>

<select
name="theme"
class="form-control">

<option value="modern">
Modern Theme
</option>

<option value="classic">
Classic Theme
</option>

<option value="university">
University Theme
</option>

</select>

</div>

<div class="mb-3">

<label>

School Logo

</label>

<input
type="file"
name="logo"
class="form-control"
accept=".jpg,.jpeg,.png,.webp">

</div>

<div class="d-flex justify-content-between">

<a
href="database.php"
class="btn btn-secondary">

Back

</a>

<button
type="submit"
name="continue"
class="btn btn-primary">

Continue

</button>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>