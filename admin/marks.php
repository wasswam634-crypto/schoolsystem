<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message = "";


// GET ACTIVE PERIOD

$period_query = mysqli_query(
    $conn,
    "SELECT period_id, academic_year, period_name
     FROM academic_periods
     WHERE status='Active'
     LIMIT 1"
);


$active_period = mysqli_fetch_assoc($period_query);


if(!$active_period){

    die("No active academic period. Please open a period first.");

}


$period_id = $active_period['period_id'];





// GET SUBJECTS

$subjects = [];


$subject_result = mysqli_query(
    $conn,
    "SELECT subject_id, subject_name
     FROM subjects
     ORDER BY subject_name"
);



while($row=mysqli_fetch_assoc($subject_result)){

    $subjects[]=$row;

}





// LOAD STUDENTS

$students = [];


if(isset($_POST['load_students'])){


    $class = $_POST['class'];



    $student_stmt = mysqli_prepare(
        $conn,
        "SELECT student_id, full_name
         FROM students
         WHERE class=?
         ORDER BY full_name"
    );



    mysqli_stmt_bind_param(
        $student_stmt,
        "s",
        $class
    );



    mysqli_stmt_execute($student_stmt);



    $student_result=mysqli_stmt_get_result($student_stmt);



    while($row=mysqli_fetch_assoc($student_result)){

        $students[]=$row;

    }



}





// SAVE MARKS

if(isset($_POST['save_marks'])){


    foreach($_POST['marks'] as $student_id=>$student_marks){



        foreach($student_marks as $subject_id=>$mark){



            if($mark==""){

                continue;

            }




            // CHECK IF MARK EXISTS

            $check = mysqli_prepare(
                $conn,
                "SELECT mark_id
                 FROM marks
                 WHERE student_id=?
                 AND subject_id=?
                 AND period_id=?"
            );


            mysqli_stmt_bind_param(
                $check,
                "iii",
                $student_id,
                $subject_id,
                $period_id
            );


            mysqli_stmt_execute($check);


            $check_result=mysqli_stmt_get_result($check);





            if(mysqli_num_rows($check_result)>0){



                // UPDATE EXISTING MARK

                $update=mysqli_prepare(
                    $conn,
                    "UPDATE marks
                     SET marks=?
                     WHERE student_id=?
                     AND subject_id=?
                     AND period_id=?"
                );



                mysqli_stmt_bind_param(
                    $update,
                    "iiii",
                    $mark,
                    $student_id,
                    $subject_id,
                    $period_id
                );



                mysqli_stmt_execute($update);



            }else{



                // INSERT NEW MARK


                $insert=mysqli_prepare(
                    $conn,
                    "INSERT INTO marks
                    (
                        student_id,
                        subject_id,
                        period_id,
                        marks
                    )
                    VALUES(?,?,?,?)"
                );



                mysqli_stmt_bind_param(
                    $insert,
                    "iiii",
                    $student_id,
                    $subject_id,
                    $period_id,
                    $mark
                );



                mysqli_stmt_execute($insert);



            }



        }


    }



    $message="Marks saved successfully";


}



?>

<!DOCTYPE html>

<html>

<head>

<title>
Marks Management
</title>


<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container-fluid p-4">


<h2>
📝 Marks Entry
</h2>



<?php if($message): ?>

<div class="alert alert-success">

<?=e($message);?>

</div>

<?php endif; ?>



<div class="alert alert-info">

Current Period:

<strong>

<?=e($active_period['academic_year']);?>

-

<?=e($active_period['period_name']);?>

</strong>

</div>







<form method="POST">


<div class="row mb-4">


<div class="col-md-4">


<label>
Class
</label>


<input 
type="text"
name="class"
class="form-control"
placeholder="Example: S1 East"
required>


</div>



<div class="col-md-3">


<br>


<button
name="load_students"
class="btn btn-primary">

Load Students

</button>


</div>


</div>


</form>







<?php if(!empty($students)): ?>


<form method="POST">


<div class="card">


<div class="card-header bg-success text-white">

Enter Marks

</div>


<div class="card-body">



<table class="table table-bordered">


<thead class="table-dark">


<tr>


<th>
Student
</th>


<?php foreach($subjects as $subject): ?>


<th>
<?=e($subject['subject_name']);?>
</th>


<?php endforeach; ?>


</tr>


</thead>



<tbody>



<?php foreach($students as $student): ?>


<tr>


<td>

<?=e($student['full_name']);?>

</td>




<?php foreach($subjects as $subject): ?>


<td>


<input

type="number"

class="form-control"

min="0"

max="100"

name="marks[<?=$student['student_id'];?>][<?=$subject['subject_id'];?>]"


>


</td>


<?php endforeach; ?>



</tr>


<?php endforeach; ?>



</tbody>


</table>




<button

type="submit"

name="save_marks"

class="btn btn-success">

Save Marks

</button>



</div>


</div>


</form>


<?php endif; ?>



</div>


</body>


</html>