<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = "";
$message_type = "success";


/*
|--------------------------------------------------------------------------
| GENERATE STUDENT FEE ACCOUNTS
|--------------------------------------------------------------------------
*/

if (isset($_POST['generate'])) {

    $period_id = (int) ($_POST['period_id'] ?? 0);
    $group_id  = (int) ($_POST['group_id'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | VALIDATE
    |--------------------------------------------------------------------------
    */

    if ($period_id <= 0 || $group_id <= 0) {

        $message = "Please select an academic period and class.";
        $message_type = "danger";

    } else {

        mysqli_begin_transaction($conn);

        try {

            /*
            |--------------------------------------------------------------------------
            | GET FEE STRUCTURE TOTAL
            |--------------------------------------------------------------------------
            */

            $fee_query = mysqli_prepare(
                $conn,

                "SELECT COALESCE(SUM(amount), 0) AS total
                 FROM fee_structure
                 WHERE period_id = ?
                 AND group_id = ?"
            );

            if (!$fee_query) {
                throw new Exception(
                    "Could not prepare fee structure query: "
                    . mysqli_error($conn)
                );
            }

            mysqli_stmt_bind_param(
                $fee_query,
                "ii",
                $period_id,
                $group_id
            );

            mysqli_stmt_execute($fee_query);

            $fee_result = mysqli_stmt_get_result($fee_query);

            $fee = mysqli_fetch_assoc($fee_result);

            mysqli_stmt_close($fee_query);


            $total_amount = (float) ($fee['total'] ?? 0);


            /*
            |--------------------------------------------------------------------------
            | MAKE SURE FEE STRUCTURE EXISTS
            |--------------------------------------------------------------------------
            */

            if ($total_amount <= 0) {

                throw new Exception(
                    "No fee structure has been created for this class and academic period."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | GET ACTIVE STUDENTS
            |--------------------------------------------------------------------------
            */

            $students_stmt = mysqli_prepare(
                $conn,

                "SELECT
                    student_id,
                    full_name,
                    reg_no
                 FROM students
                 WHERE group_id = ?
                 AND status = 'Active'
                 ORDER BY full_name ASC"
            );

            if (!$students_stmt) {

                throw new Exception(
                    "Could not prepare students query: "
                    . mysqli_error($conn)
                );
            }


            mysqli_stmt_bind_param(
                $students_stmt,
                "i",
                $group_id
            );


            mysqli_stmt_execute($students_stmt);


            $students_result =
                mysqli_stmt_get_result($students_stmt);


            /*
            |--------------------------------------------------------------------------
            | COUNTERS
            |--------------------------------------------------------------------------
            */

            $created = 0;
            $existing = 0;
            $failed = 0;


            /*
            |--------------------------------------------------------------------------
            | PROCESS EACH STUDENT
            |--------------------------------------------------------------------------
            */

            while ($student = mysqli_fetch_assoc($students_result)) {

                $student_id =
                    (int) $student['student_id'];


                /*
                |--------------------------------------------------------------------------
                | CHECK WHETHER ACCOUNT ALREADY EXISTS
                |--------------------------------------------------------------------------
                */

                $check = mysqli_prepare(
                    $conn,

                    "SELECT
                        student_fee_id
                     FROM student_fees
                     WHERE student_id = ?
                     AND period_id = ?
                     LIMIT 1"
                );


                if (!$check) {

                    throw new Exception(
                        "Could not check existing student fee account."
                    );
                }


                mysqli_stmt_bind_param(
                    $check,
                    "ii",
                    $student_id,
                    $period_id
                );


                mysqli_stmt_execute($check);


                $check_result =
                    mysqli_stmt_get_result($check);


                $existing_account =
                    mysqli_fetch_assoc($check_result);


                mysqli_stmt_close($check);


                /*
                |--------------------------------------------------------------------------
                | DO NOT CREATE DUPLICATE ACCOUNT
                |--------------------------------------------------------------------------
                */

                if ($existing_account) {

                    $existing++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | FIND PREVIOUS UNPAID BALANCE
                |--------------------------------------------------------------------------
                |
                | We look at the student's previous fee accounts.
                |
                | The most recent previous account is used.
                |
                */

                $previous_stmt = mysqli_prepare(
                    $conn,

                    "SELECT
                        sf.student_fee_id,
                        sf.total_amount,
                        sf.previous_balance,

                        COALESCE(
                            (
                                SELECT SUM(fp.amount)
                                FROM fee_payments fp
                                WHERE fp.student_fee_id =
                                      sf.student_fee_id
                            ),
                            0
                        ) AS payments_made

                     FROM student_fees sf

                     INNER JOIN academic_periods ap
                        ON sf.period_id = ap.period_id

                     WHERE sf.student_id = ?
                     AND sf.period_id < ?

                     ORDER BY sf.period_id DESC

                     LIMIT 1"
                );


                if (!$previous_stmt) {

                    throw new Exception(
                        "Could not prepare previous balance query."
                    );
                }


                mysqli_stmt_bind_param(
                    $previous_stmt,
                    "ii",
                    $student_id,
                    $period_id
                );


                mysqli_stmt_execute($previous_stmt);


                $previous_result =
                    mysqli_stmt_get_result($previous_stmt);


                $previous_account =
                    mysqli_fetch_assoc($previous_result);


                mysqli_stmt_close($previous_stmt);


                /*
                |--------------------------------------------------------------------------
                | CALCULATE PREVIOUS BALANCE
                |--------------------------------------------------------------------------
                */

                $previous_balance = 0;


                if ($previous_account) {

                    $old_total =
                        (float) $previous_account['total_amount'];

                    $old_previous =
                        (float) $previous_account['previous_balance'];

                    $old_paid =
                        (float) $previous_account['payments_made'];


                    /*
                    Previous account total obligation
                    */

                    $old_total_owed =
                        $old_total + $old_previous;


                    /*
                    Remaining unpaid amount
                    */

                    $previous_balance =
                        $old_total_owed - $old_paid;


                    /*
                    Prevent negative balance
                    */

                    if ($previous_balance < 0) {

                        $previous_balance = 0;
                    }


                    $previous_balance =
                        round($previous_balance, 2);
                }


                /*
                |--------------------------------------------------------------------------
                | NEW ACCOUNT VALUES
                |--------------------------------------------------------------------------
                */

                $amount_paid = 0;


                /*
                New balance =
                New fees + previous unpaid balance
                */

                $balance =
                    $total_amount + $previous_balance;


                $balance =
                    round($balance, 2);


                $status = "Pending";


                /*
                |--------------------------------------------------------------------------
                | CREATE STUDENT FEE ACCOUNT
                |--------------------------------------------------------------------------
                */

                $insert = mysqli_prepare(
                    $conn,

                    "INSERT INTO student_fees
                    (
                        student_id,
                        period_id,
                        total_amount,
                        previous_balance,
                        amount_paid,
                        balance,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );


                if (!$insert) {

                    throw new Exception(
                        "Could not prepare student fee account insertion."
                    );
                }


                mysqli_stmt_bind_param(
                    $insert,
                    "iidddds",

                    $student_id,
                    $period_id,
                    $total_amount,
                    $previous_balance,
                    $amount_paid,
                    $balance,
                    $status
                );


                if (!mysqli_stmt_execute($insert)) {

                    mysqli_stmt_close($insert);

                    throw new Exception(
                        "Failed to create fee account for "
                        . $student['full_name']
                        . " ("
                        . $student['reg_no']
                        . "): "
                        . mysqli_error($conn)
                    );
                }


                mysqli_stmt_close($insert);


                $created++;
            }


            mysqli_stmt_close($students_stmt);


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);


            /*
            |--------------------------------------------------------------------------
            | RESULT MESSAGE
            |--------------------------------------------------------------------------
            */

            $message =
                "Fee accounts generated successfully. "
                . $created
                . " account(s) created and "
                . $existing
                . " account(s) already existed.";

        } catch (Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK
            |--------------------------------------------------------------------------
            */

            mysqli_rollback($conn);


            $message = $e->getMessage();

            $message_type = "danger";
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET ACTIVE ACADEMIC PERIODS
|--------------------------------------------------------------------------
*/

$periods = mysqli_query(
    $conn,

    "SELECT
        period_id,
        academic_year,
        period_name,
        status
     FROM academic_periods
     WHERE status = 'Active'
     ORDER BY period_id DESC"
);


if (!$periods) {

    die(
        "Failed to load academic periods: "
        . mysqli_error($conn)
    );
}


/*
|--------------------------------------------------------------------------
| GET ACTIVE GROUPS
|--------------------------------------------------------------------------
*/

$groups = mysqli_query(
    $conn,

    "SELECT
        group_id,
        group_name,
        status
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name ASC"
);


if (!$groups) {

    die(
        "Failed to load classes: "
        . mysqli_error($conn)
    );
}

?>

<!DOCTYPE html>

<html>

<head>

<title>
Generate Student Fees
</title>


<meta name="viewport" content="width=device-width, initial-scale=1">


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container-fluid p-4">


<div class="d-flex justify-content-between align-items-center mb-4">

<h2>
🧾 Generate Student Fees
</h2>

</div>


<?php if ($message): ?>

<div class="alert alert-<?= e($message_type); ?>">

<?= e($message); ?>

</div>

<?php endif; ?>


<div class="card shadow-sm">


<div class="card-header bg-success text-white">

<h5 class="mb-0">
Generate Student Fee Accounts
</h5>

</div>


<div class="card-body">


<p class="text-muted">

Select an academic period and class. The system will create
fee accounts for all active students in that class.

Existing accounts will not be duplicated.

If a student has an unpaid balance from the previous academic
period, it will automatically be carried forward.

</p>


<form method="POST">


<div class="row g-3">


<!-- PERIOD -->

<div class="col-md-5">


<label class="form-label">

Academic Period

</label>


<select
name="period_id"
class="form-select"
required>


<option value="">

Select Academic Period

</option>


<?php while ($p = mysqli_fetch_assoc($periods)): ?>


<option
value="<?= (int) $p['period_id']; ?>">

<?= e($p['academic_year']); ?>

-

<?= e($p['period_name']); ?>


</option>


<?php endwhile; ?>


</select>


</div>


<!-- CLASS -->

<div class="col-md-5">


<label class="form-label">

Class / Group

</label>


<select
name="group_id"
class="form-select"
required>


<option value="">

Select Class

</option>


<?php while ($g = mysqli_fetch_assoc($groups)): ?>


<option
value="<?= (int) $g['group_id']; ?>">

<?= e($g['group_name']); ?>

</option>


<?php endwhile; ?>


</select>


</div>


<!-- BUTTON -->

<div class="col-md-2 d-flex align-items-end">


<button
type="submit"
name="generate"
class="btn btn-primary w-100"
onclick="return confirm(
'Generate fee accounts for all active students in this class?'
);">

Generate

</button>


</div>


</div>


</form>


</div>


</div>


<div class="card mt-4">


<div class="card-body">


<h5>
How the calculation works
</h5>


<table class="table table-bordered">


<thead class="table-dark">

<tr>

<th>
Item
</th>

<th>
Amount
</th>

</tr>

</thead>


<tbody>


<tr>

<td>
New academic-period fees
</td>

<td>
Taken from fee structure
</td>

</tr>


<tr>

<td>
Previous unpaid balance
</td>

<td>
Carried from the student's previous period
</td>

</tr>


<tr>

<td>
Amount paid
</td>

<td>
0.00 for a new account
</td>

</tr>


<tr class="table-warning">

<td>
<strong>
Opening balance
</strong>
</td>

<td>

<strong>
New fees + previous unpaid balance
</strong>

</td>

</tr>


</tbody>


</table>


</div>

</div>


</div>


</body>

</html>