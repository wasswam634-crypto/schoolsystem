<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


$message="";


// SAVE PERIOD

if(isset($_POST['save_period'])){


$academic_year = $_POST['academic_year'];
$period_name   = $_POST['period_name'];
$start_date    = $_POST['start_date'];
$end_date      = $_POST['end_date'];



// close existing active periods

mysqli_query(
$conn,
"UPDATE academic_periods 
 SET status='Closed'"
);



// insert new period

$stmt=mysqli_prepare(
$conn,
"INSERT INTO academic_periods
(
academic_year,
period_name,
start_date,
end_date,
status
)
VALUES(?,?,?,?, 'Active')"
);


mysqli_stmt_bind_param(
$stmt,
"ssss",
$academic_year,
$period_name,
$start_date,
$end_date
);


mysqli_stmt_execute($stmt);


$message="Academic period created successfully";


}



// CHANGE STATUS

if(isset($_GET['close'])){


$id=(int)$_GET['close'];


mysqli_query(
$conn,
"UPDATE academic_periods
SET status='Closed'
WHERE period_id=$id"
);


header("Location: academic_periods.php");
exit;

}



// GET PERIODS

$periods=mysqli_query(
$conn,
"SELECT *
FROM academic_periods
ORDER BY period_id DESC"
);


?>


<!DOCTYPE html>

<html>

<head>

<title>Academic Periods</title>


<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container p-4">


<h2>
📅 Academic Period Management
</h2>



<?php if($message): ?>

<div class="alert alert-success">

<?=e($message);?>

</div>

<?php endif; ?>




<div class="card mb-4">


<div class="card-header bg-primary text-white">

Create New Period

</div>



<div class="card-body">


<form method="POST">


<div class="row">


<div class="col-md-3">

<label>
Academic Year
</label>

<input 
type="text"
name="academic_year"
class="form-control"
value="<?=date('Y');?>"
required>

</div>




<div class="col-md-3">

<label>
Period Name
</label>

<input 
type="text"
name="period_name"
class="form-control"
placeholder="Example: Semester 1"
required>

</div>




<div class="col-md-3">

<label>
Start Date
</label>

<input 
type="date"
name="start_date"
class="form-control"
required>

</div>




<div class="col-md-3">

<label>
End Date
</label>

<input 
type="date"
name="end_date"
class="form-control"
required>

</div>



</div>



<br>


<button 
name="save_period"
class="btn btn-success">

Save Period

</button>


</form>


</div>


</div>





<div class="card">


<div class="card-header bg-dark text-white">

Existing Periods

</div>



<div class="card-body">


<table class="table table-bordered">


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
Action
</th>

</tr>



<?php while($row=mysqli_fetch_assoc($periods)): ?>


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

<?=e($row['status']);?>

</td>


<td>


<?php if($row['status']=="Active"): ?>


<a 
href="?close=<?=$row['period_id'];?>"
class="btn btn-danger btn-sm">

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


</table>


</div>


</div>


</div>


</body>

</html>