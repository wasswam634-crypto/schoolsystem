<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Fees Management";

include '../includes/header.php';
include '../includes/navbar.php';

$message = "";

$message = "";
$message_type = "success";

if(isset($_POST['save_fee'])){

    $student_id     = (int)$_POST['student_id'];
    $amount_due     = (float)$_POST['amount_due'];
    $amount_paid    = (float)$_POST['amount_paid'];
    $term           = trim($_POST['term']);
    $academic_year  = trim($_POST['academic_year']);
    $payment_date   = $_POST['payment_date'];

    if($amount_paid <= 0){

        $message = "Amount paid must be greater than zero.";
        $message_type = "danger";

    }else{

        // Check whether this student already has a fee record
        $check = mysqli_prepare(
            $conn,
            "SELECT *
             FROM fees
             WHERE student_id=?
             AND term=?
             AND academic_year=?"
        );

        mysqli_stmt_bind_param(
            $check,
            "iss",
            $student_id,
            $term,
            $academic_year
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if(mysqli_num_rows($result) > 0){

            // Existing record
            $fee = mysqli_fetch_assoc($result);

            $new_paid = $fee['amount_paid'] + $amount_paid;

            if($new_paid > $fee['amount_due']){

                $message = "Payment exceeds the required amount.";
                $message_type = "danger";

            }else{

                $balance = $fee['amount_due'] - $new_paid;

                $update = mysqli_prepare(
                    $conn,
                    "UPDATE fees
                     SET amount_paid=?,
                         balance=?,
                         payment_date=?
                     WHERE fee_id=?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "ddsi",
                    $new_paid,
                    $balance,
                    $payment_date,
                    $fee['fee_id']
                );

                if(mysqli_stmt_execute($update)){

                    log_activity(
                        $conn,
                        $_SESSION['user_id'],
                        "Updated fee payment for student ID ".$student_id
                    );

                    $message = "Payment updated successfully.";

                }else{

                    $message = "Failed to update payment.";
                    $message_type = "danger";

                }

            }

        }else{

            // First payment

            if($amount_paid > $amount_due){

                $message = "Amount paid cannot exceed amount due.";
                $message_type = "danger";

            }else{

                $balance = $amount_due - $amount_paid;

                $insert = mysqli_prepare(
                    $conn,
                    "INSERT INTO fees
                    (
                        student_id,
                        amount_due,
                        amount_paid,
                        balance,
                        term,
                        academic_year,
                        payment_date
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $insert,
                    "idddsss",
                    $student_id,
                    $amount_due,
                    $amount_paid,
                    $balance,
                    $term,
                    $academic_year,
                    $payment_date
                );

                if(mysqli_stmt_execute($insert)){

                    log_activity(
                        $conn,
                        $_SESSION['user_id'],
                        "Recorded first fee payment for student ID ".$student_id
                    );

                    $message = "Fee record saved successfully.";

                }else{

                    $message = "Failed to save fee record.";
                    $message_type = "danger";

                }

            }

        }

    }

}
?>

<div class="container-fluid">

<div class="row">

<?php include '../includes/admin_sidebar.php'; ?>

<div class="col-md-10 p-4">

<h2 class="mb-4">
    Fees Management
</h2>

<?php if($message): ?>

<div class="alert alert-<?= $message_type; ?>">

    <?= e($message); ?>
</div>

<?php endif; ?>

<div class="card shadow">

<div class="card-header bg-primary text-white">
    Record Fee Payment
</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label fw-bold">
Student
</label>

<select
name="student_id"
class="form-control"
required>

<option value="">
-- Select Student --
</option>

<?php

$students = mysqli_query(
    $conn,
    "SELECT student_id,
            full_name,
            reg_no
     FROM students
     ORDER BY full_name"
);

while($student=mysqli_fetch_assoc($students)){
?>

<option value="<?= $student['student_id']; ?>">

<?= e($student['full_name']); ?>

(<?= e($student['reg_no']); ?>)

</option>

<?php } ?>

</select>

</div>


<div class="col-md-3 mb-3">

<label class="form-label fw-bold">
Term
</label>

<select
name="term"
class="form-control"
required>

<option value="Term 1">Term 1</option>

<option value="Term 2">Term 2</option>

<option value="Term 3">Term 3</option>

</select>

</div>


<div class="col-md-3 mb-3">

<label class="form-label fw-bold">
Academic Year
</label>

<input
type="text"
name="academic_year"
class="form-control"
value="<?= date('Y'); ?>"
required>

</div>


<div class="col-md-4 mb-3">

<label class="form-label fw-bold">
Total Fees
</label>

<input
type="number"
step="0.01"
name="amount_due"
class="form-control"
placeholder="Enter total school fees"
required>

<small class="text-muted">

Only required on the student's first payment.

</small>

</div>


<div class="col-md-4 mb-3">

<label class="form-label fw-bold">
Payment Amount
</label>

<input
type="number"
step="0.01"
name="amount_paid"
class="form-control"
placeholder="Amount being paid today"
required>

</div>


<div class="col-md-4 mb-3">

<label class="form-label fw-bold">
Payment Date
</label>

<input
type="date"
name="payment_date"
class="form-control"
value="<?= date('Y-m-d'); ?>"
required>

</div>

</div>


<div class="text-end">

<button
class="btn btn-success"
name="save_fee">

<i class="fas fa-money-bill-wave"></i>

Save Payment

</button>

</div>

</form>

</div>


</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>