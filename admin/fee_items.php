<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message="";


// ADD FEE ITEM

if(isset($_POST['save'])){


    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);


    if($item_name==""){

        $message="Fee name is required";

    }
    else{


        $stmt=mysqli_prepare(
            $conn,
            "INSERT INTO fee_items
            (
                item_name,
                description
            )
            VALUES(?,?)"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $item_name,
            $description
        );


        mysqli_stmt_execute($stmt);


        $message="Fee item added successfully";

    }


}



// GET ITEMS

$result=mysqli_query(
    $conn,
    "SELECT *
     FROM fee_items
     ORDER BY item_id DESC"
);


?>


<!DOCTYPE html>

<html>

<head>

<title>Fee Items</title>


<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container-fluid p-4">


<h2>
💰 Fee Items Management
</h2>



<?php if($message): ?>

<div class="alert alert-success">

<?=e($message);?>

</div>

<?php endif; ?>




<div class="card mb-4">


<div class="card-header bg-primary text-white">

Add New Fee Item

</div>


<div class="card-body">


<form method="POST">


<div class="row">


<div class="col-md-4">


<label>
Fee Name
</label>


<input 
type="text"
name="item_name"
class="form-control"
placeholder="Example: Tuition"
required>


</div>



<div class="col-md-6">


<label>
Description
</label>


<input 
type="text"
name="description"
class="form-control"
placeholder="Optional description">


</div>



<div class="col-md-2 mt-4">


<button
name="save"
class="btn btn-success w-100">

Save

</button>


</div>


</div>


</form>


</div>


</div>






<div class="card">


<div class="card-header bg-dark text-white">

Existing Fee Items

</div>


<div class="card-body">


<table class="table table-bordered">


<tr>

<th>
Name
</th>

<th>
Description
</th>

<th>
Status
</th>

</tr>



<?php while($row=mysqli_fetch_assoc($result)): ?>


<tr>


<td>

<?=e($row['item_name']);?>

</td>


<td>

<?=e($row['description']);?>

</td>


<td>

<?=e($row['status']);?>

</td>


</tr>


<?php endwhile; ?>


</table>


</div>


</div>



</div>


</body>

</html>