<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');



$result = mysqli_query(
$conn,

"SELECT

student_fees.student_fee_id,

students.full_name,

students.reg_no,

students.class,


academic_periods.academic_year,

academic_periods.period_name,


student_fees.total_amount,

student_fees.amount_paid,

student_fees.balance,

student_fees.status


FROM student_fees


JOIN students

ON student_fees.student_id = students.student_id


JOIN academic_periods

ON student_fees.period_id = academic_periods.period_id


WHERE student_fees.balance > 0


ORDER BY students.class, students.full_name"

);



$total_debt = 0;


?>


<!DOCTYPE html>

<html>

<head>


<title>
Debtors Report
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



@media print{


.btn{

display:none;

}


body{

background:white;

}


.card{

box-shadow:none;

}


}


</style>



</head>


<body>


<div class="container-fluid p-4">



<div class="d-flex justify-content-between align-items-center mb-4">


<h2>

📌 Debtors Report

</h2>


<div>


<button

onclick="window.print()"

class="btn btn-dark">

Print

</button>



</div>


</div>




<div class="card">



<div class="card-header bg-danger text-white">

Students With Outstanding Fees

</div>




<div class="card-body">



<table class="table table-bordered table-striped">


<thead class="table-dark">


<tr>


<th>
Student
</th>


<th>
Class
</th>


<th>
Academic Period
</th>


<th>
Total Fees
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


</tr>


</thead>



<tbody>


<?php while($row=mysqli_fetch_assoc($result)): ?>


<?php

$total_debt += $row['balance'];

?>


<tr>


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

<?=e($row['academic_year']);?>

-

<?=e($row['period_name']);?>

</td>




<td>

<?=number_format($row['total_amount'],2);?>

</td>




<td>

<?=number_format($row['amount_paid'],2);?>

</td>




<td class="text-danger fw-bold">

<?=number_format($row['balance'],2);?>

</td>




<td>

<?=e($row['status']);?>

</td>



</tr>



<?php endwhile; ?>


</tbody>



<tfoot>


<tr>


<th colspan="5">

TOTAL OUTSTANDING

</th>


<th class="text-danger">


<?=number_format($total_debt,2);?>


</th>


<th>


</th>


</tr>


</tfoot>


</table>



</div>


</div>



</div>


</body>

</html>