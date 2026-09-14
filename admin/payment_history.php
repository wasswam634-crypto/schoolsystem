<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| VALIDATE STUDENT FEE ACCOUNT
|--------------------------------------------------------------------------
*/

$student_fee_id = (int) ($_GET['student_fee_id'] ?? 0);

if ($student_fee_id <= 0) {

    die("Invalid student fee account.");
}


/*
|--------------------------------------------------------------------------
| GET STUDENT FEE ACCOUNT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,

    "SELECT

        sf.student_fee_id,
        sf.student_id,
        sf.period_id,
        sf.total_amount,
        sf.previous_balance,

        s.full_name,
        s.reg_no,
        s.class,
        s.stream,

        ap.academic_year,
        ap.period_name

     FROM student_fees sf

     INNER JOIN students s
        ON sf.student_id = s.student_id

     INNER JOIN academic_periods ap
        ON sf.period_id = ap.period_id

     WHERE sf.student_fee_id = ?

     LIMIT 1"
);


if (!$stmt) {

    die(
        "Database error: "
        . mysqli_error($conn)
    );
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_fee_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$account = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


if (!$account) {

    die("Student fee account not found.");
}


/*
|--------------------------------------------------------------------------
| GET ALL PAYMENTS
|--------------------------------------------------------------------------
*/

$payment_stmt = mysqli_prepare(
    $conn,

    "SELECT

        payment_id,
        receipt_number,
        amount,
        payment_date,
        payment_method,
        reference_number,
        notes,
        recorded_by

     FROM fee_payments

     WHERE student_fee_id = ?

     ORDER BY payment_date ASC, payment_id ASC"
);


mysqli_stmt_bind_param(
    $payment_stmt,
    "i",
    $student_fee_id
);


mysqli_stmt_execute($payment_stmt);


$payments_result =
    mysqli_stmt_get_result($payment_stmt);


/*
|--------------------------------------------------------------------------
| CALCULATE TOTALS
|--------------------------------------------------------------------------
*/

$total_fees =
    (float) $account['total_amount'];


$previous_balance =
    (float) $account['previous_balance'];


$total_owed =
    $total_fees + $previous_balance;


$total_paid = 0;

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1">


<title>
Payment History
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container-fluid p-4">


<div class="d-flex justify-content-between align-items-center mb-4">


<div>

<h2>

💳 Payment History

</h2>

<p class="text-muted mb-0">

Complete payment history for this student's fee account.

</p>

</div>


<a
href="fee_payments.php"
class="btn btn-secondary">

← Back to Fee Payments

</a>


</div>


<!--
|--------------------------------------------------------------------------
| STUDENT INFORMATION
|--------------------------------------------------------------------------
-->

<div class="card mb-4">


<div class="card-header bg-dark text-white">

Student Information

</div>


<div class="card-body">


<div class="row">


<div class="col-md-3">

<strong>Student</strong>

<br>

<?= e($account['full_name']); ?>

</div>


<div class="col-md-2">

<strong>Reg. No.</strong>

<br>

<?= e($account['reg_no']); ?>

</div>


<div class="col-md-2">

<strong>Class</strong>

<br>

<?= e($account['class']); ?>

</div>


<div class="col-md-2">

<strong>Stream</strong>

<br>

<?= e($account['stream'] ?? ''); ?>

</div>


<div class="col-md-3">

<strong>Academic Period</strong>

<br>

<?= e($account['academic_year']); ?>

-

<?= e($account['period_name']); ?>

</div>


</div>


</div>

</div>


<!--
|--------------------------------------------------------------------------
| FEE SUMMARY
|--------------------------------------------------------------------------
-->

<div class="row mb-4">


<div class="col-md-3">


<div class="card border-primary">


<div class="card-body">


<h6>
New Period Fees
</h6>


<h4>

UGX <?= number_format($total_fees, 2); ?>

</h4>


</div>

</div>


</div>


<div class="col-md-3">


