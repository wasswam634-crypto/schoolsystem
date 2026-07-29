<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$id=$_GET['id'];



$result=mysqli_query(

$conn,

"SELECT *
 FROM website_testimonials
 WHERE testimonial_id=$id"

);



$data=mysqli_fetch_assoc($result);



if(isset($_POST['update'])){


$name=$_POST['name'];

$role=$_POST['role'];

$message=$_POST['message'];



$stmt=mysqli_prepare(

$conn,

"UPDATE website_testimonials

SET name=?,
role=?,
message=?

WHERE testimonial_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"sssi",

$name,

$role,

$message,

$id

);



mysqli_stmt_execute($stmt);



header("Location: testimonials.php");

exit;

}

?>


<div class="container mt-5">


<h2>
Edit Testimonial
</h2>



<form method="POST">


<input

type="text"

name="name"

class="form-control mb-3"

value="<?=e($data['name']);?>">



<input

type="text"

name="role"

class="form-control mb-3"

value="<?=e($data['role']);?>">



<textarea

name="message"

class="form-control mb-3"

rows="5">

<?=e($data['message']);?>

</textarea>



<button

name="update"

class="btn btn-success">

Update

</button>



</form>


</div>