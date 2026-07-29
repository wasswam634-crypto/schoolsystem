<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message="";


// GET ACTIVE PERIODS

$periods=mysqli_query(
    $conn,
    "SELECT *
     FROM academic_periods
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



// GET FEE ITEMS

$items=mysqli_query(
    $conn,
    "SELECT *
     FROM fee_items
     WHERE status='Active'
     ORDER BY item_name"
);




// SAVE FEE STRUCTURE

if(isset($_POST['save'])){


    $period_id = $_POST['period_id'];
    $group_id  = $_POST['group_id'];


    $item_ids = $_POST['item_id'];
    $amounts  = $_POST['amount'];



    $success=true;



    for($i=0; $i<count($item_ids); $i++){


        if(empty($amounts[$i])){

            continue;

        }



        $stmt=mysqli_prepare(
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

amount=VALUES(amount)"
        );



        mysqli_stmt_bind_param(
            $stmt,
            "iiid",

            $period_id,
            $group_id,
            $item_ids[$i],
            $amounts[$i]

        );



        if(!mysqli_stmt_execute($stmt)){

            $success=false;

        }


    }



    if($success){

        $message="Fee structure saved successfully.";

    }
    else{

        $message="Some fees could not be saved.";

    }



}



?>



<!DOCTYPE html>

<html>

<head>


<title>
Fee Structure
</title>


<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>



<body>


<div class="container-fluid p-4">


<h2>
💰 Fee Structure Management
</h2>



<?php if($message): ?>

<div class="alert alert-success">

<?=e($message);?>

</div>

<?php endif; ?>




<div class="card">


<div class="card-header bg-primary text-white">

Create Fee Structure

</div>



<div class="card-body">



<form method="POST">



<div class="row mb-4">


<div class="col-md-4">


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

(<?=e($p['status']);?>)

</option>


<?php endwhile; ?>


</select>


</div>





<div class="col-md-4">


<label>
Class / Group
</label>


<select 
name="group_id"
class="form-control"
required>


<option value="">
Select Class
</option>


<?php while($g=mysqli_fetch_assoc($groups)): ?>


<option value="<?= $g['group_id']; ?>">


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


<tr>

<th>
Fee Item
</th>


<th>
Amount
</th>

</tr>



<?php while($item=mysqli_fetch_assoc($items)): ?>


<tr>


<td>


<?=e($item['item_name']);?>


<input 
type="hidden"
name="item_id[]"
value="<?= $item['item_id']; ?>">


</td>




<td>


<input

type="number"

name="amount[]"

class="form-control"

placeholder="Enter amount">


</td>



</tr>


<?php endwhile; ?>


</table>





<button

name="save"

class="btn btn-success">

Save Fee Structure

</button>



</form>



</div>


</div>



</div>


</body>


</html>