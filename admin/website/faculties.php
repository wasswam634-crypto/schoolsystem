<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');



if(isset($_POST['save'])){


    $name = $_POST['name'];

    $description = $_POST['description'];



    $stmt = mysqli_prepare(

        $conn,

        "INSERT INTO faculties
        (
            name,
            description
        )

        VALUES
        (?,?)"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "ss",

        $name,

        $description

    );


    mysqli_stmt_execute($stmt);



    $success = "Faculty added successfully.";

}




$faculties = mysqli_query(

    $conn,

    "SELECT *
     FROM faculties
     ORDER BY faculty_id DESC"

);


?>


<!DOCTYPE html>

<html>

<head>

<title>
Faculty Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>
Faculty Management
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>



<form method="POST">


<label>
Faculty Name
</label>


<input

type="text"

name="name"

class="form-control mb-3"

required>


<label>
Description
</label>


<textarea

name="description"

class="form-control mb-3"

rows="4">

</textarea>


<button

name="save"

class="btn btn-primary">

Add Faculty

</button>


</form>



<hr>



<h3>
Existing Faculties
</h3>



<table class="table table-bordered">


<tr>

<th>
Name
</th>


<th>
Description
</th>


<th>
Action
</th>


</tr>



<?php while($row=mysqli_fetch_assoc($faculties)): ?>


<tr>


<td>

<?= e($row['name']); ?>

</td>



<td>

<?= e($row['description']); ?>

</td>



<td>


<a

href="edit_faculty.php?id=<?= $row['faculty_id']; ?>"

class="btn btn-warning btn-sm">

Edit

</a>



<a

href="delete_faculty.php?id=<?= $row['faculty_id']; ?>"

onclick="return confirm('Delete this faculty?')"

class="btn btn-danger btn-sm">

Delete

</a>



</td>


</tr>



<?php endwhile; ?>


</table>



</div>


</body>


</html>