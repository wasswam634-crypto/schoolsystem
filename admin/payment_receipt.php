<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


if(!isset($_GET['id'])){

    die("Payment ID missing");

}


$payment_id=(int)$_GET['id'];



/*
GET PAYMENT DETAILS
*/

$stmt=mysqli_prepare(

$conn,

"SELECT

fee_payments.*,

student_fees.student_id,

student_fees.period_id,

students.full_name,

students.reg_no,

students.class,

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


WHERE payment_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"i",

$payment_id

);



mysqli_stmt_execute($stmt);



$result=mysqli_stmt_get_result($stmt);



$payment=mysqli_fetch_assoc($result);



if(!$payment){

    die("Payment not found");

}



?>


<!DOCTYPE html>

<html>


<head>


<title>
Payment Receipt
</title>


<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">



<style>


body{

background:#f5f6fa;

font-family:Arial;

}



.receipt{

background:white;

width:700px;

margin:40px auto;

padding:40px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.1);

}



.school{

text-align:center;

}



.line{

border-top:2px dashed #333;

margin:20px 0;

}



.amount{

font-size:25px;

font-weight:bold;

}



@media print{


.btn{

display:none;

}


body{

background:white;

}


.receipt{

box-shadow:none;

}


}



</style>



</head>



<body>


<div class="receipt">


<div class="school">


<?php


$school=mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM school_settings LIMIT 1"

)

);


?>


<h2>

<?=e($school['school_name']);?>

</h2>


<p>

<?=e($school['phone'] ?? '');?>

</p>



<h3>

PAYMENT RECEIPT

</h3>



</div>



<div class="line"></div>



<h5>

Receipt No:

<strong>

<?=e($payment['receipt_number']);?>

</strong>

</h5>



<h5>

Date:

<?=e($payment['payment_date']);?>

</h5>




<hr>



<table class="table">


<tr>

<th>
Student Name
</th>

<td>

<?=e($payment['full_name']);?>

</td>

</tr>




<tr>

<th>
Registration No
</th>

<td>

<?=e($payment['reg_no']);?>

</td>

</tr>




<tr>

<th>
Class
</th>

<td>

<?=e($payment['class']);?>

</td>

</tr>




<tr>

<th>
Academic Period
</th>

<td>

<?=e($payment['academic_year']);?>

-

<?=e($payment['period_name']);?>

</td>

</tr>




<tr>

<th>
Payment
</th>

<th>
Receipt
</th>

<td>

<?=e($payment['payment_method']);?>

</td>

</tr>




<tr>

<th>
Amount Paid
</th>




<td class="amount">


$<?=number_format($payment['amount'],2);?>


</td>


</tr>


</table>



<div class="line"></div>



<div class="text-center">


<p>

Received By:

_________________

</p>



<p>

Thank you for your payment.

</p>



</div>



<button

onclick="window.print()"

class="btn btn-primary">

🖨 Print Receipt

</button>



</div>


</body>


</html>