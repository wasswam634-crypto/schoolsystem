<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message = "";
$error = "";



/*
=====================================
SAVE PAYMENT
=====================================
*/

if(isset($_POST['pay'])){


    $student_fee_id = intval($_POST['student_fee_id']);

    $amount = floatval($_POST['amount']);

    $method = $_POST['payment_method'];

    $reference = trim($_POST['reference_number']);

    $notes = trim($_POST['notes']);



    if($amount <= 0){

        $error = "Enter a valid payment amount.";

    }else{


        /*
        GET ACCOUNT
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


        $account_result = mysqli_stmt_get_result($check);


        $account = mysqli_fetch_assoc($account_result);



        if(!$account){

            $error = "Student account not found.";

        }

        elseif($amount > $account['balance']){

            $error = "Payment cannot exceed balance.";

        }

        else{


            mysqli_begin_transaction($conn);


            try{


                /*
                CREATE RECEIPT
                */

                $receipt =
                "REC-".date("YmdHis");



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
                    payment_method,
                    reference_number,
                    notes
                    )

                    VALUES(?,?,?,?,?,?,?)"

                );



                $date=date("Y-m-d");



                mysqli_stmt_bind_param(

                    $stmt,

                    "isdssss",

                    $student_fee_id,

                    $receipt,

                    $amount,

                    $date,

                    $method,

                    $reference,

                    $notes

                );



                if(!mysqli_stmt_execute($stmt)){

                    throw new Exception(
                        "Payment saving failed."
                    );

                }




                /*
                UPDATE BALANCE
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
                        "Balance update failed."
                    );

                }



                mysqli_commit($conn);



                $message =
                "Payment received successfully. Receipt: ".$receipt;



            }

            catch(Exception $e){


                mysqli_rollback($conn);


                $error=$e->getMessage();

            }


        }


    }


}



/*
=====================================
FILTERS
=====================================
*/


$search = $_GET['search'] ?? "";

$group = $_GET['group_id'] ?? "";

$period = $_GET['period_id'] ?? "";



$where = [];



if($search!=""){

    $search=mysqli_real_escape_string(
        $conn,
        $search
    );


    $where[]="
    (
    students.full_name LIKE '%$search%'
    OR students.reg_no LIKE '%$search%'
    )
    ";

}



if($group!=""){

    $where[]="students.group_id=".(int)$group;

}



if($period!=""){

    $where[]="student_fees.period_id=".(int)$period;

}



$where_sql="";


if(count($where)>0){

    $where_sql="WHERE ".implode(
        " AND ",
        $where
    );

}




/*
GET ACCOUNTS
*/


$result=mysqli_query(

$conn,

"SELECT


student_fees.*,

students.full_name,

students.reg_no,

students.class,


academic_groups.group_name,


academic_periods.academic_year,

academic_periods.period_name



FROM student_fees


JOIN students

ON student_fees.student_id =
students.student_id


LEFT JOIN academic_groups

ON students.group_id =
academic_groups.group_id


JOIN academic_periods

ON student_fees.period_id =
academic_periods.period_id



$where_sql


ORDER BY student_fee_id DESC"

);



/*
FILTER DATA
*/


$groups=mysqli_query(
$conn,
"SELECT *
 FROM academic_groups
 WHERE status='Active'"
);



$periods=mysqli_query(
$conn,
"SELECT *
 FROM academic_periods
 ORDER BY period_id DESC"
);



?>

<!DOCTYPE html>

<html>

<head>

<title>Fee Payments</title>


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



<div class="card mb-4">

<div class="card-body">


<form method="GET">


<div class="row">


<div class="col-md-4">

<input
class="form-control"
name="search"
placeholder="Search student..."
value="<?=e($search);?>">

</div>


<div class="col-md-3">


<select
name="group_id"
class="form-control">


<option value="">
All Classes
</option>


<?php while($g=mysqli_fetch_assoc($groups)): ?>

<option value="<?=$g['group_id'];?>">

<?=$g['group_name'];?>

</option>

<?php endwhile; ?>


</select>


</div>


<div class="col-md-3">


<select
name="period_id"
class="form-control">


<option value="">
All Periods
</option>


<?php while($p=mysqli_fetch_assoc($periods)): ?>

<option value="<?=$p['period_id'];?>">

<?=$p['academic_year']." - ".$p['period_name'];?>

</option>

<?php endwhile; ?>


</select>


</div>



<div class="col-md-2">

<button class="btn btn-primary w-100">

Search

</button>

</div>


</div>


</form>


</div>

</div>





<div class="card">

<div class="card-body">


<table class="table table-bordered table-striped">


<tr>

<th>Student</th>

<th>Class</th>

<th>Period</th>

<th>Total</th>

<th>Paid</th>

<th>Balance</th>

<th>Status</th>

<th>Payment</th>

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

<?=e($row['group_name']);?>

</td>


<td>

<?=$row['academic_year']." - ".$row['period_name'];?>

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

<?=$row['status'];?>

</td>


<td>

<?php if($row['balance']>0): ?>


<form method="POST">


<input type="hidden"
name="student_fee_id"
value="<?=$row['student_fee_id'];?>">


<input
class="form-control mb-2"
type="number"
name="amount"
placeholder="Amount"
required>



<select
name="payment_method"
class="form-control mb-2">

<option>Cash</option>
<option>Bank</option>
<option>Mobile Money</option>
<option>SchoolPay</option>

</select>



<input
class="form-control mb-2"
name="reference_number"
placeholder="Reference number">



<textarea
class="form-control mb-2"
name="notes"
placeholder="Notes"></textarea>



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


<br>


<a
href="payment_receipt.php?id=<?=$row['student_fee_id'];?>"
class="btn btn-dark btn-sm mt-2">

Receipt

</a>


</td>


</tr>


<?php endwhile; ?>


</table>


</div>

</div>


</div>


</body>

</html>