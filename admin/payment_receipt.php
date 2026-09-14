<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| VALIDATE PAYMENT ID
|--------------------------------------------------------------------------
*/

$payment_id = (int) ($_GET['id'] ?? 0);


if ($payment_id <= 0) {

    die("Invalid payment ID.");
}


/*
|--------------------------------------------------------------------------
| GET PAYMENT + STUDENT FEE ACCOUNT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,

    "SELECT

        fp.payment_id,
        fp.student_fee_id,
        fp.receipt_number,
        fp.amount AS payment_amount,
        fp.payment_date,
        fp.payment_method,
        fp.reference_number,
        fp.notes,
        fp.recorded_by,

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

    FROM fee_payments fp

    INNER JOIN student_fees sf
        ON fp.student_fee_id = sf.student_fee_id

    INNER JOIN students s
        ON sf.student_id = s.student_id

    INNER JOIN academic_periods ap
        ON sf.period_id = ap.period_id

    WHERE fp.payment_id = ?

    LIMIT 1"
);


if (!$stmt) {

    die(
        "Failed to prepare payment query: "
        . mysqli_error($conn)
    );
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $payment_id
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        "Failed to load payment: "
        . mysqli_stmt_error($stmt)
    );
}


$result = mysqli_stmt_get_result($stmt);


$payment = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


if (!$payment) {

    die("Payment not found.");
}


/*
|--------------------------------------------------------------------------
| BASIC VALUES
|--------------------------------------------------------------------------
*/

$student_fee_id =
    (int) $payment['student_fee_id'];


$total_fees =
    (float) $payment['total_amount'];


$previous_balance =
    (float) $payment['previous_balance'];


$current_payment =
    (float) $payment['payment_amount'];


/*
|--------------------------------------------------------------------------
| TOTAL AMOUNT OWED
|--------------------------------------------------------------------------
|
| New fees + previous unpaid balance
|
*/

$total_owed =
    $total_fees + $previous_balance;


/*
|--------------------------------------------------------------------------
| CALCULATE TOTAL PAID UP TO THIS PAYMENT
|--------------------------------------------------------------------------
|
| This is important.
|
| We don't just use the current payment.
| We calculate all payments belonging to this
| student's fee account up to this receipt.
|
*/

$paid_stmt = mysqli_prepare(
    $conn,

    "SELECT
        COALESCE(SUM(amount), 0) AS paid_before_or_this_payment

     FROM fee_payments

     WHERE student_fee_id = ?

     AND payment_id <= ?"
);


if (!$paid_stmt) {

    die(
        "Failed to prepare payment calculation: "
        . mysqli_error($conn)
    );
}


mysqli_stmt_bind_param(
    $paid_stmt,
    "ii",
    $student_fee_id,
    $payment_id
);


mysqli_stmt_execute($paid_stmt);


$paid_result =
    mysqli_stmt_get_result($paid_stmt);


$paid_row =
    mysqli_fetch_assoc($paid_result);


mysqli_stmt_close($paid_stmt);


$total_paid_after_payment =
    (float) ($paid_row['paid_before_or_this_payment'] ?? 0);


/*
|--------------------------------------------------------------------------
| REMAINING BALANCE AFTER THIS PAYMENT
|--------------------------------------------------------------------------
*/

$remaining_balance =
    $total_owed - $total_paid_after_payment;


/*
|--------------------------------------------------------------------------
| PROTECT AGAINST NEGATIVE BALANCE
|--------------------------------------------------------------------------
*/

if ($remaining_balance < 0) {

    $remaining_balance = 0;
}


$remaining_balance =
    round($remaining_balance, 2);


/*
|--------------------------------------------------------------------------
| DETERMINE STATUS
|--------------------------------------------------------------------------
*/

if ($remaining_balance <= 0) {

    $status = "Cleared";

} elseif ($total_paid_after_payment > 0) {

    $status = "Partial";

} else {

    $status = "Pending";
}


/*
|--------------------------------------------------------------------------
| GET SCHOOL DETAILS
|--------------------------------------------------------------------------
*/

