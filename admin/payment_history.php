<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');



$result = mysqli_query(
$conn,

"SELECT

fee_payments.payment_id,

fee_payments.receipt_number,

fee_payments.amount,

fee_payments.payment_date,

fee_payments.payment_method,


students.full_name,

students.reg_no,


academic_periods.academic_year,

academic_periods.period_name


FROM fee_payments


JOIN student_fees

ON fee_payments.student_fee_id =
student_fees.student_fee_id


JOIN students

ON student_fees.student_id =
students.student_id


JOIN academic_periods

ON student_fees.period_id =
academic_periods.period_id


ORDER BY fee_payments.payment_id DESC"

);



?>


<!DOCTYPE html>

<html>


<head>

<title>
Payment History
</title>


<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">


<style>


body{

background:#f5f6fa;

}


.card{

border:none;

border-radius:15px;

box-shadow:0 4px 15px rgba(0,0,0,.08);

}


</style>


</head>


<body>


<div class="container-fluid p-4">



<div class="d-flex justify-content-between align-items-center mb-4">


<h2>

💳 Payment History

</h2>


<a href="fee_payments.php"

class="btn btn-primary">

Receive Payment

</a>


</div>





<div class="card">


<div class="card-header bg-dark text-white">

All Payments

</div>


<div class="card-body">



<table class="table table-bordered table-striped">


<thead class="table-dark">


<tr>


<th>
Receipt No
</th>


<th>
Student
</th>


<th>
Period
</th>


<th>
Amount
</th>


<th>
Method
</th>


<th>
Date
</th>


<th>
Action
</th>


</tr>


</thead>




<tbody>


<?php while($row=mysqli_fetch_assoc($result)): ?>


<tr>


<td>

<?=e($row['receipt_number']);?>

</td>




<td>

<?=e($row['full_name']);?>

<br>

<small>

<?=e($row['reg_no']);?>

</small>

</td>




<td>

<?=e($row['academic_year']);?>

-

<?=e($row['period_name']);?>

</td>




<td>

<?=number_format($row['amount'],2);?>

</td>




<td>

<?=e($row['payment_method']);?>

</td>




<td>

<?=e($row['payment_date']);?>

</td>




<td>


<a

href="payment_receipt.php?id=<?=$row['payment_id'];?>"

class="btn btn-success btn-sm">


🖨 Receipt


</a>


</td>


</tr>



<?php endwhile; ?>


</tbody>


</table>



</div>


</div>


</div>


</body>


</html>