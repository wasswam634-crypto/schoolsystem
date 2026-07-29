<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$id = $_GET['id'];



$result = mysqli_query(

$conn,

"SELECT *
FROM faculties
WHERE faculty_id=$id"

);



$faculty=mysqli_fetch_assoc($result);



if(isset($_POST['update'])){


$name=$_POST['name'];

$description=$_POST['description'];



$stmt=mysqli_prepare(

$conn,

"UPDATE faculties
SET name=?,
description=?
WHERE faculty_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"ssi",

$name,

$description,

$id

);



mysqli_stmt_execute($stmt);



header("Location: faculties.php");

exit;


}


?>


<div class="container mt-5">


<h2>
Edit Faculty
</h2>


<form method="POST">


<input

type="text"

name="name"

class="form-control mb-3"

value="<?= e($faculty['name']); ?>">



<textarea

name="description"

class="form-control mb-3">

<?= e($faculty['description']); ?>

</textarea>



<button

name="update"

class="btn btn-success">

Update Faculty

</button>



</form>


</div>