<?php

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Student Statement";

include '../includes/header.php';
include '../includes/navbar.php';


$student_fee_id = $_GET['id'] ?? null;


?>


<div class="container-fluid">

<div class="row">


<?php include '../includes/admin_sidebar.php'; ?>


<div class="col-md-10 p-4">


<h2 class="mb-4">
Student Fee Statement
</h2>



<?php

if(!$student_fee_id){

echo '<div class="alert alert-warning">
No student selected.
</div>';

}else{


$statement = mysqli_query(

$conn,

"SELECT

sf.*,

s.full_name,

s.reg_no,

a.academic_year,

a.period_name


FROM student_fees sf


JOIN students s

ON sf.student_id=s.student_id


JOIN academic_periods a

ON sf.period_id=a.period_id


WHERE sf.student_fee_id='$student_fee_id'"

);



$data=mysqli_fetch_assoc($statement);



if($data){



?>



<div class="card shadow">


<div class="card-header bg-primary text-white">

Student Information

</div>


<div class="card-body">


<table class="table table-bordered">


<tr>

<th>Name</th>

<td>

<?= e($data['full_name']); ?>

</td>

</tr>



<tr>

<th>Registration Number</th>

<td>

<?= e($data['reg_no']); ?>

</td>

</tr>



<tr>

<th>Academic Period</th>

<td>

<?= e($data['academic_year']); ?>

-

<?= e($data['period_name']); ?>

</td>

</tr>



<tr>

<th>Total Fees</th>

<td>

<?= number_format($data['total_amount']); ?>

</td>

</tr>



<tr>

<th>Amount Paid</th>

<td>

<?= number_format($data['amount_paid']); ?>

</td>

</tr>



<tr>

<th>Balance</th>

<td>

<?= number_format($data['balance']); ?>

</td>

</tr>



<tr>

<th>Status</th>

<td>

<?= e($data['status']); ?>

</td>

</tr>



</table>


</div>

</div>





<div class="card shadow mt-4">


<div class="card-header bg-dark text-white">

Payment History

</div>



<div class="card-body">


<table class="table table-bordered table-striped">


<thead>

<tr>

<th>Receipt</th>

<th>Date</th>

<th>Amount</th>

<th>Method</th>

</tr>

</thead>



<tbody>



<?php


$payments=mysqli_query(

$conn,


"SELECT *

FROM fee_payments

WHERE student_fee_id='$student_fee_id'

ORDER BY payment_id DESC"

);



while($payment=mysqli_fetch_assoc($payments)){


?>

<tr>


<td>

<?= e($payment['receipt_number']); ?>

</td>



<td>

<?= e($payment['payment_date']); ?>

</td>



<td>

<?= number_format($payment['amount']); ?>

</td>



<td>

<?= e($payment['payment_method']); ?>

</td>


</tr>


<?php } ?>


</tbody>


</table>



</div>

</div>




<button

onclick="window.print()"

class="btn btn-success mt-3">

Print Statement

</button>



<?php


}else{


echo '<div class="alert alert-danger">
Statement not found.
</div>';


}


}


?>



</div>

</div>

</div>



<?php include '../includes/footer.php'; ?>