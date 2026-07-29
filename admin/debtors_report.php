<?php

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$page_title = "Debtors Report";


include '../includes/header.php';
include '../includes/navbar.php';


?>


<div class="container-fluid">

<div class="row">


<?php include '../includes/admin_sidebar.php'; ?>


<div class="col-md-10 p-4">


<h2 class="mb-4">

Students With Outstanding Balances

</h2>



<div class="card shadow">


<div class="card-header bg-danger text-white">

Debtors List

</div>


<div class="card-body">


<table class="table table-bordered table-striped">


<thead>

<tr>

<th>#</th>

<th>Student</th>

<th>Registration No</th>

<th>Period</th>

<th>Total Fees</th>

<th>Paid</th>

<th>Balance</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>



<tbody>


<?php


$query=mysqli_query(

$conn,


"SELECT

sf.student_fee_id,

sf.total_amount,

sf.amount_paid,

sf.balance,

sf.status,


s.full_name,

s.reg_no,


a.academic_year,

a.period_name


FROM student_fees sf


JOIN students s

ON sf.student_id=s.student_id


JOIN academic_periods a

ON sf.period_id=a.period_id


WHERE sf.balance > 0


ORDER BY sf.balance DESC"

);



$count=1;



while($row=mysqli_fetch_assoc($query)){


?>


<tr>


<td>

<?= $count++; ?>

</td>



<td>

<?= e($row['full_name']); ?>

</td>



<td>

<?= e($row['reg_no']); ?>

</td>



<td>

<?= e($row['academic_year']); ?>

-

<?= e($row['period_name']); ?>

</td>



<td>

<?= number_format($row['total_amount']); ?>

</td>



<td>

<?= number_format($row['amount_paid']); ?>

</td>



<td class="text-danger">

<?= number_format($row['balance']); ?>

</td>



<td>


<?php if($row['status']=="Partial"){ ?>

<span class="badge bg-warning">

Partial

</span>


<?php }else{ ?>


<span class="badge bg-danger">

Pending

</span>


<?php } ?>


</td>



<td>


<a href="student_statement.php?id=<?= $row['student_fee_id']; ?>"

class="btn btn-sm btn-info">

Statement

</a>


</td>


</tr>



<?php } ?>


</tbody>


</table>


</div>


</div>


<br>


<button

onclick="window.print()"

class="btn btn-dark">

Print Debtors List

</button>



</div>

</div>

</div>



<?php include '../includes/footer.php'; ?>