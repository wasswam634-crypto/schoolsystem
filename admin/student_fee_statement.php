<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


if(!isset($_GET['student_fee_id'])){

    die("Student fee account missing");

}


$student_fee_id = (int)$_GET['student_fee_id'];



/*
GET STUDENT FEE ACCOUNT
*/

$stmt = mysqli_prepare(

$conn,

"SELECT

student_fees.*,

students.full_name,

students.reg_no,

students.class,


academic_periods.academic_year,

academic_periods.period_name


FROM student_fees


JOIN students

ON student_fees.student_id = students.student_id


JOIN academic_periods

ON student_fees.period_id = academic_periods.period_id


WHERE student_fee_id=?"


);


mysqli_stmt_bind_param(

$stmt,

"i",

$student_fee_id

);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$account = mysqli_fetch_assoc($result);



if(!$account){

    die("Fee account not found");

}





/*
GET PAYMENTS
*/

$payments = mysqli_query(

$conn,

"

SELECT *

FROM fee_payments

WHERE student_fee_id=$student_fee_id

ORDER BY payment_date ASC, payment_id ASC

"

);



/*
CALCULATE PROGRESS
*/

$progress = 0;


if($account['total_amount'] > 0){

    $progress =
    ($account['amount_paid'] / $account['total_amount']) * 100;

}


?>



<!DOCTYPE html>

<html>


<head>


<title>
Student Fee Statement
</title>


<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">



<style>


body{

background:#f5f6fa;

font-family:Arial;

}



.statement{

background:white;

max-width:950px;

margin:30px auto;

padding:35px;

border-radius:15px;

box-shadow:0 4px 20px rgba(0,0,0,.1);

}



.school{

text-align:center;

}



.balance{

font-size:22px;

font-weight:bold;

}



table th{

background:#212529;

color:white;

}



@media print{


.btn{

display:none;

}


body{

background:white;

}


.statement{

box-shadow:none;

}


}



</style>



</head>



<body>



<div class="statement">



<div class="school">


<?php

$school = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM school_settings LIMIT 1"

)

);


?>


<h2>

<?=e($school['school_name']);?>

</h2>


<h3>

STUDENT FEE STATEMENT

</h3>


</div>



<hr>



<h5>
Student Information
</h5>



<table class="table table-bordered">


<tr>

<th>Name</th>

<td>

<?=e($account['full_name']);?>

</td>

</tr>



<tr>

<th>Registration No</th>

<td>

<?=e($account['reg_no']);?>

</td>

</tr>




<tr>

<th>Class</th>

<td>

<?=e($account['class']);?>

</td>

</tr>



<tr>

<th>Academic Period</th>

<td>

<?=e($account['academic_year']);?>

-

<?=e($account['period_name']);?>

</td>

</tr>


</table>






<h5 class="mt-4">

Fee Summary

</h5>



<table class="table table-bordered">


<tr>

<th>
Total Fees Charged
</th>


<td>

<?=number_format($account['total_amount'],2);?>

</td>


</tr>




<tr>

<th>
Total Paid
</th>


<td class="text-success">

<?=number_format($account['amount_paid'],2);?>

</td>


</tr>





<tr>

<th>
Current Balance
</th>


<td class="text-danger balance">


<?=number_format($account['balance'],2);?>


</td>


</tr>


</table>





<h5>

Payment Progress

</h5>


<div class="progress mb-4">


<div

class="progress-bar bg-success"

role="progressbar"

style="width:<?=round($progress);?>%">


<?=round($progress);?>%


</div>


</div>







<h5>

Account Statement

</h5>


<table class="table table-bordered">


<thead>


<tr>


<th>
Date
</th>


<th>
Description
</th>


<th>
Debit
</th>


<th>
Credit
</th>


<th>
Balance
</th>


</tr>


</thead>




<tbody>



<?php



$current_balance = $account['total_amount'];



?>


<tr>


<td>

<?=e($account['created_at']);?>

</td>


<td>

School Fees Charged

</td>


<td>

<?=number_format($account['total_amount'],2);?>

</td>


<td>

-

</td>


<td>

<?=number_format($current_balance,2);?>

</td>


</tr>





<?php while($payment=mysqli_fetch_assoc($payments)): ?>



<?php


$current_balance -= $payment['amount'];


?>



<tr>


<td>

<?=e($payment['payment_date']);?>

</td>



<td>


Payment

<br>


<small>

Receipt:

<?=e($payment['receipt_number']);?>

</small>


</td>



<td>

-

</td>



<td class="text-success">

<?=number_format($payment['amount'],2);?>

</td>



<td>


<?=number_format($current_balance,2);?>


</td>


</tr>



<?php endwhile; ?>




</tbody>


</table>







<div class="mt-4">


<button

onclick="window.print()"

class="btn btn-primary">


🖨 Print Statement


</button>


</div>




</div>



</body>


</html>