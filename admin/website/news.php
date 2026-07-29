<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');



if(isset($_POST['save'])){


$title = $_POST['title'];

$message = $_POST['message'];

$status = $_POST['status'];



$stmt = mysqli_prepare(

$conn,

"INSERT INTO announcements
(
title,
message,
status
)

VALUES
(?,?,?)"

);



mysqli_stmt_bind_param(

$stmt,

"sss",

$title,

$message,

$status

);



mysqli_stmt_execute($stmt);



$success = "News added successfully.";

}



$news = mysqli_query(

$conn,

"SELECT *
FROM announcements
ORDER BY created_at DESC"

);


?>


<!DOCTYPE html>

<html>

<head>

<title>
Website News Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>
Website News
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>



<form method="POST">


<label>
News Title
</label>


<input

type="text"

name="title"

class="form-control mb-3"

required>



<label>
News Content
</label>


<textarea

name="message"

rows="5"

class="form-control mb-3"

required></textarea>



<label>
Status
</label>


<select

name="status"

class="form-control mb-3">


<option value="active">
Active
</option>


<option value="inactive">
Inactive
</option>


</select>



<button

name="save"

class="btn btn-primary">

Publish News

</button>


</form>



<hr>



<h3>
Existing News
</h3>



<table class="table table-bordered">


<tr>

<th>
Title
</th>

<th>
Status
</th>

<th>
Date
</th>

<th>
Action
</th>

</tr>



<?php while($row=mysqli_fetch_assoc($news)): ?>


<tr>


<td>

<?= e($row['title']); ?>

</td>


<td>

<?= e($row['status']); ?>

</td>


<td>

<?= e($row['created_at']); ?>

</td>



<td>


<a

href="edit_news.php?id=<?= $row['id']; ?>"

class="btn btn-warning btn-sm">

Edit

</a>



<a

href="delete_news.php?id=<?= $row['id']; ?>"

onclick="return confirm('Delete this news?')"

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