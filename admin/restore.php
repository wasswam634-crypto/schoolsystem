<?php
include '../includes/config.php';
include '../includes/auth.php';

require_role('admin');

$message = "";

if(isset($_POST['restore'])){

    if(isset($_FILES['backup_file']) &&
       $_FILES['backup_file']['error'] == 0){

        $tmp_file = $_FILES['backup_file']['tmp_name'];

        $database = "school-management";

        $command =
            '"C:\xampp\mysql\bin\mysql.exe" ' .
            '-u root ' .
            $database .
            ' < "' . $tmp_file . '"';

        system($command, $result);

        if($result === 0){
            $message = "Database restored successfully.";
        }else{
            $message = "Restore failed.";
        }
    }
}

$page_title = "Restore Database";

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">

<div class="row">

<?php include '../includes/admin_sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2 class="mb-4">
Restore Database
</h2>

<?php if($message): ?>

<div class="alert alert-info">
    <?= $message; ?>
</div>

<?php endif; ?>

<div class="card shadow">

<div class="card-header bg-warning">
Upload Backup File
</div>

<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">

<label class="form-label">
SQL Backup File
</label>

<input
type="file"
name="backup_file"
class="form-control"
accept=".sql"
required>

</div>

<button
type="submit"
name="restore"
class="btn btn-warning">

Restore Database

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>