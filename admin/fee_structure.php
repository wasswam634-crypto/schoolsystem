<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = "";
$message_type = "success";


/*
====================================================
SAVE FEE STRUCTURE
====================================================
*/

if(isset($_POST['save'])){

    $period_id = (int)$_POST['period_id'];
    $group_id  = (int)$_POST['group_id'];

    $item_ids = $_POST['item_id'] ?? [];
    $amounts  = $_POST['amount'] ?? [];

    $success = true;


    if($period_id <= 0 || $group_id <= 0){

        $message = "Please select an academic period and class.";
        $message_type = "danger";

    }else{


        for($i = 0; $i < count($item_ids); $i++){

            $item_id = (int)$item_ids[$i];

            $amount = (float)($amounts[$i] ?? 0);


            /*
            Ignore empty fee amounts
            */

            if($amount <= 0){

                continue;

            }


            $stmt = mysqli_prepare(
                $conn,

                "INSERT INTO fee_structure
                (
                    period_id,
                    group_id,
                    item_id,
                    amount
                )

                VALUES(?,?,?,?)

                ON DUPLICATE KEY UPDATE
                    amount = VALUES(amount)"
            );


            if(!$stmt){

                $success = false;
                break;

            }


            mysqli_stmt_bind_param(
                $stmt,
                "iiid",
                $period_id,
                $group_id,
                $item_id,
                $amount
            );


            if(!mysqli_stmt_execute($stmt)){

                $success = false;

            }


            mysqli_stmt_close($stmt);

        }


        if($success){

            $message =
                "Fee structure saved successfully.";

        }else{

            $message =
                "Some fee items could not be saved.";

            $message_type = "danger";

        }

    }

}



/*
====================================================
GENERATE STUDENT FEE ACCOUNTS
====================================================
*/

