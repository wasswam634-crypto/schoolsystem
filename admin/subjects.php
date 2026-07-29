<?php

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Subjects";

include '../includes/header.php';
include '../includes/navbar.php';


$message = "";
$message_type = "success";


// Save subject

if(isset($_POST['save_subject'])){


    $group_id = (int)$_POST['group_id'];
    $subject_code = trim($_POST['subject_code']);
    $subject_name = trim($_POST['subject_name']);



    $check = mysqli_prepare(
        $conn,
        "SELECT subject_id
         FROM subjects
         WHERE subject_name=? 
         AND group_id=?"
    );


    mysqli_stmt_bind_param(
        $check,
        "si",
        $subject_name,
        $group_id
    );


    mysqli_stmt_execute($check);


    $result = mysqli_stmt_get_result($check);



    if(mysqli_num_rows($result)>0){


        $message = "This subject already exists for this group.";
        $message_type = "danger";


    }else{


        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO subjects
            (
                group_id,
                subject_code,
                subject_name
            )
            VALUES
            (?,?,?)"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $group_id,
            $subject_code,
            $subject_name
        );


        if(mysqli_stmt_execute($stmt)){


            log_activity(
                $conn,
                $_SESSION['user_id'],
                "Added subject ".$subject_name
            );


            $message = "Subject added successfully.";


        }else{


            $message = "Failed to add subject.";
            $message_type = "danger";

        }

    }

}


?>

<div class="container-fluid">

<div class="row">


<?php include '../includes/admin_sidebar.php'; ?>


<div class="col-md-10 p-4">


<h2 class="mb-4">
Subjects Management
</h2>



<?php if($message): ?>

<div class="alert alert-<?= $message_type ?>">

<?= e($message); ?>

</div>

<?php endif; ?>




<div class="card shadow">


<div class="card-header bg-primary text-white">

Add Subject

</div>


<div class="card-body">


<form method="POST">


<div class="row">


<div class="col-md-3">

<label>
Class / Course
</label>


<select 
name="group_id"
class="form-control"
required>


<option value="">
Select Group
</option>


<?php

$groups=mysqli_query(
$conn,
"SELECT group_id,group_name
 FROM academic_groups
 WHERE status='Active'
 ORDER BY group_name"
);


while($g=mysqli_fetch_assoc($groups)){

?>


<option value="<?= $g['group_id']; ?>">

<?= e($g['group_name']); ?>

</option>


<?php } ?>


</select>


</div>



<div class="col-md-3">

<label>
Subject Code
</label>


<input

type="text"

name="subject_code"

class="form-control"

placeholder="MAT101">


</div>




<div class="col-md-4">

<label>
Subject Name
</label>


<input

type="text"

name="subject_name"

class="form-control"

placeholder="Mathematics"

required>


</div>




<div class="col-md-2 d-flex align-items-end">


<button

name="save_subject"

class="btn btn-success w-100">

Save

</button>


</div>


</div>


</form>


</div>


</div>





<div class="card shadow mt-4">


<div class="card-header bg-dark text-white">

Subjects List

</div>


<div class="card-body">


<table class="table table-bordered">


<tr>

<th>#</th>
<th>Group</th>
<th>Code</th>
<th>Subject</th>

</tr>


<?php


$subjects=mysqli_query(
$conn,

"SELECT 
subjects.*,
academic_groups.group_name

FROM subjects

JOIN academic_groups

ON subjects.group_id=academic_groups.group_id

ORDER BY subject_id DESC"

);


$count=1;


while($row=mysqli_fetch_assoc($subjects)){


?>


<tr>


<td>
<?= $count++; ?>
</td>


<td>
<?= e($row['group_name']); ?>
</td>


<td>
<?= e($row['subject_code']); ?>
</td>


<td>
<?= e($row['subject_name']); ?>
</td>


</tr>


<?php } ?>


</table>


</div>


</div>



</div>

</div>

</div>


<?php include '../includes/footer.php'; ?>