<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


if(isset($_POST['save'])){


    $title = $_POST['title'];

    $value = $_POST['value'];



    $stmt = mysqli_prepare(

        $conn,

        "INSERT INTO website_statistics
        (
            stat_title,
            stat_value
        )

        VALUES
        (?,?)"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "ss",

        $title,

        $value

    );


    mysqli_stmt_execute($stmt);


    $success = "Statistic added successfully.";

}



$stats = mysqli_query(

    $conn,

    "SELECT *
     FROM website_statistics
     ORDER BY stat_id DESC"

);

?>


<!DOCTYPE html>

<html>

<head>

<title>
Statistics Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>


<body>


<div class="container mt-5">


<h2>
Homepage Statistics
</h2>


<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>



<form method="POST">


<label>
Statistic Title
</label>


<input

type="text"

name="title"

class="form-control mb-3"

placeholder="Example: Students"

required

>


<label>
Statistic Value
</label>


<input

type="text"

name="value"

class="form-control mb-3"

placeholder="Example: 5000+"

required

>


<button

name="save"

class="btn btn-primary">

Add Statistic

</button>


</form>



<hr>


<h3>
Current Statistics
</h3>



<table class="table table-bordered">


<tr>

<th>
Title
</th>

<th>
Value
</th>

</tr>



<?php while($row=mysqli_fetch_assoc($stats)): ?>


<tr>

<td>

<?= e($row['stat_title']); ?>

</td>


<td>

<?= e($row['stat_value']); ?>

</td>


</tr>



<?php endwhile; ?>


</table>



</div>


</body>

</html>