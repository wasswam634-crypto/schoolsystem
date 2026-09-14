```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = "";
$message_type = "success";


/*
|--------------------------------------------------------------------------
| GENERATE / REFRESH STUDENT FEE ACCOUNTS
|--------------------------------------------------------------------------
|
| This function creates an account for every ACTIVE student
| for the selected academic period.
|
| Previous unpaid balances are carried forward from the
| most recent previous student_fees account.
|
*/
function generate_period_accounts($conn, $period_id)
{
    $created = 0;
    $existing = 0;

    /*
    |--------------------------------------------------------------------------
    | GET PERIOD
    |--------------------------------------------------------------------------
    */

    $period_stmt = mysqli_prepare(
        $conn,
        "SELECT period_id, academic_year, period_name, status
         FROM academic_periods
         WHERE period_id = ?
         LIMIT 1"
    );

    if (!$period_stmt) {
        throw new Exception(
            "Could not prepare academic period query: "
            . mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $period_stmt,
        "i",
        $period_id
    );

    mysqli_stmt_execute($period_stmt);

    $period_result = mysqli_stmt_get_result($period_stmt);

    $period = mysqli_fetch_assoc($period_result);

    if (!$period) {
        throw new Exception("Academic period not found.");
    }


    /*
    |--------------------------------------------------------------------------
    | GET FEE STRUCTURE FOR THIS PERIOD
    |--------------------------------------------------------------------------
    |
    | We calculate the total fee for each group once.
    |
    */

    $fee_stmt = mysqli_prepare(
        $conn,

        "SELECT
            group_id,
            COALESCE(SUM(amount), 0) AS total_amount
         FROM fee_structure
         WHERE period_id = ?
         GROUP BY group_id"
    );

    if (!$fee_stmt) {
        throw new Exception(
            "Could not prepare fee structure query: "
            . mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $fee_stmt,
        "i",
        $period_id
    );

    mysqli_stmt_execute($fee_stmt);

    $fee_result = mysqli_stmt_get_result($fee_stmt);


    /*
    |--------------------------------------------------------------------------
    | STORE FEE TOTALS BY GROUP
    |--------------------------------------------------------------------------
    */

    $group_fees = [];

    while ($fee = mysqli_fetch_assoc($fee_result)) {

        $group_id = (int) $fee['group_id'];

        $group_fees[$group_id] =
            round(
                (float) $fee['total_amount'],
                2
            );
    }


    /*
    |--------------------------------------------------------------------------
    | GET ALL ACTIVE STUDENTS
    |--------------------------------------------------------------------------
    */

    $students_stmt = mysqli_prepare(
        $conn,

        "SELECT
            student_id,
            full_name,
            reg_no,
            group_id
         FROM students
         WHERE status = 'Active'
         ORDER BY full_name ASC"
    );

    if (!$students_stmt) {
        throw new Exception(
            "Could not prepare students query: "
            . mysqli_error($conn)
        );
    }

    mysqli_stmt_execute($students_stmt);

    $students_result =
        mysqli_stmt_get_result($students_stmt);


    /*
    |--------------------------------------------------------------------------
    | PREPARE CHECK ACCOUNT
    |--------------------------------------------------------------------------
    */

    $check_account = mysqli_prepare(
        $conn,

        "SELECT student_fee_id
         FROM student_fees
         WHERE student_id = ?
         AND period_id = ?
         LIMIT 1"
    );

    if (!$check_account) {
        throw new Exception(
            "Could not prepare account check query: "
            . mysqli_error($conn)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PREPARE PREVIOUS ACCOUNT QUERY
    |--------------------------------------------------------------------------
    |
    | We find the most recent fee account belonging to this student
    | from an older academic period.
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
                    WHERE fp.student_fee_id = sf.student_fee_id
                ),
                0
            ) AS payments_made

         FROM student_fees sf

         WHERE sf.student_id = ?

         AND sf.period_id < ?

         ORDER BY sf.period_id DESC

         LIMIT 1"
    );

    if (!$previous_stmt) {
        throw new Exception(
            "Could not prepare previous balance query: "
            . mysqli_error($conn)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PREPARE INSERT ACCOUNT
    |--------------------------------------------------------------------------
    */

    $insert_account = mysqli_prepare(
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

    if (!$insert_account) {
        throw new Exception(
            "Could not prepare student fee account insertion: "
            . mysqli_error($conn)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROCESS EVERY ACTIVE STUDENT
    |--------------------------------------------------------------------------
    */

    while ($student = mysqli_fetch_assoc($students_result)) {

        $student_id =
            (int) $student['student_id'];

        $group_id =
            (int) $student['group_id'];


        /*
        |--------------------------------------------------------------------------
        | CHECK WHETHER ACCOUNT ALREADY EXISTS
        |--------------------------------------------------------------------------
        */

        mysqli_stmt_bind_param(
            $check_account,
            "ii",
            $student_id,
            $period_id
        );

        mysqli_stmt_execute($check_account);

        $check_result =
            mysqli_stmt_get_result($check_account);

        $existing_account =
            mysqli_fetch_assoc($check_result);


        if ($existing_account) {

            $existing++;

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | GET THIS STUDENT'S NEW PERIOD FEES
        |--------------------------------------------------------------------------
        */

        $total_amount =
            (float) ($group_fees[$group_id] ?? 0);

        $total_amount =
            round($total_amount, 2);


        /*
        |--------------------------------------------------------------------------
        | CHECK THAT A FEE STRUCTURE EXISTS
        |--------------------------------------------------------------------------
        */

        if ($total_amount <= 0) {

            throw new Exception(
                "No fee structure has been created for group ID "
                . $group_id
                . " in "
                . $period['academic_year']
                . " - "
                . $period['period_name']
                . ". Please create the fee structure first."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FIND PREVIOUS UNPAID BALANCE
        |--------------------------------------------------------------------------
        */

        $previous_balance = 0;


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


        if ($previous_account) {

            $old_total =
                (float) (
                    $previous_account['total_amount']
                    ?? 0
                );

            $old_previous =
                (float) (
                    $previous_account['previous_balance']
                    ?? 0
                );

            $old_paid =
                (float) (
                    $previous_account['payments_made']
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | PREVIOUS PERIOD TOTAL OWED
            |--------------------------------------------------------------------------
            */

            $old_total_owed =
                $old_total
                +
                $old_previous;


            /*
            |--------------------------------------------------------------------------
            | PREVIOUS UNPAID BALANCE
            |--------------------------------------------------------------------------
            */

            $previous_balance =
                $old_total_owed
                -
                $old_paid;


            $previous_balance =
                round(
                    $previous_balance,
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | NEVER CARRY A NEGATIVE BALANCE
            |--------------------------------------------------------------------------
            */

            if ($previous_balance < 0) {

                $previous_balance = 0;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | NEW ACCOUNT VALUES
        |--------------------------------------------------------------------------
        */

        $amount_paid = 0;


        /*
        | New fees + old unpaid balance
        */

        $balance =
            $total_amount
            +
            $previous_balance;


        $balance =
            round(
                $balance,
                2
            );


        $status = "Pending";


        /*
        |--------------------------------------------------------------------------
        | INSERT ACCOUNT
        |--------------------------------------------------------------------------
        */

        mysqli_stmt_bind_param(
            $insert_account,
            "iidddds",

            $student_id,
            $period_id,
            $total_amount,
            $previous_balance,
            $amount_paid,
            $balance,
            $status
        );


        if (!mysqli_stmt_execute($insert_account)) {

            throw new Exception(
                "Could not create fee account for "
                . $student['full_name']
                . ": "
                . mysqli_stmt_error($insert_account)
            );
        }


        $created++;
    }


    return [
        'created' => $created,
        'existing' => $existing
    ];
}


/*
|--------------------------------------------------------------------------
| SAVE / OPEN NEW PERIOD
|--------------------------------------------------------------------------
*/

if (isset($_POST['save_period'])) {

    $academic_year =
        trim($_POST['academic_year'] ?? '');

    $period_name =
        trim($_POST['period_name'] ?? '');

    $start_date =
        trim($_POST['start_date'] ?? '');

    $end_date =
        trim($_POST['end_date'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $academic_year === ''
        ||
        $period_name === ''
        ||
        $start_date === ''
        ||
        $end_date === ''
    ) {

        $message =
            "Please fill in all academic period fields.";

        $message_type = "danger";

    } elseif ($end_date < $start_date) {

        $message =
            "The end date cannot be before the start date.";

        $message_type = "danger";

    } else {

        mysqli_begin_transaction($conn);


        try {

            /*
            |--------------------------------------------------------------------------
            | CLOSE ALL EXISTING ACTIVE PERIODS
            |--------------------------------------------------------------------------
            */

            $close_stmt = mysqli_prepare(
                $conn,

                "UPDATE academic_periods
                 SET status = 'Closed'
                 WHERE status = 'Active'"
            );

            if (!$close_stmt) {

                throw new Exception(
                    "Could not close existing period: "
                    . mysqli_error($conn)
                );
            }

            mysqli_stmt_execute($close_stmt);


            /*
            |--------------------------------------------------------------------------
            | CREATE NEW ACTIVE PERIOD
            |--------------------------------------------------------------------------
            */

            $insert_period = mysqli_prepare(
                $conn,

                "INSERT INTO academic_periods
                (
                    academic_year,
                    period_name,
                    start_date,
                    end_date,
                    status
                )
                VALUES (?, ?, ?, ?, 'Active')"
            );

            if (!$insert_period) {

                throw new Exception(
                    "Could not prepare academic period insertion: "
                    . mysqli_error($conn)
                );
            }


            mysqli_stmt_bind_param(
                $insert_period,
                "ssss",
                $academic_year,
                $period_name,
                $start_date,
                $end_date
            );


            if (!mysqli_stmt_execute($insert_period)) {

                throw new Exception(
                    "Could not create academic period: "
                    . mysqli_stmt_error($insert_period)
                );
            }


            /*
            |--------------------------------------------------------------------------
            | GET NEW PERIOD ID
            |--------------------------------------------------------------------------
            */

            $new_period_id =
                mysqli_insert_id($conn);


            if ($new_period_id <= 0) {

                throw new Exception(
                    "The new academic period could not be identified."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CHECK WHETHER FEE STRUCTURE EXISTS
            |--------------------------------------------------------------------------
            */

            $fee_check = mysqli_prepare(
                $conn,

                "SELECT COUNT(*) AS total
                 FROM fee_structure
                 WHERE period_id = ?"
            );

            if (!$fee_check) {

                throw new Exception(
                    "Could not check fee structure."
                );
            }


            mysqli_stmt_bind_param(
                $fee_check,
                "i",
                $new_period_id
            );


            mysqli_stmt_execute($fee_check);


            $fee_check_result =
                mysqli_stmt_get_result($fee_check);


            $fee_check_row =
                mysqli_fetch_assoc($fee_check_result);


            $fee_count =
                (int) ($fee_check_row['total'] ?? 0);


            /*
            |--------------------------------------------------------------------------
            | GENERATE ACCOUNTS IF FEE STRUCTURE ALREADY EXISTS
            |--------------------------------------------------------------------------
            */

            $created_accounts = 0;
            $existing_accounts = 0;


            if ($fee_count > 0) {

                $account_result =
                    generate_period_accounts(
                        $conn,
                        $new_period_id
                    );


                $created_accounts =
                    $account_result['created'];

                $existing_accounts =
                    $account_result['existing'];
            }


            /*
            |--------------------------------------------------------------------------
            | COMMIT EVERYTHING
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);


            /*
            |--------------------------------------------------------------------------
            | SUCCESS MESSAGE
            |--------------------------------------------------------------------------
            */

            if ($fee_count > 0) {

                $message =
                    "Academic period opened successfully. "
                    . $created_accounts
                    . " student fee account(s) created.";

                if ($existing_accounts > 0) {

                    $message .=
                        " "
                        . $existing_accounts
                        . " account(s) already existed.";
                }

            } else {

                $message =
                    "Academic period opened successfully. "
                    . "Fee structure has not yet been created for this period, "
                    . "so student accounts will be generated after the fee structure is added.";
            }


            $message_type = "success";


        } catch (Exception $e) {

            mysqli_rollback($conn);


            $message =
                "The academic period was not created. "
                . $e->getMessage();

            $message_type = "danger";
        }
    }
}


/*
|--------------------------------------------------------------------------
| MANUALLY REFRESH / GENERATE ACCOUNTS FOR A PERIOD
|--------------------------------------------------------------------------
|
| This is useful after you have created the fee structure.
|
*/

if (isset($_GET['generate_accounts'])) {

    $period_id =
        (int) $_GET['generate_accounts'];


    if ($period_id <= 0) {

        $message =
            "Invalid academic period.";

        $message_type = "danger";

    } else {

        mysqli_begin_transaction($conn);


        try {

            /*
            |--------------------------------------------------------------------------
            | ONLY ALLOW ACCOUNT GENERATION FOR ACTIVE PERIOD
            |--------------------------------------------------------------------------
            */

            $active_check = mysqli_prepare(
                $conn,

                "SELECT period_id
                 FROM academic_periods
                 WHERE period_id = ?
                 AND status = 'Active'
                 LIMIT 1"
            );


            if (!$active_check) {

                throw new Exception(
                    "Could not verify academic period."
                );
            }


            mysqli_stmt_bind_param(
                $active_check,
                "i",
                $period_id
            );


            mysqli_stmt_execute($active_check);


            $active_result =
                mysqli_stmt_get_result($active_check);


            if (!mysqli_fetch_assoc($active_result)) {

                throw new Exception(
                    "Only the active academic period can have accounts generated."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | GENERATE ACCOUNTS
            |--------------------------------------------------------------------------
            */

            $account_result =
                generate_period_accounts(
                    $conn,
                    $period_id
                );


            mysqli_commit($conn);


            $message =
                $account_result['created']
                . " student fee account(s) created.";

            if ($account_result['existing'] > 0) {

                $message .=
                    " "
                    . $account_result['existing']
                    . " account(s) already existed.";
            }


            $message_type = "success";


        } catch (Exception $e) {

            mysqli_rollback($conn);


            $message =
                "Accounts were not generated. "
                . $e->getMessage();

            $message_type = "danger";
        }
    }
}


/*
|--------------------------------------------------------------------------
| CLOSE PERIOD
|--------------------------------------------------------------------------
*/

if (isset($_GET['close'])) {

    $id =
        (int) $_GET['close'];


    if ($id <= 0) {

        $message =
            "Invalid academic period.";

        $message_type = "danger";

    } else {

        $stmt = mysqli_prepare(
            $conn,

            "UPDATE academic_periods
             SET status = 'Closed'
             WHERE period_id = ?"
        );


        if (!$stmt) {

            $message =
                "Could not prepare close period query.";

            $message_type = "danger";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id
            );


            if (mysqli_stmt_execute($stmt)) {

                $message =
                    "Academic period closed successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Could not close academic period.";

                $message_type = "danger";
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET PERIODS
|--------------------------------------------------------------------------
*/

$periods = mysqli_query(
    $conn,

    "SELECT
        ap.*,

        (
            SELECT COUNT(*)
            FROM student_fees sf
            WHERE sf.period_id = ap.period_id
        ) AS account_count

     FROM academic_periods ap

     ORDER BY ap.period_id DESC"
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

<title>Academic Periods</title>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>

</head>


<body>


<div class="container p-4">


<h2 class="mb-4">

📅 Academic Period Management

</h2>


<?php if ($message): ?>

<div
class="alert alert-<?=e($message_type);?>"
>

<?=e($message);?>

</div>

<?php endif; ?>


<!--
|--------------------------------------------------------------------------
| CREATE NEW PERIOD
|--------------------------------------------------------------------------
-->

<div class="card mb-4">

<div class="card-header bg-primary text-white">

Create / Open New Academic Period

</div>


<div class="card-body">


<form method="POST">


<div class="row g-3">


<div class="col-md-3">

<label class="form-label">

Academic Year

</label>

<input
type="text"
name="academic_year"
class="form-control"
value="<?=e(date('Y'));?>"
required
>

</div>


<div class="col-md-3">

<label class="form-label">

Period Name

</label>

<input
type="text"
name="period_name"
class="form-control"
placeholder="Example: Semester 1"
required
>

</div>


<div class="col-md-3">

<label class="form-label">

Start Date

</label>

<input
type="date"
name="start_date"
class="form-control"
required
>

</div>


<div class="col-md-3">

<label class="form-label">

End Date

</label>

<input
type="date"
name="end_date"
class="form-control"
required
>

</div>


</div>


<br>


<button
type="submit"
name="save_period"
class="btn btn-success"
>

Open New Period

</button>


</form>


</div>

</div>


<!--
|--------------------------------------------------------------------------
| EXISTING PERIODS
|--------------------------------------------------------------------------
-->

<div class="card">

<div class="card-header bg-dark text-white">

Existing Academic Periods

</div>


<div class="card-body">


<div class="table-responsive">


<table
class="table table-bordered table-striped align-middle"
>


<thead class="table-dark">

<tr>

<th>
Year
</th>

<th>
Period
</th>

<th>
Start
</th>

<th>
End
</th>

<th>
Status
</th>

<th>
Accounts
</th>

<th>
Action
</th>

</tr>

</thead>


<tbody>


<?php while ($row = mysqli_fetch_assoc($periods)): ?>


<tr>


<td>

<?=e($row['academic_year']);?>

</td>


<td>

<?=e($row['period_name']);?>

</td>


<td>

<?=e($row['start_date']);?>

</td>


<td>

<?=e($row['end_date']);?>

</td>


<td>


<?php if ($row['status'] === 'Active'): ?>

<span class="badge bg-success">

Active

</span>

<?php else: ?>

<span class="badge bg-secondary">

Closed

</span>

<?php endif; ?>


</td>


<td>

<strong>

<?=e($row['account_count']);?>

</strong>

student(s)

</td>


<td>


<?php if ($row['status'] === 'Active'): ?>


<a
href="?generate_accounts=<?=$row['period_id'];?>"
class="btn btn-primary btn-sm"
onclick="return confirm('Generate fee accounts for all active students for this period?');"
>

Refresh Accounts

</a>


<a
href="?close=<?=$row['period_id'];?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Are you sure you want to close this academic period?');"
>

Close

</a>


<?php else: ?>


<span class="text-muted">

Closed

</span>


<?php endif; ?>


</td>


</tr>


<?php endwhile; ?>


</tbody>


</table>


</div>


</div>

</div>


</div>


</body>

</html>
```
