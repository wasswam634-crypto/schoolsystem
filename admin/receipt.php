<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

if(!isset($_GET['id'])){
    die("Receipt ID missing");
}

$fee_id = (int) $_GET['id'];

$query = mysqli_prepare(
    $conn,
    "SELECT
        fees.*,
        students.full_name,
        students.reg_no,
        students.class
     FROM fees
     JOIN students
        ON fees.student_id = students.student_id
     WHERE fees.fee_id = ?"
);

mysqli_stmt_bind_param(
    $query,
    "i",
    $fee_id
);

mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);

$receipt = mysqli_fetch_assoc($result);

if(!$receipt){
    die("Receipt not found");
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Fee Receipt</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<button
onclick="window.print()"
class="btn btn-primary mb-3">

Print Receipt

</button>

<div class="card">

<div class="card-body">

<div class="text-center">

<h2>School Fee Receipt</h2>

<p>
Receipt Number:
<strong>
RCP-<?= str_pad($receipt['fee_id'],5,'0',STR_PAD_LEFT); ?>
</strong>
</p>

</div>

<hr>

<p>
<strong>Student:</strong>
<?= e($receipt['full_name']); ?>
</p>

<p>
<strong>Registration Number:</strong>
<?= e($receipt['reg_no']); ?>
</p>

<p>
<strong>Class:</strong>
<?= e($receipt['class']); ?>
</p>

<p>
<strong>Term:</strong>
<?= e($receipt['term']); ?>
</p>

<p>
<strong>Academic Year:</strong>
<?= e($receipt['academic_year']); ?>
</p>

<hr>

<p>
<strong>Amount Due:</strong>
UGX <?= number_format($receipt['amount_due']); ?>
</p>

<p>
<strong>Amount Paid:</strong>
UGX <?= number_format($receipt['amount_paid']); ?>
</p>

<p class="text-danger">
<strong>Balance:</strong>
UGX <?= number_format($receipt['balance']); ?>
</p>

<p>
<strong>Payment Date:</strong>
<?= e($receipt['payment_date']); ?>
</p>

<br><br>

<div class="row">

<div class="col-md-6">
____________________
<br>
Bursar
</div>

<div class="col-md-6 text-end">
____________________
<br>
School Stamp
</div>

</div>

</div>

</div>

</div>

</body>
</html>