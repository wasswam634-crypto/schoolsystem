<?php

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Academic Groups";

include '../includes/header.php';
include '../includes/navbar.php';


$message = "";
$message_type = "success";


// Save group

if(isset($_POST['save_group'])){


    $group_name = trim($_POST['group_name']);
    $group_type = trim($_POST['group_type']);


    // Check duplicate

    $check = mysqli_prepare(
        $conn,
        "SELECT group_id 
         FROM academic_groups
         WHERE group_name=?"
    );


    mysqli_stmt_bind_param(
        $check,
        "s",
        $group_name
    );


    mysqli_stmt_execute($check);


    $result = mysqli_stmt_get_result($check);



    if(mysqli_num_rows($result) > 0){


        $message = "This group already exists.";
        $message_type = "danger";


    }else{


        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO academic_groups
            (
                group_name,
                group_type,
                status
            )
            VALUES
            (?,?, 'Active')"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $group_name,
            $group_type
        );



        if(mysqli_stmt_execute($stmt)){


            log_activity(
                $conn,
                $_SESSION['user_id'],
                "Created academic group ".$group_name
            );


            $message = "Academic group created successfully.";


        }else{


            $message = "Failed to create group.";
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
Academic Groups
</h2>



<?php if($message): ?>

<div class="alert alert-<?= $message_type ?>">

<?= e($message); ?>

</div>

<?php endif; ?>





<div class="card shadow">


<div class="card-header bg-primary text-white">

Add Class / Course / Department

</div>


<div class="card-body">


<form method="POST">


<div class="row">


<div class="col-md-5">

<label class="form-label">

Group Name

</label>


<input

type="text"

name="group_name"

class="form-control"

placeholder="Example: Senior 1"

required>

</div>




<div class="col-md-4">


<label class="form-label">

Group Type

</label>


<select

name="group_type"

class="form-control"

required>


<option value="Class">
Class
</option>


<option value="Course">
Course
</option>


<option value="Department">
Department
</option>


</select>


</div>




<div class="col-md-3 d-flex align-items-end">


<button

class="btn btn-success w-100"

name="save_group">

Save Group

</button>


</div>


</div>


</form>


</div>


</div>






<div class="card shadow mt-4">


<div class="card-header bg-dark text-white">

Existing Groups

</div>



<div class="card-body">


<table class="table table-bordered">


<thead>

<tr>

<th>#</th>

<th>Group Name</th>

<th>Type</th>

<th>Status</th>

</tr>

</thead>


<tbody>


<?php


$groups = mysqli_query(
    $conn,
    "SELECT *
     FROM academic_groups
     ORDER BY group_id DESC"
);


$count = 1;


while($row=mysqli_fetch_assoc($groups)){


?>


<tr>


<td>
<?= $count++; ?>
</td>


<td>
<?= e($row['group_name']); ?>
</td>


<td>
<?= e($row['group_type']); ?>
</td>


<td>


<?php if($row['status']=="Active"){ ?>


<span class="badge bg-success">
Active
</span>


<?php }else{ ?>


<span class="badge bg-secondary">
Inactive
</span>


<?php } ?>


</td>


</tr>


<?php } ?>


</tbody>


</table>


</div>


</div>




</div>


</div>

</div>



<?php include '../includes/footer.php'; ?>