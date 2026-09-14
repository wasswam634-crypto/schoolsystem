<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = "";
$message_type = "success";


/*
|--------------------------------------------------------------------------
| SAVE / UPDATE FEE STRUCTURE
|--------------------------------------------------------------------------
*/

if (isset($_POST['save'])) {

    $period_id = (int)($_POST['period_id'] ?? 0);
    $group_id  = (int)($_POST['group_id'] ?? 0);

    $item_ids = $_POST['item_id'] ?? [];
    $amounts  = $_POST['amount'] ?? [];

    if ($period_id <= 0 || $group_id <= 0) {

        $message = "Please select an academic period and class.";
        $message_type = "danger";

    } else {

        mysqli_begin_transaction($conn);

        try {

            /*
            |--------------------------------------------------------------------------
            | Verify period exists
            |--------------------------------------------------------------------------
            */

            $period_check = mysqli_prepare(
                $conn,
                "SELECT period_id
                 FROM academic_periods
                 WHERE period_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $period_check,
                "i",
                $period_id
            );

            mysqli_stmt_execute($period_check);

            $period_result = mysqli_stmt_get_result($period_check);

            if (!mysqli_fetch_assoc($period_result)) {
                throw new Exception("Selected academic period does not exist.");
            }

            mysqli_stmt_close($period_check);


            /*
            |--------------------------------------------------------------------------
            | Verify class/group exists
            |--------------------------------------------------------------------------
            */

            $group_check = mysqli_prepare(
                $conn,
                "SELECT group_id
                 FROM academic_groups
                 WHERE group_id = ?
                 AND status = 'Active'
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $group_check,
                "i",
                $group_id
            );

            mysqli_stmt_execute($group_check);

            $group_result = mysqli_stmt_get_result($group_check);

            if (!mysqli_fetch_assoc($group_result)) {
                throw new Exception("Selected class does not exist or is inactive.");
            }

            mysqli_stmt_close($group_check);


            /*
            |--------------------------------------------------------------------------
            | Process fee items
            |--------------------------------------------------------------------------
            */

            $saved_items = 0;

            for ($i = 0; $i < count($item_ids); $i++) {

                $item_id = (int)($item_ids[$i] ?? 0);

                $amount = (float)($amounts[$i] ?? 0);


                /*
                |----------------------------------------------------------------------
                | Ignore invalid item IDs
                |----------------------------------------------------------------------
                */

                if ($item_id <= 0) {
                    continue;
                }


                /*
                |----------------------------------------------------------------------
                | Ignore empty amounts
                |----------------------------------------------------------------------
                */

                if ($amount <= 0) {
                    continue;
                }


                /*
                |----------------------------------------------------------------------
                | Make sure fee item exists and is active
                |----------------------------------------------------------------------
                */

                $item_check = mysqli_prepare(
                    $conn,

                    "SELECT item_id
                     FROM fee_items
                     WHERE item_id = ?
                     AND status = 'Active'
                     LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $item_check,
                    "i",
                    $item_id
                );

                mysqli_stmt_execute($item_check);

                $item_result =
                    mysqli_stmt_get_result($item_check);

                if (!mysqli_fetch_assoc($item_result)) {

                    mysqli_stmt_close($item_check);

                    throw new Exception(
                        "One of the selected fee items is invalid."
                    );
                }

                mysqli_stmt_close($item_check);


                /*
                |----------------------------------------------------------------------
                | Check whether this structure already exists
                |----------------------------------------------------------------------
                */

                $check = mysqli_prepare(
                    $conn,

                    "SELECT structure_id
                     FROM fee_structure
                     WHERE period_id = ?
                     AND group_id = ?
                     AND item_id = ?
                     LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $check,
                    "iii",
                    $period_id,
                    $group_id,
                    $item_id
                );

                mysqli_stmt_execute($check);

                $check_result =
                    mysqli_stmt_get_result($check);

                $existing =
                    mysqli_fetch_assoc($check_result);

                mysqli_stmt_close($check);


                /*
                |----------------------------------------------------------------------
                | UPDATE existing structure
                |----------------------------------------------------------------------
                */

                if ($existing) {

                    $structure_id =
                        (int)$existing['structure_id'];

                    $update = mysqli_prepare(
                        $conn,

                        "UPDATE fee_structure
                         SET amount = ?
                         WHERE structure_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $update,
                        "di",
                        $amount,
                        $structure_id
                    );

                    if (!mysqli_stmt_execute($update)) {

                        mysqli_stmt_close($update);

                        throw new Exception(
                            "Failed to update fee structure."
                        );
                    }

                    mysqli_stmt_close($update);
                }


                /*
                |----------------------------------------------------------------------
                | INSERT new structure
                |----------------------------------------------------------------------
                */

                else {

                    $insert = mysqli_prepare(
                        $conn,

                        "INSERT INTO fee_structure
                        (
                            period_id,
                            group_id,
                            item_id,
                            amount
                        )
                        VALUES (?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $insert,
                        "iiid",
                        $period_id,
                        $group_id,
                        $item_id,
                        $amount
                    );

                    if (!mysqli_stmt_execute($insert)) {

                        mysqli_stmt_close($insert);

                        throw new Exception(
                            "Failed to save fee structure."
                        );
                    }

                    mysqli_stmt_close($insert);
                }

                $saved_items++;
            }


            /*
            |--------------------------------------------------------------------------
            | Make sure at least one fee item was entered
            |--------------------------------------------------------------------------
            */

            if ($saved_items <= 0) {

                throw new Exception(
                    "Please enter an amount for at least one fee item."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);

            $message =
                "Fee structure saved successfully.";

            $message_type = "success";
        }

        catch (Exception $e) {

            mysqli_rollback($conn);

            $message = $e->getMessage();
            $message_type = "danger";
        }
    }
}



/*
|--------------------------------------------------------------------------
| GENERATE STUDENT FEE ACCOUNTS
|--------------------------------------------------------------------------
*/

if (isset($_POST['generate_accounts'])) {

    $period_id = (int)($_POST['generate_period_id'] ?? 0);
    $group_id  = (int)($_POST['generate_group_id'] ?? 0);


    if ($period_id <= 0 || $group_id <= 0) {

        $message =
            "Please select an academic period and class.";

        $message_type = "danger";

    } else {

        mysqli_begin_transaction($conn);

        try {

            /*
            |--------------------------------------------------------------------------
            | Get total fee for selected period + class
            |--------------------------------------------------------------------------
            */

            $total_query = mysqli_prepare(
                $conn,

                "SELECT
                    COALESCE(SUM(amount), 0) AS total_fee
                 FROM fee_structure
                 WHERE period_id = ?
                 AND group_id = ?"
            );

            mysqli_stmt_bind_param(
                $total_query,
                "ii",
                $period_id,
                $group_id
            );

            mysqli_stmt_execute($total_query);

            $total_result =
                mysqli_stmt_get_result($total_query);

            $total_row =
                mysqli_fetch_assoc($total_result);

            $total_fee =
                (float)$total_row['total_fee'];

            mysqli_stmt_close($total_query);


            /*
            |--------------------------------------------------------------------------
            | Check whether fee structure exists
            |--------------------------------------------------------------------------
            */

            if ($total_fee <= 0) {

                throw new Exception(
                    "No fee structure has been created for this class and academic period. Please create the fee structure first."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Find previous academic period
            |--------------------------------------------------------------------------
            */

            $previous_period_id = 0;

            $previous_period_stmt = mysqli_prepare(
                $conn,

                "SELECT period_id
                 FROM academic_periods
                 WHERE period_id < ?
                 ORDER BY period_id DESC
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $previous_period_stmt,
                "i",
                $period_id
            );

            mysqli_stmt_execute(
                $previous_period_stmt
            );

            $previous_period_result =
                mysqli_stmt_get_result(
                    $previous_period_stmt
                );

            $previous_period =
                mysqli_fetch_assoc(
                    $previous_period_result
                );

            if ($previous_period) {

                $previous_period_id =
                    (int)$previous_period['period_id'];
            }

            mysqli_stmt_close(
                $previous_period_stmt
            );


            /*
            |--------------------------------------------------------------------------
            | Get all active students in selected class
            |--------------------------------------------------------------------------
            */

            $students_stmt = mysqli_prepare(
                $conn,

                "SELECT student_id
                 FROM students
                 WHERE group_id = ?
                 AND status = 'Active'
                 ORDER BY student_id"
            );

            mysqli_stmt_bind_param(
                $students_stmt,
                "i",
                $group_id
            );

            mysqli_stmt_execute(
                $students_stmt
            );

            $students_result =
                mysqli_stmt_get_result(
                    $students_stmt
                );


            $created = 0;
            $existing = 0;


            /*
            |--------------------------------------------------------------------------
            | Process each student
            |--------------------------------------------------------------------------
            */

            while (
                $student =
                mysqli_fetch_assoc($students_result)
            ) {

                $student_id =
                    (int)$student['student_id'];


                /*
                |--------------------------------------------------------------------------
                | Check whether current account already exists
                |--------------------------------------------------------------------------
                */

                $check = mysqli_prepare(
                    $conn,

                    "SELECT student_fee_id
                     FROM student_fees
                     WHERE student_id = ?
                     AND period_id = ?
                     LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $check,
                    "ii",
                    $student_id,
                    $period_id
                );

                mysqli_stmt_execute($check);

                $check_result =
                    mysqli_stmt_get_result($check);

                $current_account =
                    mysqli_fetch_assoc($check_result);

                mysqli_stmt_close($check);


                /*
                |--------------------------------------------------------------------------
                | Do not recreate an existing account
                |--------------------------------------------------------------------------
                */

                if ($current_account) {

                    $existing++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Get previous outstanding balance
                |--------------------------------------------------------------------------
                */

                $previous_balance = 0;

                if ($previous_period_id > 0) {

                    $previous_stmt = mysqli_prepare(
                        $conn,

                        "SELECT balance
                         FROM student_fees
                         WHERE student_id = ?
                         AND period_id = ?
                         LIMIT 1"
                    );

                    mysqli_stmt_bind_param(
                        $previous_stmt,
                        "ii",
                        $student_id,
                        $previous_period_id
                    );

                    mysqli_stmt_execute(
                        $previous_stmt
                    );

                    $previous_result =
                        mysqli_stmt_get_result(
                            $previous_stmt
                        );

                    $previous_account =
                        mysqli_fetch_assoc(
                            $previous_result
                        );

                    if ($previous_account) {

                        $previous_balance =
                            (float)$previous_account['balance'];
                    }

                    mysqli_stmt_close(
                        $previous_stmt
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Never carry a negative previous balance
                |--------------------------------------------------------------------------
                */

                if ($previous_balance < 0) {

                    $previous_balance = 0;
                }


                /*
                |--------------------------------------------------------------------------
                | Create account
                |--------------------------------------------------------------------------
                */

                $amount_paid = 0;

                $balance =
                    $total_fee +
                    $previous_balance;

                $balance =
                    round($balance, 2);

                $status = "Pending";


                /*
                |--------------------------------------------------------------------------
                | Insert student fee account
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

                mysqli_stmt_bind_param(
                    $insert,
                    "iidddds",
                    $student_id,
                    $period_id,
                    $total_fee,
                    $previous_balance,
                    $amount_paid,
                    $balance,
                    $status
                );

                if (!mysqli_stmt_execute($insert)) {

                    mysqli_stmt_close($insert);

                    throw new Exception(
                        "Failed to create fee account for student ID "
                        . $student_id
                    );
                }

                mysqli_stmt_close($insert);

                $created++;
            }


            mysqli_stmt_close($students_stmt);


            /*
            |--------------------------------------------------------------------------
            | Commit transaction
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);


            $message =
                "Student fee accounts generated successfully. "
                . $created
                . " account(s) created and "
                . $existing
                . " account(s) already existed.";

            $message_type = "success";
        }

        catch (Exception $e) {

            mysqli_rollback($conn);

            $message =
                "Fee account generation failed: "
                . $e->getMessage();

            $message_type = "danger";
        }
    }
}



/*
|--------------------------------------------------------------------------
| GET ACADEMIC PERIODS
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
| GET ACTIVE GROUPS / CLASSES
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
        "Failed to load classes: "
        . mysqli_error($conn)
    );
}



/*
|--------------------------------------------------------------------------
| GET ACTIVE FEE ITEMS
|--------------------------------------------------------------------------
*/

$items = mysqli_query(
    $conn,

    "SELECT
        item_id,
        item_name
     FROM fee_items
     WHERE status = 'Active'
     ORDER BY item_name"
);

if (!$items) {

    die(
        "Failed to load fee items: "
        . mysqli_error($conn)
    );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Fee Structure Management</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<style>

body {
    background: #f4f6f9;
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 3px 15px rgba(0,0,0,.08);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.table th {
    white-space: nowrap;
}

.total-box {
    font-size: 20px;
    font-weight: bold;
}

</style>

</head>


<body>


<div class="container-fluid p-4">


<h2 class="mb-4">

💰 Fee Structure Management

</h2>



<?php if ($message): ?>

<div class="alert alert-<?=e($message_type);?>">

<?=e($message);?>

</div>

<?php endif; ?>



<!--
|--------------------------------------------------------------------------
| CREATE / UPDATE FEE STRUCTURE
|--------------------------------------------------------------------------
-->

<div class="card mb-4">


<div class="card-header bg-primary text-white">

<h5 class="mb-0">

Create / Update Fee Structure

</h5>

</div>


<div class="card-body">


<form method="POST"
      id="feeStructureForm">


<div class="row g-3 mb-4">


<div class="col-md-4">

<label class="form-label">

Academic Period

</label>


<select
name="period_id"
id="period_id"
class="form-select"
required>

<option value="">

Select Academic Period

</option>


<?php while (
    $p = mysqli_fetch_assoc($periods)
): ?>

<option
value="<?=$p['period_id'];?>">

<?=e($p['academic_year']);?>

 -

<?=e($p['period_name']);?>

<?php if (!empty($p['status'])): ?>

(
<?=e($p['status']);?>
)

<?php endif; ?>

</option>

<?php endwhile; ?>

</select>

</div>



<div class="col-md-4">

<label class="form-label">

Class / Group

</label>


<select
name="group_id"
id="group_id"
class="form-select"
required>

<option value="">

Select Class

</option>


<?php while (
    $g = mysqli_fetch_assoc($groups)
): ?>

<option
value="<?=$g['group_id'];?>">

<?=e($g['group_name']);?>

</option>

<?php endwhile; ?>

</select>

</div>


</div>



<h5 class="mb-3">

Fee Items

</h5>



<div class="table-responsive">

<table class="table table-bordered align-middle">


<thead class="table-dark">

<tr>

<th>
Fee Item
</th>

<th>
Amount (UGX)
</th>

</tr>

</thead>


<tbody>


<?php while (
    $item = mysqli_fetch_assoc($items)
): ?>


<tr>

<td>

<?=e($item['item_name']);?>

<input
type="hidden"
name="item_id[]"
value="<?=$item['item_id'];?>">

</td>


<td>

<input
type="number"
step="0.01"
min="0"
name="amount[]"
class="form-control fee-amount"
placeholder="Enter amount">

</td>

</tr>


<?php endwhile; ?>


</tbody>


<tfoot>

<tr>

<th class="text-end">

Total Fee:

</th>

<th>

<span id="totalFee">

UGX 0

</span>

</th>

</tr>

</tfoot>


</table>

</div>



<button
type="submit"
name="save"
class="btn btn-success">

💾 Save Fee Structure

</button>


<button
type="reset"
class="btn btn-secondary">

Clear

</button>


</form>


</div>

</div>



<!--
|--------------------------------------------------------------------------
| GENERATE STUDENT ACCOUNTS
|--------------------------------------------------------------------------
-->

<div class="card">


<div class="card-header bg-dark text-white">

<h5 class="mb-0">

Generate Student Fee Accounts

</h5>

</div>



<div class="card-body">


<div class="alert alert-info">

<strong>How this works:</strong>

The system uses the fee structure entered by the administrator
for the selected class and academic period.

<br><br>

It then finds every active student belonging to that class
and creates a fee account for each student.

<br><br>

If a student has an unpaid balance from the previous academic
period, that balance is automatically carried forward.

</div>



<form method="POST">


<div class="row g-3">


<div class="col-md-5">

<label class="form-label">

Academic Period

</label>


<select
name="generate_period_id"
class="form-select"
required>


<option value="">

Select Academic Period

</option>


<?php

$periods_generate = mysqli_query(

    $conn,

    "SELECT
        period_id,
        academic_year,
        period_name,
        status
     FROM academic_periods
     ORDER BY period_id DESC"

);

?>


<?php while (
    $p = mysqli_fetch_assoc($periods_generate)
): ?>


<option
value="<?=$p['period_id'];?>">

<?=e($p['academic_year']);?>

 -

<?=e($p['period_name']);?>

<?php if (!empty($p['status'])): ?>

(
<?=e($p['status']);?>
)

<?php endif; ?>

</option>


<?php endwhile; ?>


</select>

</div>



<div class="col-md-5">

<label class="form-label">

Class / Group

</label>


<select
name="generate_group_id"
class="form-select"
required>


<option value="">

Select Class

</option>


<?php

$groups_generate = mysqli_query(

    $conn,

    "SELECT
        group_id,
        group_name
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name"

);

?>


<?php while (
    $g = mysqli_fetch_assoc($groups_generate)
): ?>


<option
value="<?=$g['group_id'];?>">

<?=e($g['group_name']);?>

</option>


<?php endwhile; ?>


</select>

</div>



<div class="col-md-2 d-flex align-items-end">


<button
type="submit"
name="generate_accounts"
class="btn btn-dark w-100"
onclick="return confirm(
'Generate fee accounts for all active students in this class? Existing accounts will not be recreated.'
);">

Generate Accounts

</button>


</div>


</div>


</form>


</div>


</div>


</div>



<script>

/*
|--------------------------------------------------------------------------
| Calculate total fee automatically
|--------------------------------------------------------------------------
*/

function calculateTotal() {

    let total = 0;

    document
        .querySelectorAll('.fee-amount')
        .forEach(function(input) {

            let amount =
                parseFloat(input.value) || 0;

            total += amount;
        });


    document.getElementById('totalFee').textContent =
        'UGX ' +
        total.toLocaleString('en-UG');
}


/*
|--------------------------------------------------------------------------
| Recalculate whenever amount changes
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll('.fee-amount')
    .forEach(function(input) {

        input.addEventListener(
            'input',
            calculateTotal
        );

    });


/*
|--------------------------------------------------------------------------
| Reset total when form is cleared
|--------------------------------------------------------------------------
*/

document
    .getElementById('feeStructureForm')
    .addEventListener('reset', function() {

        setTimeout(function() {

            calculateTotal();

        }, 50);

    });


/*
|--------------------------------------------------------------------------
| Initial calculation
|--------------------------------------------------------------------------
*/

calculateTotal();

</script>


</body>

</html>