<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| GET STUDENT FEE ACCOUNT
|--------------------------------------------------------------------------
*/

$student_fee_id = (int) ($_GET['student_fee_id'] ?? 0);

if ($student_fee_id <= 0) {
    set_error("Invalid student fee account.");
    redirect("fee_payments.php");
}


/*
|--------------------------------------------------------------------------
| LOAD FEE ACCOUNT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,

    "SELECT
        student_fees.student_fee_id,
        student_fees.student_id,
        student_fees.period_id,
        student_fees.total_amount,
        student_fees.previous_balance,

        students.full_name,
        students.reg_no,
        students.class,
        students.stream,

        academic_groups.group_name,

        academic_periods.academic_year,
        academic_periods.period_name

     FROM student_fees

     INNER JOIN students
        ON student_fees.student_id = students.student_id

     LEFT JOIN academic_groups
        ON students.group_id = academic_groups.group_id

     INNER JOIN academic_periods
        ON student_fees.period_id = academic_periods.period_id

     WHERE student_fees.student_fee_id = ?"
);


if (!$stmt) {

    die(
        "Could not prepare fee account query: "
        . mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_fee_id
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        "Could not load fee account: "
        . mysqli_stmt_error($stmt)
    );

}


$result = mysqli_stmt_get_result($stmt);

$account = mysqli_fetch_assoc($result);


if (!$account) {

    set_error("Student fee account not found.");

    redirect("fee_payments.php");

}


/*
|--------------------------------------------------------------------------
| CALCULATE TOTAL PREVIOUS PAYMENTS
|--------------------------------------------------------------------------
*/

$paid_stmt = mysqli_prepare(
    $conn,

    "SELECT
        COALESCE(SUM(amount), 0) AS total_paid

     FROM fee_payments

     WHERE student_fee_id = ?"
);