$school_result = mysqli_query(
    $conn,

    "SELECT *
     FROM school_settings
     LIMIT 1"
);


$school = mysqli_fetch_assoc($school_result);


/*
|--------------------------------------------------------------------------
| SCHOOL INFORMATION
|--------------------------------------------------------------------------
*/

$school_name =
    $school['school_name']
    ?? 'School Name';


$school_phone =
    $school['phone']
    ?? '';


$school_email =
    $school['email']
    ?? '';


$school_address =
    $school['address']
    ?? '';

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1">


<title>
Payment Receipt - <?= e($payment['receipt_number']); ?>
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<style>

body {

    background: #f2f4f7;

    font-family: Arial, sans-serif;

}


.receipt {

    background: white;

    max-width: 800px;

    margin: 30px auto;

    padding: 40px;

    box-shadow: 0 4px 20px rgba(0,0,0,.12);

}


.school-header {

    text-align: center;

}


.school-header h2 {

    margin-bottom: 5px;

    font-weight: bold;

}


.school-header p {

    margin: 2px 0;

}


.receipt-title {

    margin-top: 20px;

    font-weight: bold;

}


.dashed-line {

    border-top: 2px dashed #333;

    margin: 20px 0;

}


.amount-box {

    border: 2px solid #198754;

    padding: 20px;

    text-align: center;

    margin-top: 20px;

    margin-bottom: 20px;

}


.amount-box .label {

    font-size: 15px;

    color: #555;

}


.amount-box .amount {

    font-size: 30px;

    font-weight: bold;

    color: #198754;

}


.summary-table th {

    width: 55%;

}


.remaining {

    font-size: 20px;

    font-weight: bold;

}


.status-cleared {

    color: #198754;

    font-weight: bold;

}


.status-partial {

    color: #d39e00;

    font-weight: bold;

}


.status-pending {

    color: #dc3545;

    font-weight: bold;

}


.signature {

    margin-top: 50px;

}


@media print {

    body {

        background: white;

    }


    .receipt {

        box-shadow: none;

        margin: 0;

        max-width: 100%;

        padding: 20px;

    }


    .no-print {

        display: none !important;

    }

}


</style>

</head>


<body>


<div class="receipt">


<!--
|--------------------------------------------------------------------------
| SCHOOL HEADER
|--------------------------------------------------------------------------
-->

<div class="school-header">


<h2>

<?= e($school_name); ?>

</h2>


<?php if ($school_address !== ''): ?>

<p>

<?= e($school_address); ?>

</p>

<?php endif; ?>


<?php if ($school_phone !== ''): ?>

<p>

Tel: <?= e($school_phone); ?>

</p>

<?php endif; ?>


<?php if ($school_email !== ''): ?>

<p>

Email: <?= e($school_email); ?>

</p>

<?php endif; ?>


<h3 class="receipt-title">

PAYMENT RECEIPT

</h3>


</div>


<div class="dashed-line"></div>


<!--
|--------------------------------------------------------------------------
| RECEIPT INFORMATION
|--------------------------------------------------------------------------
-->

<div class="row mb-3">


<div class="col-md-6">

<strong>
Receipt No:
</strong>

<br>

<?= e($payment['receipt_number']); ?>

</div>


<div class="col-md-6 text-md-end">

<strong>
Payment Date:
</strong>

<br>

<?= e($payment['payment_date']); ?>

</div>


</div>


<!--
|--------------------------------------------------------------------------
| STUDENT INFORMATION
|--------------------------------------------------------------------------
-->

<h5 class="mt-4">

Student Information

</h5>


<table class="table table-bordered">


<tr>

<th>
Student Name
</th>

<td>

<?= e($payment['full_name']); ?>

</td>

</tr>


<tr>

<th>
Registration Number
</th>

<td>

<?= e($payment['reg_no']); ?>

</td>

</tr>


<tr>

<th>
Class
</th>

<td>

<?= e($payment['class']); ?>

</td>

</tr>


