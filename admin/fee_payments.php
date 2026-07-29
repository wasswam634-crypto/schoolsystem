<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message = "";
$error = "";



/*
=========================================
SAVE PAYMENT
=========================================
*/

if(isset($_POST['pay'])){


    $student_fee_id = intval($_POST['student_fee_id']);

    $amount = floatval($_POST['amount']);

    $method = $_POST['payment_method'];



    if($amount <= 0){

        $error = "Enter a valid payment amount.";

    }

    else{


        /*
        GET CURRENT BALANCE
        */

        $check = mysqli_prepare(
            $conn,

            "SELECT balance
             FROM student_fees
             WHERE student_fee_id=?"
        );


        mysqli_stmt_bind_param(
            $check,
            "i",
            $student_fee_id
        );


        mysqli_stmt_execute($check);


        $check_result = mysqli_stmt_get_result($check);


        $account = mysqli_fetch_assoc($check_result);



        if(!$account){

            $error="Student account not found.";

        }

        elseif($amount > $account['balance']){


            $error="Payment cannot exceed the remaining balance.";


        }

        else{


            /*
            START TRANSACTION
            */

            mysqli_begin_transaction($conn);



            try{


                /*
                CREATE RECEIPT NUMBER
                */

                $receipt =
                "REC".date("YmdHis");



                /*
                INSERT PAYMENT HISTORY
                */

                $stmt = mysqli_prepare(
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



                if(!mysqli_stmt_execute($stmt)){


                    throw new Exception(
                        "Payment record failed."
                    );


                }




                /*
                UPDATE STUDENT ACCOUNT
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


                    WHEN amount_paid + ? > 0

                    THEN 'Partial'


                    ELSE 'Pending'


                    END


                    WHERE student_fee_id=?"

                );



                mysqli_stmt_bind_param(

                    $update,

                    "ddddi",

                    $amount,

                    $amount,

                    $amount,

                    $amount,

                    $student_fee_id

                );



                if(!mysqli_stmt_execute($update)){


                    throw new Exception(
                        "Account update failed."
                    );


                }



                mysqli_commit($conn);



                $message =
                "Payment recorded successfully. Receipt: ".$receipt;



            }

            catch(Exception $e){


                mysqli_rollback($conn);


                $error=$e->getMessage();


            }



        }


    }


}





/*
=========================================
GET STUDENT ACCOUNTS
=========================================
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

ON student_fees.student_id =
students.student_id



JOIN academic_periods

ON student_fees.period_id =
academic_periods.period_id



ORDER BY student_fee_id DESC"

);



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




<?php if($error): ?>

<div class="alert alert-danger">

<?=e($error);?>

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

<?php if($row['status']=="Cleared"): ?>

<span class="badge bg-success">

Cleared

</span>


<?php elseif($row['status']=="Partial"): ?>


<span class="badge bg-warning">

Partial

</span>


<?php else: ?>


<span class="badge bg-danger">

Pending

</span>


<?php endif; ?>


</td>




<td>


<?php if($row['balance'] > 0): ?>


<form method="POST">


<input

type="hidden"

name="student_fee_id"

value="<?=$row['student_fee_id'];?>">



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


<?php else: ?>


<span class="text-success">

Fully Paid

</span>


<?php endif; ?>


</td>



</tr>



<?php endwhile; ?>



</table>



</div>


</div>


</div>


</body>


</html>