<div class="card border-warning">


<div class="card-body">


<h6>
Previous Balance
</h6>


<h4>

UGX <?= number_format(
    $previous_balance,
    2
); ?>

</h4>


</div>

</div>


</div>


<div class="col-md-3">


<div class="card border-success">


<div class="card-body">


<h6>
Total Owed
</h6>


<h4>

UGX <?= number_format(
    $total_owed,
    2
); ?>

</h4>


</div>

</div>


</div>


<div class="col-md-3">


<div class="card border-danger">


<div class="card-body">


<h6>
Current Balance
</h6>


<h4 id="currentBalance">

UGX 0.00

</h4>


</div>

</div>


</div>


</div>


<!--
|--------------------------------------------------------------------------
| PAYMENT HISTORY
|--------------------------------------------------------------------------
-->

<div class="card">


<div class="card-header bg-primary text-white">

Payment History

</div>


<div class="card-body">


<div class="table-responsive">


<table class="table table-bordered table-striped align-middle">


<thead class="table-dark">


<tr>

<th>#</th>

<th>Date</th>

<th>Receipt Number</th>

<th>Payment Method</th>

<th>Reference</th>

<th>Amount Paid</th>

<th>Running Balance</th>

<th>Action</th>

</tr>


</thead>


<tbody>


<?php

$counter = 1;

$running_balance = $total_owed;


while (
    $payment =
    mysqli_fetch_assoc($payments_result)
):


    $payment_amount =
        (float) $payment['amount'];


    $total_paid +=
        $payment_amount;


    $running_balance -=
        $payment_amount;


    if ($running_balance < 0) {

        $running_balance = 0;

    }

?>


<tr>


<td>

<?= $counter++; ?>

</td>


<td>

<?= e($payment['payment_date']); ?>

</td>


<td>

<strong>

<?= e(
    $payment['receipt_number']
); ?>

</strong>

</td>


<td>

<?= e(
    $payment['payment_method']
    ?: 'Not specified'
); ?>

</td>


<td>

<?= e(
    $payment['reference_number']
    ?: '-'
); ?>

</td>


<td class="text-success fw-bold">

UGX
<?= number_format(
    $payment_amount,
    2
); ?>

</td>


<td class="fw-bold">

UGX
<?= number_format(
    $running_balance,
    2
); ?>

</td>


<td>


<a
href="payment_receipt.php?id=<?= (int) $payment['payment_id']; ?>"
class="btn btn-dark btn-sm">

🧾 Receipt

</a>


</td>


</tr>


<?php endwhile; ?>


<?php

$current_balance =
    $total_owed - $total_paid;


if ($current_balance < 0) {

    $current_balance = 0;

}

?>


<?php if ($counter === 1): ?>


<tr>


<td colspan="8"
class="text-center text-muted">

No payments have been recorded for this account.

</td>


</tr>


<?php endif; ?>


</tbody>


<tfoot class="table-light">


<tr>


<th colspan="5"
class="text-end">

TOTAL PAID:

</th>


<th class="text-success">

UGX
<?= number_format(
    $total_paid,
    2
); ?>

</th>


<th>

UGX
<?= number_format(
    $current_balance,
    2
); ?>

</th>


<th>

<?php if ($current_balance <= 0): ?>

<span class="badge bg-success">

CLEARED

</span>


<?php elseif ($total_paid > 0): ?>

<span class="badge bg-warning text-dark">

PARTIAL

</span>


<?php else: ?>

<span class="badge bg-danger">

PENDING

</span>

<?php endif; ?>


</th>


</tr>


</tfoot>


</table>


</div>


</div>


</div>


</div>


<script>

/*
|--------------------------------------------------------------------------
| DISPLAY CURRENT BALANCE
|--------------------------------------------------------------------------
*/

document.getElementById(
    "currentBalance"
).textContent =
    "UGX <?= number_format(
        $current_balance,
        2
    ); ?>";

</script>


</body>

</html>