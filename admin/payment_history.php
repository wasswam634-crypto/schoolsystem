<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
=================================
FILTERS
=================================
*/

$search = $_GET['search'] ?? "";

$date_from = $_GET['date_from'] ?? "";

$date_to = $_GET['date_to'] ?? "";



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
        OR fee_payments.receipt_number LIKE '%$search%'
    )
    ";

}



if($date_from!=""){

    $where[]="
    fee_payments.payment_date >= '$date_from'
    ";

}



if($date_to!=""){

    $where[]="
    fee_payments.payment_date <= '$date_to'
    ";

}



$where_sql="";


if(count($where)>0){

    $where_sql="WHERE ".implode(
        " AND ",
        $where
    );

}




/*
=================================
GET PAYMENT HISTORY
=================================
*/


$result=mysqli_query(

$conn,


"SELECT


fee_payments.*,


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



$where_sql


ORDER BY payment_id DESC"

);





/*
TOTAL COLLECTION
*/


$total=mysqli_query(

$conn,


"SELECT SUM(amount) AS total

FROM fee_payments

$where_sql"

);


$total_row=mysqli_fetch_assoc($total);

$total_amount=$total_row['total'] ?? 0;



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



<div class="d-flex justify-content-between">


<h2>
💳 Payment History
</h2>



<a href="fee_payments.php"

class="btn btn-primary">

Receive Payment

</a>


</div>




<div class="alert alert-success mt-3">

Total Collected:

<strong>

UGX <?=number_format($total_amount);?>

</strong>

</div>





<div class="card mb-4">


<div class="card-body">


<form method="GET">


<div class="row">


<div class="col-md-4">


<input

class="form-control"

name="search"

placeholder="Search student or receipt"

value="<?=e($search);?>">


</div>




<div class="col-md-3">


<input

type="date"

class="form-control"

name="date_from"

value="<?=e($date_from);?>">


</div>




<div class="col-md-3">


<input

type="date"

class="form-control"

name="date_to"

value="<?=e($date_to);?>">


</div>




<div class="col-md-2">


<button

class="btn btn-dark w-100">

Filter

</button>


</div>


</div>


</form>


</div>


</div>







<div class="card">


<div class="card-body">


<table class="table table-bordered table-striped">


<thead class="table-dark">


<tr>


<th>
Receipt
</th>


<th>
Student
</th>


<th>
Class
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
Reference
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

<?=e($row['class']);?>

</td>





<td>

<?=$row['academic_year'];?>

-

<?=$row['period_name'];?>

</td>





<td>

UGX <?=number_format($row['amount']);?>

</td>





<td>

<?=e($row['payment_method']);?>

</td>





<td>

<?=e($row['reference_number']);?>

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