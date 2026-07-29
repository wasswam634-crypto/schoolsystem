<?php

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$page_title = "Fee Items";


include '../includes/header.php';
include '../includes/navbar.php';



$message = "";
$message_type = "success";



// SAVE FEE ITEM

if(isset($_POST['save_item'])){


    $item_name = trim($_POST['item_name']);

    $description = trim($_POST['description']);



    // Check duplicate


    $check = mysqli_prepare(
        $conn,
        "SELECT item_id
         FROM fee_items
         WHERE item_name=?"
    );


    mysqli_stmt_bind_param(
        $check,
        "s",
        $item_name
    );


    mysqli_stmt_execute($check);


    $result = mysqli_stmt_get_result($check);



    if(mysqli_num_rows($result) > 0){


        $message = "This fee item already exists.";

        $message_type = "danger";


    }else{


        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO fee_items
            (
                item_name,
                description,
                status
            )
            VALUES
            (?,?, 'Active')"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $item_name,
            $description
        );



        if(mysqli_stmt_execute($stmt)){


            log_activity(
                $conn,
                $_SESSION['user_id'],
                "Created fee item ".$item_name
            );


            $message = "Fee item added successfully.";


        }else{


            $message = "Failed to add fee item.";

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

Fee Items

</h2>




<?php if($message): ?>

<div class="alert alert-<?= $message_type ?>">

<?= e($message); ?>

</div>

<?php endif; ?>




<div class="card shadow">


<div class="card-header bg-primary text-white">

Add New Fee Item

</div>



<div class="card-body">


<form method="POST">


<div class="row">


<div class="col-md-5">


<label class="form-label">

Fee Name

</label>


<input

type="text"

name="item_name"

class="form-control"

placeholder="Example: Tuition"

required>


</div>




<div class="col-md-5">


<label class="form-label">

Description

</label>


<input

type="text"

name="description"

class="form-control"

placeholder="Optional description">


</div>




<div class="col-md-2 d-flex align-items-end">


<button

class="btn btn-success w-100"

name="save_item">

Save

</button>


</div>



</div>


</form>


</div>


</div>





<div class="card shadow mt-4">


<div class="card-header bg-dark text-white">

Existing Fee Items

</div>



<div class="card-body">


<table class="table table-bordered">


<thead>

<tr>

<th>#</th>

<th>Fee Name</th>

<th>Description</th>

<th>Status</th>

</tr>

</thead>



<tbody>


<?php


$items = mysqli_query(

$conn,

"SELECT *

FROM fee_items

ORDER BY item_id DESC"

);



$count = 1;


while($row=mysqli_fetch_assoc($items)){


?>


<tr>


<td>

<?= $count++; ?>

</td>



<td>

<?= e($row['item_name']); ?>

</td>



<td>

<?= e($row['description']); ?>

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