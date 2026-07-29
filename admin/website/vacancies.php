<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');



if(isset($_POST['save'])){


$title = $_POST['title'];

$department = $_POST['department'];

$description = $_POST['description'];

$deadline = $_POST['deadline'];

$status = $_POST['status'];



$stmt = mysqli_prepare(

$conn,

"INSERT INTO vacancies

(
title,
department,
description,
deadline,
status
)

VALUES
(?,?,?,?,?)"

);



mysqli_stmt_bind_param(

$stmt,

"sssss",

$title,

$department,

$description,

$deadline,

$status

);



mysqli_stmt_execute($stmt);



$success="Vacancy added successfully.";

}




$vacancies=mysqli_query(

$conn,

"SELECT *
FROM vacancies
ORDER BY vacancy_id DESC"

);


?>


<!DOCTYPE html>

<html>

<head>

<title>
Vacancy Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>
Vacancy Management
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?=e($success);?>

</div>

<?php endif; ?>



<form method="POST">


<label>
Job Title
</label>


<input

type="text"

name="title"

class="form-control mb-3"

required>



<label>
Department
</label>


<input

type="text"

name="department"

class="form-control mb-3"

required>



<label>
Description
</label>


<textarea

name="description"

class="form-control mb-3"

rows="5"

required></textarea>



<label>
Deadline
</label>


<input

type="date"

name="deadline"

class="form-control mb-3"

required>



<label>
Status
</label>


<select

name="status"

class="form-control mb-3">


<option value="open">

Open

</option>


<option value="closed">

Closed

</option>


</select>



<button

name="save"

class="btn btn-primary">

Add Vacancy

</button>


</form>



<hr>



<h3>
Existing Vacancies
</h3>



<table class="table table-bordered">


<tr>

<th>
Title
</th>

<th>
Department
</th>

<th>
Deadline
</th>

<th>
Status
</th>

<th>
Action
</th>

</tr>



<?php while($row=mysqli_fetch_assoc($vacancies)): ?>


<tr>


<td>

<?=e($row['title']);?>

</td>


<td>

<?=e($row['department']);?>

</td>


<td>

<?=e($row['deadline']);?>

</td>


<td>

<?=e($row['status']);?>

</td>


<td>


<a

href="edit_vacancy.php?id=<?=$row['vacancy_id'];?>"

class="btn btn-warning btn-sm">

Edit

</a>



<a

href="delete_vacancy.php?id=<?=$row['vacancy_id'];?>"

onclick="return confirm('Delete this vacancy?')"

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