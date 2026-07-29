<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$id=$_GET['id'];



$result=mysqli_query(

$conn,

"SELECT *
FROM announcements
WHERE id=$id"

);



$news=mysqli_fetch_assoc($result);



if(isset($_POST['update'])){


$title=$_POST['title'];

$message=$_POST['message'];

$status=$_POST['status'];



$stmt=mysqli_prepare(

$conn,

"UPDATE announcements

SET title=?,
message=?,
status=?

WHERE id=?"

);



mysqli_stmt_bind_param(

$stmt,

"sssi",

$title,

$message,

$status,

$id

);



mysqli_stmt_execute($stmt);



header("Location: news.php");

exit;

}


?>


<div class="container mt-5">


<h2>
Edit News
</h2>


<form method="POST">


<input

type="text"

name="title"

class="form-control mb-3"

value="<?= e($news['title']); ?>">



<textarea

name="message"

class="form-control mb-3"

rows="5">

<?= e($news['message']); ?>

</textarea>



<select

name="status"

class="form-control mb-3">


<option value="active"

<?= $news['status']=="active"?'selected':'';?>>

Active

</option>


<option value="inactive"

<?= $news['status']=="inactive"?'selected':'';?>>

Inactive

</option>


</select>



<button

name="update"

class="btn btn-success">

Update News

</button>


</form>


</div>