<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message="";


// GET PERIODS

$periods=mysqli_query(
    $conn,

    "SELECT *
     FROM academic_periods
     WHERE status='Active'
     ORDER BY period_id DESC"
);



// GET GROUPS

$groups=mysqli_query(
    $conn,

    "SELECT *
     FROM academic_groups
     WHERE status='Active'
     ORDER BY group_name"
);



if(isset($_POST['generate'])){


    $period_id = $_POST['period_id'];
    $group_id  = $_POST['group_id'];



    /*
    GET FEE TOTAL
    */


    $fee_query=mysqli_prepare(
        $conn,

        "SELECT SUM(amount) AS total
         FROM fee_structure
         WHERE period_id=?
         AND group_id=?"
    );


    mysqli_stmt_bind_param(
        $fee_query,
        "ii",
        $period_id,
        $group_id
    );


    mysqli_stmt_execute($fee_query);


    $fee_result=mysqli_stmt_get_result($fee_query);


    $fee=mysqli_fetch_assoc($fee_result);



    $total_amount=$fee['total'] ?? 0;



    if($total_amount<=0){


        $message="No fee structure found for this class.";


    }

    else{


        /*
        GET STUDENTS
        */


        $students=mysqli_prepare(
            $conn,

            "SELECT student_id
             FROM students
             WHERE group_id=?
             AND status='Active'"
        );


        mysqli_stmt_bind_param(
            $students,
            "i",
            $group_id
        );


        mysqli_stmt_execute($students);


        $student_result=mysqli_stmt_get_result($students);





        while($student=mysqli_fetch_assoc($student_result)){



            /*
            CHECK IF ALREADY CREATED
            */


            $check=mysqli_prepare(
                $conn,

                "SELECT student_fee_id
                 FROM student_fees
                 WHERE student_id=?
                 AND period_id=?"
            );


            mysqli_stmt_bind_param(
                $check,
                "ii",
                $student['student_id'],
                $period_id
            );


            mysqli_stmt_execute($check);


            $exists=mysqli_stmt_get_result($check);




            if(mysqli_num_rows($exists)>0){

                continue;

            }





            /*
            CREATE ACCOUNT
            */


            $insert=mysqli_prepare(
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



            $previous=0;
            $paid=0;
            $balance=$total_amount;
            $status="Pending";



            mysqli_stmt_bind_param(
                $insert,

                "iidddds",

                $student['student_id'],
                $period_id,
                $total_amount,
                $previous,
                $paid,
                $balance,
                $status
            );



            mysqli_stmt_execute($insert);



        }



        $message="Student fees generated successfully.";

    }


}


?>



<!DOCTYPE html>

<html>

<head>

<title>
Generate Student Fees
</title>


<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container-fluid p-4">


<h2>
🧾 Generate Student Fees
</h2>



<?php if($message): ?>

<div class="alert alert-info">

<?=e($message);?>

</div>

<?php endif; ?>





<div class="card">


<div class="card-header bg-success text-white">

Generate Accounts

</div>



<div class="card-body">


<form method="POST">



<div class="row">



<div class="col-md-5">


<label>
Academic Period
</label>


<select 
name="period_id"
class="form-control"
required>


<option value="">
Select Period
</option>


<?php while($p=mysqli_fetch_assoc($periods)): ?>


<option value="<?= $p['period_id']; ?>">

<?=e($p['academic_year']);?>
-
<?=e($p['period_name']);?>

</option>


<?php endwhile; ?>


</select>


</div>





<div class="col-md-5">


<label>
Class / Group
</label>


<select 
name="group_id"
class="form-control"
required>


<option>
Select Class
</option>


<?php while($g=mysqli_fetch_assoc($groups)): ?>


<option value="<?= $g['group_id']; ?>">


<?=e($g['group_name']);?>


</option>


<?php endwhile; ?>


</select>


</div>





<div class="col-md-2 mt-4">


<button

name="generate"

class="btn btn-primary w-100">

Generate

</button>


</div>



</div>



</form>


</div>


</div>



</div>


</body>

</html>