if(isset($_POST['generate_accounts'])){

    $period_id = (int)$_POST['generate_period_id'];
    $group_id  = (int)$_POST['generate_group_id'];


    if($period_id <= 0 || $group_id <= 0){

        $message =
            "Please select an academic period and class.";

        $message_type = "danger";

    }else{


        /*
        --------------------------------------------
        CALCULATE TOTAL FEE FOR THIS GROUP/PERIOD
        --------------------------------------------
        */

        $total_query = mysqli_prepare(

            $conn,

            "SELECT COALESCE(SUM(amount),0) AS total_fee

             FROM fee_structure

             WHERE period_id=?
             AND group_id=?"

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
        --------------------------------------------
        CHECK WHETHER FEE STRUCTURE EXISTS
        --------------------------------------------
        */

        if($total_fee <= 0){

            $message =
                "No fee structure has been created for this class and academic period.";

            $message_type = "danger";

        }else{


            /*
            ----------------------------------------
            GET ACTIVE STUDENTS IN THIS GROUP
            ----------------------------------------
            */

            $students_stmt = mysqli_prepare(

                $conn,

                "SELECT student_id

                 FROM students

                 WHERE group_id=?
                 AND status='Active'"

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
            ----------------------------------------
            CREATE ACCOUNT FOR EACH STUDENT
            ----------------------------------------
            */

            while(
                $student =
                mysqli_fetch_assoc($students_result)
            ){

                $student_id =
                    (int)$student['student_id'];


                /*
                Check whether account already exists
                */

                $check = mysqli_prepare(

                    $conn,

                    "SELECT student_fee_id

                     FROM student_fees

                     WHERE student_id=?
                     AND period_id=?"

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


                $existing_account =
                    mysqli_fetch_assoc($check_result);


                mysqli_stmt_close($check);



                if($existing_account){

                    $existing++;

                    continue;

                }



                /*
                ------------------------------------
                CREATE NEW STUDENT FEE ACCOUNT
                ------------------------------------
                */

                $previous_balance = 0;
                $amount_paid = 0;
                $balance = $total_fee;
                $status = "Pending";


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

                    VALUES(?,?,?,?,?,?,?)"

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


                if(mysqli_stmt_execute($insert)){

                    $created++;

                }


                mysqli_stmt_close($insert);

            }


            mysqli_stmt_close($students_stmt);



            /*
            ----------------------------------------
            RESULT MESSAGE
            ----------------------------------------
            */

            $message =
                "Student fee accounts generated successfully. "
                .$created.
                " account(s) created, "
                .$existing.
                " account(s) already existed.";

        }

    }

}



/*
====================================================
GET ACADEMIC PERIODS
====================================================
*/

$periods = mysqli_query(

    $conn,

    "SELECT *

     FROM academic_periods

     ORDER BY period_id DESC"

);



/*
====================================================
GET ACTIVE GROUPS
====================================================
*/

$groups = mysqli_query(

    $conn,

    "SELECT *

     FROM academic_groups

     WHERE status='Active'

     ORDER BY group_name"

);



/*
====================================================
GET ACTIVE FEE ITEMS
====================================================
*/

$items = mysqli_query(

    $conn,

    "SELECT *

     FROM fee_items

     WHERE status='Active'

     ORDER BY item_name"

);

?>



<!DOCTYPE html>

<html>

<head>

<title>
Fee Structure Management
</title>


<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">


</head>


<body>


<div class="container-fluid p-4">


<h2 class="mb-4">

💰 Fee Structure Management

</h2>



<?php if($message): ?>

<div class="alert alert-<?=e($message_type);?>">

<?=e($message);?>

</div>

<?php endif; ?>



<!--
====================================================
CREATE FEE STRUCTURE
====================================================
-->

<div class="card mb-4">


<div class="card-header bg-primary text-white">

Create / Update Fee Structure

</div>



<div class="card-body">


<form method="POST">


<div class="row mb-4">


<div class="col-md-4">


<label class="form-label">

Academic Period

</label>


<select

name="period_id"

class="form-control"

required>


<option value="">

Select Period

</option>


<?php while(
$p = mysqli_fetch_assoc($periods)
): ?>


<option value="<?=$p['period_id'];?>">

<?=e($p['academic_year']);?>

-

<?=e($p['period_name']);?>

(<?=e($p['status']);?>)

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

class="form-control"

required>


<option value="">

Select Class

</option>


<?php while(
$g = mysqli_fetch_assoc($groups)
): ?>


<option value="<?=$g['group_id'];?>">

<?=e($g['group_name']);?>

</option>


<?php endwhile; ?>


</select>


</div>


</div>



<h5>

Fee Items

</h5>



<table class="table table-bordered">


<thead>

<tr>

<th>

Fee Item

</th>

<th>

Amount

</th>

</tr>

</thead>



<tbody>


<?php while(
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

class="form-control"

placeholder="Enter amount">


</td>


</tr>


<?php endwhile; ?>


</tbody>


</table>



<button

type="submit"

name="save"

class="btn btn-success">

Save Fee Structure

</button>


</form>


</div>

</div>




<!--
====================================================
GENERATE STUDENT ACCOUNTS
====================================================
-->

<div class="card">


<div class="card-header bg-dark text-white">

Generate Student Fee Accounts

</div>



<div class="card-body">


<p class="text-muted">

After creating a fee structure, generate accounts for all
active students belonging to the selected class.

</p>



<form method="POST">


<div class="row">


<div class="col-md-5">


<label class="form-label">

Academic Period

</label>


<select

name="generate_period_id"

class="form-control"

required>


<option value="">

Select Period

</option>


<?php

$periods_generate = mysqli_query(

    $conn,

    "SELECT *

     FROM academic_periods

     ORDER BY period_id DESC"

);

?>


<?php while(
$p = mysqli_fetch_assoc($periods_generate)
): ?>


<option value="<?=$p['period_id'];?>">

<?=e($p['academic_year']);?>

-

<?=e($p['period_name']);?>

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

class="form-control"

required>


<option value="">

Select Class

</option>


<?php

$groups_generate = mysqli_query(

    $conn,

    "SELECT *

     FROM academic_groups

     WHERE status='Active'

     ORDER BY group_name"

);

?>


<?php while(
$g = mysqli_fetch_assoc($groups_generate)
): ?>


<option value="<?=$g['group_id'];?>">

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
'Generate fee accounts for all active students in this class?'
);">


Generate Accounts

</button>


</div>


</div>


</form>


</div>


</div>


</div>


</body>

</html>