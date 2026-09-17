<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| SAVE PAYMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST['pay'])) {

    $student_fee_id = (int) ($_POST['student_fee_id'] ?? 0);

    $amount = (float) ($_POST['amount'] ?? 0);

    $method = trim($_POST['payment_method'] ?? '');

    $reference = trim($_POST['reference_number'] ?? '');

    $payment_date = trim($_POST['payment_date'] ?? date('Y-m-d'));

    $notes = trim($_POST['notes'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($student_fee_id <= 0) {

        set_error("Invalid student fee account.");

        redirect("fee_payments.php");
    }


    if ($amount <= 0) {

        set_error("Enter a valid payment amount.");

        redirect("fee_payments.php");
    }


    if ($method === '') {

        set_error("Please select a payment method.");

        redirect("fee_payments.php");
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE PAYMENT DATE
    |--------------------------------------------------------------------------
    */

    $date_object = DateTime::createFromFormat(
        'Y-m-d',
        $payment_date
    );

    if (
        !$date_object ||
        $date_object->format('Y-m-d') !== $payment_date
    ) {

        set_error("Invalid payment date.");

        redirect("fee_payments.php");
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
        | GET THE FEE ACCOUNT
        |--------------------------------------------------------------------------
        |
        | The payment MUST belong to an existing fee account.
        |
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


        $account =
            mysqli_fetch_assoc($account_result);


        if (!$account) {

            throw new Exception(
                "Student fee account not found."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GET ALL PREVIOUS PAYMENTS
        |--------------------------------------------------------------------------
        |
        | fee_payments is the transaction history.
        |
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
        | CALCULATE AMOUNT OWED
        |--------------------------------------------------------------------------
        */

        $total_amount =
            (float) ($account['total_amount'] ?? 0);


        $previous_balance =
            (float) ($account['previous_balance'] ?? 0);


        $total_owed =
            $total_amount + $previous_balance;


        $total_owed =
            round($total_owed, 2);


        /*
        |--------------------------------------------------------------------------
        | CURRENT BALANCE
        |--------------------------------------------------------------------------
        */

        $current_balance =
            $total_owed - $total_paid_before;


        $current_balance =
            round($current_balance, 2);


        if ($current_balance < 0) {

            $current_balance = 0;
        }


        /*
        |--------------------------------------------------------------------------
        | PREVENT PAYMENT AFTER FULL CLEARANCE
        |--------------------------------------------------------------------------
        */

        if ($current_balance <= 0) {

            throw new Exception(
                "This student's fee account is already fully paid."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PREVENT OVERPAYMENT
        |--------------------------------------------------------------------------
        */

        if ($amount > $current_balance) {

            throw new Exception(
                "Payment cannot exceed the outstanding balance of "
                . number_format($current_balance, 2)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GENERATE UNIQUE RECEIPT
        |--------------------------------------------------------------------------
        */

        $receipt =
            "REC-"
            . date("YmdHis")
            . "-"
            . strtoupper(substr(uniqid(), -6));


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
        | INSERT PAYMENT TRANSACTION
        |--------------------------------------------------------------------------
        |
        | THIS is where the bursar's payment enters the system.
        |
        | The payment is linked to the exact fee account through
        | student_fee_id.
        |
        */

        $stmt = mysqli_prepare(
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


        if (!$stmt) {

            throw new Exception(
                "Could not prepare payment insertion: "
                . mysqli_error($conn)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
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


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Payment could not be saved: "
                . mysqli_stmt_error($stmt)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | RECALCULATE TOTAL PAID
        |--------------------------------------------------------------------------
        |
        | We NEVER trust a manually entered amount_paid.
        |
        | Instead:
        |
        | total paid = SUM(all payments for this fee account)
        |
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
            mysqli_stmt_get_result($new_paid_stmt);


        $new_paid_row =
            mysqli_fetch_assoc($new_paid_result);


        $new_total_paid =
            (float) ($new_paid_row['total_paid'] ?? 0);


        /*
        |--------------------------------------------------------------------------
        | CALCULATE NEW BALANCE
        |--------------------------------------------------------------------------
        */

        $new_balance =
            $total_owed - $new_total_paid;


        $new_balance =
            round($new_balance, 2);


        if ($new_balance < 0) {

            $new_balance = 0;
        }


        /*
        |--------------------------------------------------------------------------
        | DETERMINE ACCOUNT STATUS
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
        | UPDATE FEE ACCOUNT
        |--------------------------------------------------------------------------
        |
        | The account is updated AFTER the payment transaction has been
        | successfully inserted.
        |
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
        | COMMIT EVERYTHING
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
            . number_format($amount, 2)
            . " received successfully. Receipt: "
            . $receipt
        );


        redirect("fee_payments.php");
    }


    catch (Exception $e) {

        mysqli_rollback($conn);

        set_error(
            $e->getMessage()
        );

        redirect("fee_payments.php");
    }
}


/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$message = get_success();

$error = get_error();


/*
|--------------------------------------------------------------------------
| SEARCH FILTERS
|--------------------------------------------------------------------------
*/

$search =
    trim($_GET['search'] ?? '');


$group =
    $_GET['group_id'] ?? '';


$period =
    $_GET['period_id'] ?? '';


$where = [];


/*
|--------------------------------------------------------------------------
| SEARCH STUDENT
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $search_safe =
        mysqli_real_escape_string(
            $conn,
            $search
        );


    $where[] = "
        (
            students.full_name LIKE '%$search_safe%'
            OR students.reg_no LIKE '%$search_safe%'
        )
    ";
}


/*
|--------------------------------------------------------------------------
| GROUP FILTER
|--------------------------------------------------------------------------
*/

if ($group !== '') {

    $where[] =
        "students.group_id = "
        . (int) $group;
}


/*
|--------------------------------------------------------------------------
| PERIOD FILTER
|--------------------------------------------------------------------------
*/

if ($period !== '') {

    $where[] =
        "student_fees.period_id = "
        . (int) $period;
}


/*
|--------------------------------------------------------------------------
| WHERE
|--------------------------------------------------------------------------
*/

$where_sql = "";


if (!empty($where)) {

    $where_sql =
        "WHERE "
        . implode(
            " AND ",
            $where
        );
}


/*
|--------------------------------------------------------------------------
| LOAD FEE ACCOUNTS
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| We start from student_fees.
|
| Therefore a student appears here only when a fee account exists.
|
*/

$result = mysqli_query(
    $conn,

    "SELECT

        student_fees.student_fee_id,
        student_fees.student_id,
        student_fees.period_id,
        student_fees.total_amount,
        student_fees.previous_balance,

        COALESCE(
            payments.total_paid,
            0
        ) AS calculated_paid,

        GREATEST(
            (
                student_fees.total_amount
                +
                student_fees.previous_balance
                -
                COALESCE(payments.total_paid, 0)
            ),
            0
        ) AS calculated_balance,

        students.full_name,
        students.reg_no,
        students.class,
        students.stream,

        academic_groups.group_name,

        academic_periods.academic_year,
        academic_periods.period_name

    FROM student_fees

    INNER JOIN students
        ON student_fees.student_id =
           students.student_id

    LEFT JOIN academic_groups
        ON students.group_id =
           academic_groups.group_id

    INNER JOIN academic_periods
        ON student_fees.period_id =
           academic_periods.period_id

    LEFT JOIN (

        SELECT

            student_fee_id,

            SUM(amount) AS total_paid

        FROM fee_payments

        GROUP BY student_fee_id

    ) AS payments

        ON payments.student_fee_id =
           student_fees.student_fee_id

    $where_sql

    ORDER BY
        student_fees.student_fee_id DESC"
);


if (!$result) {

    die(
        "Failed to load fee accounts: "
        . mysqli_error($conn)
    );
}


/*
|--------------------------------------------------------------------------
| LOAD GROUPS
|--------------------------------------------------------------------------
*/

$groups = mysqli_query(
    $conn,

    "SELECT
        group_id,
        group_name
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name"
);


if (!$groups) {

    die(
        "Failed to load groups: "
        . mysqli_error($conn)
    );
}


/*
|--------------------------------------------------------------------------
| LOAD PERIODS
|--------------------------------------------------------------------------
*/

$periods = mysqli_query(
    $conn,

    "SELECT
        period_id,
        academic_year,
        period_name
     FROM academic_periods
     ORDER BY period_id DESC"
);


if (!$periods) {

    die(
        "Failed to load academic periods: "
        . mysqli_error($conn)
    );
}

?>


<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Fee Payments</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<style>

body {
    background: #f4f6f9;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,.08);
}

.table {
    background: white;
}

.badge {
    font-size: 13px;
}

.payment-box {
    min-width: 280px;
}

.account-header {
    font-weight: 600;
}

.balance-amount {
    font-size: 17px;
}

</style>

</head>


<body>


<div class="container-fluid p-4">


<!--
|--------------------------------------------------------------------------
| PAGE HEADER
|--------------------------------------------------------------------------
-->

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="mb-1">
💵 Fee Payments
</h2>

<p class="text-muted mb-0">
Receive and manage student fee payments.
</p>

</div>

</div>


<!-- SUCCESS -->

<?php if ($message): ?>

<div class="alert alert-success">

<?= e($message); ?>

</div>

<?php endif; ?>


<!-- ERROR -->

<?php if ($error): ?>

<div class="alert alert-danger">

<?= e($error); ?>

</div>

<?php endif; ?>


<!--
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
-->

<div class="card mb-4">

<div class="card-body">

<form method="GET">

<div class="row g-2">


<div class="col-md-4">

<label class="form-label">
Student
</label>

<input
type="text"
class="form-control"
name="search"
placeholder="Search name or registration number..."
value="<?= e($search); ?>">

</div>


<div class="col-md-3">

<label class="form-label">
Class / Group
</label>

<select
name="group_id"
class="form-select">

<option value="">
All Classes / Groups
</option>


<?php while ($g = mysqli_fetch_assoc($groups)): ?>

<option
value="<?= e($g['group_id']); ?>"
<?= ((string)$group === (string)$g['group_id'])
    ? 'selected'
    : ''; ?>>

<?= e($g['group_name']); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="col-md-3">

<label class="form-label">
Academic Period
</label>

<select
name="period_id"
class="form-select">

<option value="">
All Academic Periods
</option>


<?php while ($p = mysqli_fetch_assoc($periods)): ?>

<option
value="<?= e($p['period_id']); ?>"
<?= ((string)$period === (string)$p['period_id'])
    ? 'selected'
    : ''; ?>>

<?= e(
    $p['academic_year']
    . " - "
    . $p['period_name']
); ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="col-md-2 d-flex align-items-end">

<button
type="submit"
class="btn btn-primary w-100">

🔍 Search

</button>

</div>


</div>

</form>

</div>

</div>


<!--
|--------------------------------------------------------------------------
| FEE ACCOUNTS
|--------------------------------------------------------------------------
-->

<div class="card">

<div class="card-header bg-dark text-white">

<strong>
Student Fee Accounts
</strong>

</div>


<div class="card-body">


<?php if (mysqli_num_rows($result) === 0): ?>


<div class="alert alert-warning mb-0">

<strong>No fee account found.</strong>

<br>

<?php if ($search !== ''): ?>

No fee account exists for
<strong><?= e($search); ?></strong>
under the selected filters.

<br><br>

The student must first have a
<strong>fee account</strong>
created for the relevant academic period before a payment can be received.

<?php else: ?>

No student fee accounts match the selected filters.

<?php endif; ?>

</div>


<?php else: ?>


<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">


<thead class="table-dark">

<tr>

<th>Student</th>

<th>Class</th>

<th>Period</th>

<th>Total Fees</th>

<th>Previous Balance</th>

<th>Total Paid</th>

<th>Balance</th>

<th>Status</th>

<th style="min-width:320px;">
Receive Payment
</th>

</tr>

</thead>


<tbody>


<?php while ($row = mysqli_fetch_assoc($result)): ?>


<?php

/*
|--------------------------------------------------------------------------
| CALCULATE DISPLAY VALUES
|--------------------------------------------------------------------------
*/

$total_owed =
    (float) $row['total_amount']
    +
    (float) $row['previous_balance'];


$paid =
    (float) $row['calculated_paid'];


$balance =
    $total_owed - $paid;


$balance =
    round($balance, 2);


if ($balance < 0) {

    $balance = 0;
}


if ($paid <= 0) {

    $display_status = "Pending";

} elseif ($balance <= 0) {

    $display_status = "Cleared";

} else {

    $display_status = "Partial";

}


/*
|--------------------------------------------------------------------------
| UNIQUE MODAL ID
|--------------------------------------------------------------------------
*/

$modal_id =
    "paymentModal"
    . (int) $row['student_fee_id'];

?>


<tr>


<!--
|--------------------------------------------------------------------------
| STUDENT
|--------------------------------------------------------------------------
-->

<td>

<strong class="account-header">

<?= e($row['full_name']); ?>

</strong>

<br>

<small class="text-muted">

<?= e($row['reg_no']); ?>

</small>

</td>


<!--
|--------------------------------------------------------------------------
| CLASS
|--------------------------------------------------------------------------
-->

<td>

<?= e(
    $row['group_name']
    ?: $row['class']
); ?>


<?php if (!empty($row['stream'])): ?>

<br>

<small class="text-muted">

Stream:
<?= e($row['stream']); ?>

</small>

<?php endif; ?>

</td>


<!--
|--------------------------------------------------------------------------
| PERIOD
|--------------------------------------------------------------------------
-->

<td>

<?= e(
    $row['academic_year']
    . " - "
    . $row['period_name']
); ?>

</td>


<!--
|--------------------------------------------------------------------------
| TOTAL FEES
|--------------------------------------------------------------------------
-->

<td>

<strong>

<?= number_format(
    $total_owed,
    2
); ?>

</strong>

</td>


<!--
|--------------------------------------------------------------------------
| PREVIOUS BALANCE
|--------------------------------------------------------------------------
-->

<td>

<?= number_format(
    (float)$row['previous_balance'],
    2
); ?>

</td>


<!--
|--------------------------------------------------------------------------
| TOTAL PAID
|--------------------------------------------------------------------------
-->

<td class="text-success">

<strong>

<?= number_format(
    $paid,
    2
); ?>

</strong>

</td>


<!--
|--------------------------------------------------------------------------
| BALANCE
|--------------------------------------------------------------------------
-->

<td>

<strong
class="<?= $balance > 0
    ? 'text-danger'
    : 'text-success'; ?> balance-amount">

<?= number_format(
    $balance,
    2
); ?>

</strong>

</td>


<!--
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
-->

<td>


<?php if ($display_status === 'Cleared'): ?>

<span class="badge bg-success">
Cleared
</span>


<?php elseif ($display_status === 'Partial'): ?>

<span class="badge bg-warning text-dark">
Partial
</span>


<?php else: ?>

<span class="badge bg-danger">
Pending
</span>

<?php endif; ?>


</td>


<!--
|--------------------------------------------------------------------------
| RECEIVE PAYMENT
|--------------------------------------------------------------------------
-->

<td class="payment-box">


<?php if ($balance > 0): ?>


<a
    href="receive_payment.php?student_fee_id=<?= e($row['student_fee_id']); ?>"
    class="btn btn-success btn-sm w-100">

    💰 Receive Payment

</a>


<!--
|--------------------------------------------------------------------------
| PAYMENT MODAL
|--------------------------------------------------------------------------
-->

<div
class="modal fade"
id="<?= e($modal_id); ?>"
tabindex="-1"
aria-hidden="true">


<div class="modal-dialog modal-dialog-centered">


<div class="modal-content">


<div class="modal-header bg-success text-white">

<h5 class="modal-title">

Receive Payment

</h5>

<button
type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">
</button>

</div>


<div class="modal-body">


<div class="alert alert-light border">

<strong>
<?= e($row['full_name']); ?>
</strong>

<br>

<small>
Reg No:
<?= e($row['reg_no']); ?>
</small>

<br>

<small>
Period:
<?= e(
    $row['academic_year']
    . " - "
    . $row['period_name']
); ?>
</small>

<hr>

<div class="row">

<div class="col-6">

<small class="text-muted">
Total Fees
</small>

<br>

<strong>
<?= number_format($total_owed, 2); ?>
</strong>

</div>


<div class="col-6">

<small class="text-muted">
Outstanding
</small>

<br>

<strong class="text-danger">

<?= number_format($balance, 2); ?>

</strong>

</div>

</div>

</div>


<form method="POST">


<!--
|--------------------------------------------------------------------------
| VERY IMPORTANT
|--------------------------------------------------------------------------
|
| This ID identifies the exact fee account receiving the payment.
|
-->

<input
type="hidden"
name="student_fee_id"
value="<?= e($row['student_fee_id']); ?>">


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
        $balance,
        2,
        '.',
        ''
    )
); ?>"
name="amount"
class="form-control"
placeholder="Enter amount"
required>


<small class="text-muted">

Maximum payment:
<?= number_format($balance, 2); ?>

</small>

</div>


<div class="mb-3">

<label class="form-label">

Payment Method
<span class="text-danger">*</span>

</label>


<select
name="payment_method"
class="form-select"
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
class="form-control"
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


<div class="mb-3">

<label class="form-label">

Notes

</label>


<textarea
name="notes"
class="form-control"
rows="3"
placeholder="Optional notes about this payment"></textarea>

</div>


<div class="d-grid">

<button
type="submit"
name="pay"
class="btn btn-success">

💰 Receive Payment

</button>

</div>


</form>


</div>

</div>

</div>

</div>


<?php else: ?>


<div class="text-success fw-bold mb-2">

✓ Fully Paid

</div>


<?php endif; ?>


<!--
|--------------------------------------------------------------------------
| LATEST RECEIPT
|--------------------------------------------------------------------------
-->

<?php

$receipt_stmt = mysqli_prepare(
    $conn,

    "SELECT payment_id
     FROM fee_payments
     WHERE student_fee_id = ?
     ORDER BY payment_id DESC
     LIMIT 1"
);


if ($receipt_stmt) {

    mysqli_stmt_bind_param(
        $receipt_stmt,
        "i",
        $row['student_fee_id']
    );


    mysqli_stmt_execute(
        $receipt_stmt
    );


    $receipt_result =
        mysqli_stmt_get_result(
            $receipt_stmt
        );


    $latest_payment =
        mysqli_fetch_assoc(
            $receipt_result
        );

} else {

    $latest_payment = null;
}

?>


<?php if ($latest_payment): ?>

<a
href="payment_receipt.php?id=<?= e($latest_payment['payment_id']); ?>"
class="btn btn-dark btn-sm mt-2 w-100">

🧾 View Latest Receipt

</a>

<?php endif; ?>


</td>


</tr>


<?php endwhile; ?>


</tbody>

</table>

</div>


<?php endif; ?>


</div>

</div>


</div>


<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