if (!$paid_stmt) {

    die(
        "Could not prepare payment calculation: "
        . mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $paid_stmt,
    "i",
    $student_fee_id
);


if (!mysqli_stmt_execute($paid_stmt)) {

    die(
        "Could not calculate previous payments: "
        . mysqli_stmt_error($paid_stmt)
    );

}


$paid_result = mysqli_stmt_get_result($paid_stmt);

$paid_row = mysqli_fetch_assoc($paid_result);


$total_paid_before =
    (float) ($paid_row['total_paid'] ?? 0);


/*
|--------------------------------------------------------------------------
| CALCULATE BALANCE
|--------------------------------------------------------------------------
*/

$total_amount =
    (float) $account['total_amount'];

$previous_balance =
    (float) $account['previous_balance'];

$total_owed =
    $total_amount + $previous_balance;

$total_owed =
    round($total_owed, 2);


$current_balance =
    $total_owed - $total_paid_before;

$current_balance =
    round($current_balance, 2);


if ($current_balance < 0) {
    $current_balance = 0;
}


/*
|--------------------------------------------------------------------------
| SAVE PAYMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST['pay'])) {

    $amount =
        (float) ($_POST['amount'] ?? 0);

    $method =
        trim($_POST['payment_method'] ?? '');

    $reference =
        trim($_POST['reference_number'] ?? '');

    $payment_date =
        trim(
            $_POST['payment_date']
            ?? date('Y-m-d')
        );

    $notes =
        trim($_POST['notes'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATE AMOUNT
    |--------------------------------------------------------------------------
    */

    if ($amount <= 0) {

        set_error(
            "Enter a valid payment amount."
        );

        redirect(
            "receive_payment.php?student_fee_id="
            . $student_fee_id
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PREVENT PAYMENT AFTER FULL CLEARANCE
    |--------------------------------------------------------------------------
    */

    if ($current_balance <= 0) {

        set_error(
            "This student's fee account is already fully paid."
        );

        redirect(
            "fee_payments.php"
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PREVENT OVERPAYMENT
    |--------------------------------------------------------------------------
    */

    if ($amount > $current_balance) {

        set_error(
            "Payment cannot exceed the outstanding balance of "
            . number_format(
                $current_balance,
                2
            )
        );

        redirect(
            "receive_payment.php?student_fee_id="
            . $student_fee_id
        );

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE PAYMENT METHOD
    |--------------------------------------------------------------------------
    */

    if ($method === '') {

        set_error(
            "Please select a payment method."
        );

        redirect(
            "receive_payment.php?student_fee_id="
            . $student_fee_id
        );

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE PAYMENT DATE
    |--------------------------------------------------------------------------
    */

    $date_object =
        DateTime::createFromFormat(
            'Y-m-d',
            $payment_date
        );


    if (
        !$date_object ||
        $date_object->format('Y-m-d')
            !== $payment_date
    ) {

        set_error(
            "Invalid payment date."
        );

        redirect(
            "receive_payment.php?student_fee_id="
            . $student_fee_id
        );

    }


    /*
    |--------------------------------------------------------------------------
    | START TRANSACTION
    |--------------------------------------------------------------------------
    */

    mysqli_begin_transaction($conn);


    try {

        /*
        |--------------------------------------------------------------------------
        | LOCK FEE ACCOUNT
        |--------------------------------------------------------------------------
        */

        $check = mysqli_prepare(
            $conn,

            "SELECT
                student_fee_id,
                student_id,
                period_id,
                total_amount,
                previous_balance

             FROM student_fees

             WHERE student_fee_id = ?

             FOR UPDATE"
        );


        if (!$check) {

            throw new Exception(
                "Could not prepare fee account query: "
                . mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $check,
            "i",
            $student_fee_id
        );


        if (!mysqli_stmt_execute($check)) {

            throw new Exception(
                "Could not load fee account: "
                . mysqli_stmt_error($check)
            );

        }


        $account_result =
            mysqli_stmt_get_result($check);

        $locked_account =
            mysqli_fetch_assoc($account_result);


        if (!$locked_account) {

            throw new Exception(
                "Student fee account not found."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RECALCULATE PAYMENTS INSIDE TRANSACTION
        |--------------------------------------------------------------------------
        */

        $paid_stmt = mysqli_prepare(
            $conn,

            "SELECT
                COALESCE(SUM(amount), 0) AS total_paid

             FROM fee_payments

             WHERE student_fee_id = ?"
        );


        if (!$paid_stmt) {

            throw new Exception(
                "Could not prepare payment calculation: "
                . mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $paid_stmt,
            "i",
            $student_fee_id
        );


        if (!mysqli_stmt_execute($paid_stmt)) {

            throw new Exception(
                "Could not calculate previous payments: "
                . mysqli_stmt_error($paid_stmt)
            );

        }


        $paid_result =
            mysqli_stmt_get_result($paid_stmt);

        $paid_row =
            mysqli_fetch_assoc($paid_result);


        $total_paid_before =
            (float) ($paid_row['total_paid'] ?? 0);


        /*
        |--------------------------------------------------------------------------
        | CALCULATE CURRENT BALANCE
        |--------------------------------------------------------------------------
        */

        $total_amount =
            (float) (
                $locked_account['total_amount']
                ?? 0
            );

        $previous_balance =
            (float) (
                $locked_account['previous_balance']
                ?? 0
            );


        $total_owed =
            $total_amount
            + $previous_balance;


        $current_balance =
            $total_owed
            - $total_paid_before;


        $current_balance =
            round(
                $current_balance,
                2
            );


        if ($current_balance < 0) {
            $current_balance = 0;
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($current_balance <= 0) {

            throw new Exception(
                "This student's fee account is already fully paid."
            );

        }


        if ($amount > $current_balance) {

            throw new Exception(
                "Payment cannot exceed the outstanding balance of "
                . number_format(
                    $current_balance,
                    2
                )
            );

        }


        /*
        |--------------------------------------------------------------------------
        | GENERATE RECEIPT
        |--------------------------------------------------------------------------
        */

        $receipt =
            "REC-"
            . date("YmdHis")
            . "-"
            . strtoupper(
                substr(
                    uniqid(),
                    -6
                )
            );


        /*
        |--------------------------------------------------------------------------
        | CURRENT ADMIN
        |--------------------------------------------------------------------------
        */

        $recorded_by =
            isset($_SESSION['user_id'])
                ? (int) $_SESSION['user_id']
                : null;


        /*
        |--------------------------------------------------------------------------
        | INSERT PAYMENT
        |--------------------------------------------------------------------------
        */

        $insert = mysqli_prepare(
            $conn,

            "INSERT INTO fee_payments
            (
                student_fee_id,
                receipt_number,
                amount,
                payment_date,
                payment_method,
                reference_number,
                notes,
                recorded_by
            )

            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );


        if (!$insert) {

            throw new Exception(
                "Could not prepare payment insertion: "
                . mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $insert,
            "isdssssi",
            $student_fee_id,
            $receipt,
            $amount,
            $payment_date,
            $method,
            $reference,
            $notes,
            $recorded_by
        );


        if (!mysqli_stmt_execute($insert)) {

            throw new Exception(
                "Payment could not be saved: "
                . mysqli_stmt_error($insert)
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RECALCULATE TOTAL PAID
        |--------------------------------------------------------------------------
        */

        $new_paid_stmt = mysqli_prepare(
            $conn,

            "SELECT
                COALESCE(SUM(amount), 0) AS total_paid

             FROM fee_payments

             WHERE student_fee_id = ?"
        );


        if (!$new_paid_stmt) {

            throw new Exception(
                "Could not recalculate payment total."
            );

        }


        mysqli_stmt_bind_param(
            $new_paid_stmt,
            "i",
            $student_fee_id
        );


        if (!mysqli_stmt_execute($new_paid_stmt)) {

            throw new Exception(
                "Could not recalculate payments: "
                . mysqli_stmt_error($new_paid_stmt)
            );

        }


        $new_paid_result =
            mysqli_stmt_get_result(
                $new_paid_stmt
            );

        $new_paid_row =
            mysqli_fetch_assoc(
                $new_paid_result
            );


        $new_total_paid =
            (float) (
                $new_paid_row['total_paid']
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | CALCULATE NEW BALANCE
        |--------------------------------------------------------------------------
        */

        $new_balance =
            $total_owed
            - $new_total_paid;


        $new_balance =
            round(
                $new_balance,
                2
            );


        if ($new_balance < 0) {
            $new_balance = 0;
        }


        /*
        |--------------------------------------------------------------------------
        | ACCOUNT STATUS
        |--------------------------------------------------------------------------
        */

        if ($new_balance <= 0) {

            $status = "Cleared";

        } elseif ($new_total_paid > 0) {

            $status = "Partial";

        } else {

            $status = "Pending";

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE STUDENT FEE ACCOUNT
        |--------------------------------------------------------------------------
        */

        $update = mysqli_prepare(
            $conn,

            "UPDATE student_fees

             SET
                amount_paid = ?,
                balance = ?,
                status = ?

             WHERE student_fee_id = ?"
        );


        if (!$update) {

            throw new Exception(
                "Could not prepare fee account update: "
                . mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $update,
            "ddsi",
            $new_total_paid,
            $new_balance,
            $status,
            $student_fee_id
        );


        if (!mysqli_stmt_execute($update)) {

            throw new Exception(
                "Fee account could not be updated: "
                . mysqli_stmt_error($update)
            );

        }


        /*
        |--------------------------------------------------------------------------
        | COMMIT
        |--------------------------------------------------------------------------
        */

        mysqli_commit($conn);


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        set_success(
            "Payment of "
            . number_format(
                $amount,
                2
            )
            . " received successfully. Receipt: "
            . $receipt
        );


        redirect(
            "fee_payments.php"
        );

    }

    catch (Exception $e) {

        mysqli_rollback($conn);


        set_error(
            $e->getMessage()
        );


        redirect(
            "receive_payment.php?student_fee_id="
            . $student_fee_id
        );

    }

}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

$message = get_success();
$error = get_error();


/*
|--------------------------------------------------------------------------
| PAGE VALUES
|--------------------------------------------------------------------------
*/

$display_class =
    $account['group_name']
    ?: $account['class'];

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>Receive Payment</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">


<style>

body {
    background: #f4f6f9;
}

.payment-wrapper {
    max-width: 800px;
    margin: 40px auto;
}

.payment-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(0,0,0,.10);
    overflow: hidden;
}

.payment-header {
    background: #198754;
    color: white;
    padding: 18px 24px;
}

.payment-header h4 {
    margin: 0;
    font-weight: 600;
}

.student-summary {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 18px;
}

.summary-label {
    font-size: 13px;
    color: #6c757d;
}

.summary-value {
    font-weight: 600;
}

.balance-box {
    background: #fff3cd;
    border: 1px solid #ffecb5;
    border-radius: 10px;
    padding: 18px;
}

.balance-value {
    font-size: 24px;
    font-weight: 700;
    color: #dc3545;
}

</style>

</head>


<body>


<div class="container-fluid">


<div class="payment-wrapper">


<div class="card payment-card">


<div class="payment-header">

<h4>
💰 Receive Payment
</h4>

</div>


<div class="card-body p-4">


<?php if ($message): ?>

<div class="alert alert-success">

<?= e($message); ?>

</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="alert alert-danger">

<?= e($error); ?>

</div>

<?php endif; ?>


<!--
|--------------------------------------------------------------------------
| STUDENT INFORMATION
|--------------------------------------------------------------------------
-->

<div class="student-summary mb-4">

<div class="row g-3">


<div class="col-md-6">

<div class="summary-label">
Student
</div>

<div class="summary-value">
<?= e($account['full_name']); ?>
</div>

</div>


<div class="col-md-6">

<div class="summary-label">
Registration Number
</div>

<div class="summary-value">
<?= e($account['reg_no']); ?>
</div>

</div>


<div class="col-md-6">

<div class="summary-label">
Class
</div>

<div class="summary-value">
<?= e($display_class); ?>

<?php if (!empty($account['stream'])): ?>

<span class="text-muted">
- <?= e($account['stream']); ?>
</span>

<?php endif; ?>

</div>

</div>


<div class="col-md-6">

<div class="summary-label">
Academic Period
</div>

<div class="summary-value">

<?= e(
    $account['academic_year']
    . " - "
    . $account['period_name']
); ?>

</div>

</div>


</div>

</div>


<!--
|--------------------------------------------------------------------------
| FEE SUMMARY
|--------------------------------------------------------------------------
-->

<div class="row g-3 mb-4">


<div class="col-md-4">

<div class="card border">

<div class="card-body">

<div class="text-muted small">
Total Fees
</div>

<div class="fw-bold fs-5">

<?= number_format(
    $total_owed,
    2
); ?>

</div>

</div>

</div>

</div>


<div class="col-md-4">

<div class="card border">

<div class="card-body">

<div class="text-muted small">
Total Paid
</div>

<div class="fw-bold fs-5 text-success">

<?= number_format(
    $total_paid_before,
    2
); ?>

</div>

</div>

</div>

</div>


<div class="col-md-4">

<div class="balance-box">

<div class="text-muted small">
Outstanding Balance
</div>

<div class="balance-value">

<?= number_format(
    $current_balance,
    2
); ?>

</div>

</div>

</div>


</div>


<?php if ($current_balance > 0): ?>


<!--
|--------------------------------------------------------------------------
| PAYMENT FORM
|--------------------------------------------------------------------------
-->

<form method="POST">


<div class="mb-3">

<label class="form-label">

Amount Paid

<span class="text-danger">*</span>

</label>


<input
    type="number"
    step="0.01"
    min="0.01"
    max="<?= e(
        number_format(
            $current_balance,
            2,
            '.',
            ''
        )
    ); ?>"
    name="amount"
    class="form-control form-control-lg"
    placeholder="Enter amount"
    required>


<small class="text-muted">

Maximum payment:

<?= number_format(
    $current_balance,
    2
); ?>

</small>

</div>


<div class="mb-3">

<label class="form-label">

Payment Method

<span class="text-danger">*</span>

</label>


<select
    name="payment_method"
    class="form-select form-select-lg"
    required>


<option value="">

Select payment method

</option>


<option value="Cash">

Cash

</option>


<option value="Bank">

Bank

</option>


<option value="Mobile Money">

Mobile Money

</option>


<option value="SchoolPay">

SchoolPay

</option>


</select>

</div>


<div class="mb-3">

<label class="form-label">

Payment Date

<span class="text-danger">*</span>

</label>


<input
    type="date"
    name="payment_date"
    class="form-control form-control-lg"
    value="<?= e(date('Y-m-d')); ?>"
    required>

</div>


<div class="mb-3">

<label class="form-label">

Reference Number

</label>


<input
    type="text"
    name="reference_number"
    class="form-control"
    placeholder="e.g. Mobile Money / Bank reference">

</div>


<div class="mb-4">

<label class="form-label">

Notes

</label>


<textarea
    name="notes"
    class="form-control"
    rows="3"
    placeholder="Optional notes about this payment"></textarea>

</div>


<div class="d-flex gap-2">


<a
    href="fee_payments.php"
    class="btn btn-secondary">

Cancel

</a>


<button
    type="submit"
    name="pay"
    class="btn btn-success flex-grow-1">

💰 Receive Payment

</button>


</div>


</form>


<?php else: ?>


<div class="alert alert-success">

<strong>✓ Fully Paid</strong>

<br>

This student's fee account has no outstanding balance.

</div>


<a
    href="fee_payments.php"
    class="btn btn-secondary">

← Back to Fee Payments

</a>


<?php endif; ?>


</div>

</div>

</div>

</div>


</body>

</html>