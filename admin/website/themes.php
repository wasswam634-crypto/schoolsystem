<?php
include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');

if(isset($_POST['save'])){

    $theme = $_POST['theme'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE school_settings
         SET theme=?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $theme
    );

    mysqli_stmt_execute($stmt);

    $success = "Theme updated successfully.";
}

$settings = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT theme
         FROM school_settings
         LIMIT 1"
    )
);
?>

<!DOCTYPE html>
<html>
<head>

<title>Theme Management</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2>Theme Management</h2>

<?php if(isset($success)){ ?>

<div class="alert alert-success">
    <?= e($success); ?>
</div>

<?php } ?>

<form method="POST">

<label>Select Theme</label>

<select
name="theme"
class="form-control mb-3">

<option
value="modern"
<?= ($settings['theme'] ?? '') == 'modern' ? 'selected' : ''; ?>>

Modern Theme

</option>

<option
value="classic"
<?= ($settings['theme'] ?? '') == 'classic' ? 'selected' : ''; ?>>

Classic Theme

</option>

<option
value="university"
<?= ($settings['theme'] ?? '') == 'university' ? 'selected' : ''; ?>>

University Theme

</option>

</select>

<button
name="save"
class="btn btn-primary">

Save Theme

</button>

</form>

</div>

</body>
</html>