<?php if (!empty($payment['stream'])): ?>

<tr>

<th>
Stream
</th>

<td>

<?= e($payment['stream']); ?>

</td>

</tr>

<?php endif; ?>


<tr>

<th>
Academic Period
</th>

<td>

<?= e($payment['academic_year']); ?>

-

<?= e($payment['period_name']); ?>

</td>

</tr>


</table>


<!--
|--------------------------------------------------------------------------
| PAYMENT AMOUNT
|--------------------------------------------------------------------------
-->

<div class="amount-box">


<div class="label">

AMOUNT RECEIVED

</div>


<div class="amount">

UGX <?= number_format($current_payment, 2); ?>

</div>


</div>


<!--
|--------------------------------------------------------------------------
| FEE SUMMARY
|--------------------------------------------------------------------------
-->

<h5>

Fee Summary

</h5>


<table class="table table-bordered summary-table">


<tr>

<th>
New Period Fees
</th>

<td>

UGX <?= number_format($total_fees, 2); ?>

</td>

</tr>


<tr>

<th>
Previous Balance
</th>

<td>

UGX <?= number_format($previous_balance, 2); ?>

</td>

</tr>


<tr class="table-warning">

<th>
Total Amount Owed
</th>

<td>

<strong>

UGX <?= number_format($total_owed, 2); ?>

</strong>

</td>

</tr>


<tr>

<th>
Total Paid After This Payment
</th>

<td class="text-success">

<strong>

UGX <?= number_format(
    $total_paid_after_payment,
    2
); ?>

</strong>

</td>

</tr>


<tr>

<th>
Payment Made On This Receipt
</th>

<td>

UGX <?= number_format(
    $current_payment,
    2
); ?>

</td>

</tr>


<tr class="table-light">

<th>
Remaining Balance
</th>

<td class="remaining">


UGX <?= number_format(
    $remaining_balance,
    2
); ?>


</td>

</tr>


<tr>

<th>
Account Status
</th>

<td>


<?php if ($status === 'Cleared'): ?>

<span class="status-cleared">

✓ CLEARED

</span>


<?php elseif ($status === 'Partial'): ?>

<span class="status-partial">

⚠ PARTIAL

</span>


<?php else: ?>

<span class="status-pending">

PENDING

</span>

<?php endif; ?>


</td>

</tr>


</table>


<!--
|--------------------------------------------------------------------------
| PAYMENT DETAILS
|--------------------------------------------------------------------------
-->

<h5 class="mt-4">

Payment Details

</h5>


<table class="table table-bordered">


<tr>

<th>
Payment Method
</th>

<td>

<?= e(
    $payment['payment_method']
    ?: 'Not specified'
); ?>

</td>

</tr>


<?php if (!empty($payment['reference_number'])): ?>

<tr>

<th>
Reference Number
</th>

<td>

<?= e($payment['reference_number']); ?>

</td>

</tr>

<?php endif; ?>


<?php if (!empty($payment['notes'])): ?>

<tr>

<th>
Notes
</th>

<td>

<?= nl2br(e($payment['notes'])); ?>

</td>

</tr>

<?php endif; ?>


</table>


<!--
|--------------------------------------------------------------------------
| SIGNATURE
|--------------------------------------------------------------------------
-->

<div class="signature">


<div class="row">


<div class="col-md-6">

Received By:

<br><br>

____________________________

</div>


<div class="col-md-6 text-md-end">

Authorized Signature:

<br><br>

____________________________

</div>


</div>


</div>


<div class="text-center mt-4">

<strong>

Thank you for your payment.

</strong>

<br>

Please keep this receipt for your records.

</div>


<!--
|--------------------------------------------------------------------------
| PRINT BUTTON
|--------------------------------------------------------------------------
-->

<div class="text-center mt-4 no-print">


<button
onclick="window.print()"
class="btn btn-primary">

🖨 Print Receipt

</button>


<button
onclick="window.close()"
class="btn btn-secondary">

Close

</button>


</div>


</div>


</body>

</html>