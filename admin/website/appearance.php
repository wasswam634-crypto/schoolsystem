<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


// Save appearance settings

if(isset($_POST['save'])){


    $primary   = $_POST['primary_color'];
    $secondary = $_POST['secondary_color'];
    $accent    = $_POST['accent_color'];
    $font      = $_POST['font_family'];



    $stmt = mysqli_prepare(
        $conn,

        "UPDATE school_settings
         SET 
            primary_color=?,
            secondary_color=?,
            accent_color=?,
            font_family=?
         LIMIT 1"

    );


    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $primary,
        $secondary,
        $accent,
        $font
    );


    mysqli_stmt_execute($stmt);


    $success = "Appearance settings saved successfully.";

}



// Get current settings

$result = mysqli_query(
    $conn,
    "SELECT * FROM school_settings LIMIT 1"
);


$settings = mysqli_fetch_assoc($result);



?>


<!DOCTYPE html>

<html>

<head>

<title>
Appearance Settings
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>



<body>


<div class="container mt-5">


<h2 class="mb-4">
Website Appearance
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>




<form method="POST">



<!-- Primary Color -->

<div class="mb-3">

<label class="form-label">

Primary Color

<!-- Primary Color -->

<div class="mb-3">

<label class="form-label">

Primary Color
</label>


<select name="primary_color" class="form-control">


<option value="#000000"
<?= ($settings['primary_color'] ?? '') == '#000000' ? 'selected' : ''; ?>>
Black
</option>


<option value="#0B3D91"
<?= ($settings['primary_color'] ?? '') == '#0B3D91' ? 'selected' : ''; ?>>
Blue
</option>


<option value="#198754"
<?= ($settings['primary_color'] ?? '') == '#198754' ? 'selected' : ''; ?>>
Green
</option>


<option value="#800000"
<?= ($settings['primary_color'] ?? '') == '#800000' ? 'selected' : ''; ?>>
Maroon
</option>


<option value="#6F42C1"
<?= ($settings['primary_color'] ?? '') == '#6F42C1' ? 'selected' : ''; ?>>
Purple
</option>


<option value="#FF8C00"
<?= ($settings['primary_color'] ?? '') == '#FF8C00' ? 'selected' : ''; ?>>
Orange
</option>


</select>

</div>





<!-- Secondary Color -->


<div class="mb-3">

<label class="form-label">
Secondary Color
</label>


<select name="secondary_color" class="form-control">


<option value="#FFFFFF"
<?= ($settings['secondary_color'] ?? '') == '#FFFFFF' ? 'selected' : ''; ?>>
White
</option>


<option value="#0B3D91"
<?= ($settings['secondary_color'] ?? '') == '#0B3D91' ? 'selected' : ''; ?>>
Blue
</option>


<option value="#198754"
<?= ($settings['secondary_color'] ?? '') == '#198754' ? 'selected' : ''; ?>>
Green
</option>


<option value="#FFD700"
<?= ($settings['secondary_color'] ?? '') == '#FFD700' ? 'selected' : ''; ?>>
Gold
</option>


<option value="#DC3545"
<?= ($settings['secondary_color'] ?? '') == '#DC3545' ? 'selected' : ''; ?>>
Red
</option>


</select>

</div>




<!-- Accent Color -->


<div class="mb-3">

<label class="form-label">
Accent Color
</label>


<select name="accent_color" class="form-control">


<option value="#FFD700"
<?= ($settings['accent_color'] ?? '') == '#FFD700' ? 'selected' : ''; ?>>
Gold
</option>


<option value="#DC3545"
<?= ($settings['accent_color'] ?? '') == '#DC3545' ? 'selected' : ''; ?>>
Red
</option>


<option value="#000000"
<?= ($settings['accent_color'] ?? '') == '#000000' ? 'selected' : ''; ?>>
Black
</option>


<option value="#0B3D91"
<?= ($settings['accent_color'] ?? '') == '#0B3D91' ? 'selected' : ''; ?>>
Blue
</option>


<option value="#198754"
<?= ($settings['accent_color'] ?? '') == '#198754' ? 'selected' : ''; ?>>
Green
</option>


</select>

</div>







<!-- Font Family -->


<div class="mb-3">


<label class="form-label">

Font Family

</label>



<select

name="font_family"

class="form-control"

>


<option value="Arial"

<?= ($settings['font_family'] ?? '')=="Arial" ? "selected" : ""; ?>

>

Arial

</option>




<option value="Verdana"

<?= ($settings['font_family'] ?? '')=="Verdana" ? "selected" : ""; ?>

>

Verdana

</option>




<option value="Tahoma"

<?= ($settings['font_family'] ?? '')=="Tahoma" ? "selected" : ""; ?>

>

Tahoma

</option>




<option value="Georgia"

<?= ($settings['font_family'] ?? '')=="Georgia" ? "selected" : ""; ?>

>

Georgia

</option>




<option value="Times New Roman"

<?= ($settings['font_family'] ?? '')=="Times New Roman" ? "selected" : ""; ?>

>

Times New Roman

</option>



</select>


</div>






<button

type="submit"

name="save"

class="btn btn-primary"

>

Save Appearance

</button>



</form>



</div>


</body>


</html>