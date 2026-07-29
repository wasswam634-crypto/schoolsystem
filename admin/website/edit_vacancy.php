<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$id=$_GET['id'];



$result=mysqli_query(

$conn,

"SELECT *
FROM vacancies
WHERE vacancy_id=$id"

);



$data=mysqli_fetch_assoc($result);



if(isset($_POST['update'])){


$title=$_POST['title'];

$department=$_POST['department'];

$description=$_POST['description'];

$deadline=$_POST['deadline'];

$status=$_POST['status'];



$stmt=mysqli_prepare(

$conn,

"UPDATE vacancies

SET title=?,
department=?,
description=?,
deadline=?,
status=?

WHERE vacancy_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"sssssi",

$title,

$department,

$description,

$deadline,

$status,

$id

);



mysqli_stmt_execute($stmt);



header("Location: vacancies.php");

exit;


}

?>


<div class="container mt-5">


<h2>
Edit Vacancy
</h2>



<form method="POST">


<input

name="title"

class="form-control mb-3"

value="<?=e($data['title']);?>">



<input

name="department"

class="form-control mb-3"

value="<?=e($data['department']);?>">



<textarea

name="description"

class="form-control mb-3">

<?=e($data['description']);?>

</textarea>



<input

type="date"

name="deadline"

class="form-control mb-3"

value="<?=$data['deadline'];?>">



<select

name="status"

class="form-control mb-3">


<option value="open"

<?=$data['status']=="open"?'selected':'';?>>

Open

</option>


<option value="closed"

<?=$data['status']=="closed"?'selected':'';?>>

Closed

</option>


</select>



<button

name="update"

class="btn btn-success">

Update

</button>


</form>


</div>