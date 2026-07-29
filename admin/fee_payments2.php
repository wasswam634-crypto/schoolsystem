<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message="";



/*
GET STUDENT ACCOUNTS
*/

$result=mysqli_query(
$conn,

"SELECT

student_fees.*,

students.full_name,

students.reg_no,

academic_periods.academic_year,

academic_periods.period_name


FROM student_fees


JOIN students

ON student_fees.student_id = students.student_id


JOIN academic_periods

ON student_fees.period_id = academic_periods.period_id


ORDER BY student_fees.student_fee_id DESC"

);






/*
SAVE PAYMENT
*/


if(isset($_POST['pay'])){


    $student_fee_id=$_POST['student_fee_id'];

    $amount=$_POST['amount'];

    $method=$_POST['payment_method'];



    $receipt="REC".time();



    /*
    INSERT PAYMENT
    */


    $stmt=mysqli_prepare(

    $conn,

    "INSERT INTO fee_payments

    (
    student_fee_id,
    receipt_number,
    amount,
    payment_date,
    payment_method
    )

    VALUES(?,?,?,?,?)"

    );



    $date=date("Y-m-d");



    mysqli_stmt_bind_param(

    $stmt,

    "isdss",

    $student_fee_id,

    $receipt,

    $amount,

    $date,

    $method

    );



    mysqli_stmt_execute($stmt);






    /*
    UPDATE STUDENT BALANCE
    */


    $update=mysqli_prepare(

    $conn,

    "UPDATE student_fees

SET

amount_paid = amount_paid + ?,

balance = total_amount - (amount_paid + ?),

status =

CASE

WHEN total_amount - (amount_paid + ?) <= 0

THEN 'Cleared'


WHEN (amount_paid + ?) > 0

THEN 'Partial'


ELSE 'Pending'

END

WHERE student_fee_id=?"

    );

    mysqli_stmt_bind_param(

    $update,

    "dddi",

    $amount,

    $amount,

    $amount,

    $student_fee_id

    );



    mysqli_stmt_execute($update);



    $message="Payment recorded successfully.";


}



?>



<!DOCTYPE html>

<html>

<head>

<title>
Fee Payments
</title>


<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">


</head>



<body>


<div class="container-fluid p-4">


<h2>
💵 Fee Payments
</h2>



<?php if($message): ?>

<div class="alert alert-success">

<?=e($message);?>

</div>

<?php endif; ?>




<div class="card">


<div class="card-header bg-primary text-white">

Student Fee Accounts

</div>



<div class="card-body">


<table class="table table-bordered table-striped">


<tr>

<th>
Student
</th>

<th>
Period
</th>


<th>
Total
</th>


<th>
Paid
</th>


<th>
Balance
</th>


<th>
Status
</th>


<th>
Payment
</th>


</tr>




<?php while($row=mysqli_fetch_assoc($result)): ?>


<tr>


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

<?=number_format($row['total_amount']);?>

</td>



<td>

<?=number_format($row['amount_paid']);?>

</td>



<td>

<?=number_format($row['balance']);?>

</td>



<td>


<?=e($row['status']);?>


</td>




<td>


<form method="POST">


<input

type="hidden"

name="student_fee_id"

value="<?= $row['student_fee_id'];?>">



<input

type="number"

name="amount"

class="form-control mb-2"

placeholder="Amount"

required>



<select

name="payment_method"

class="form-control mb-2">


<option>
Cash
</option>


<option>
Bank
</option>


<option>
Mobile Money
</option>


<option>
SchoolPay
</option>


</select>



<button

name="pay"

class="btn btn-success btn-sm">

Receive Payment

</button>


</form>


</td>


</tr>



<?php endwhile; ?>



</table>



</div>


</div>


</div>


</body>

</html>