<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');




if(isset($_POST['save'])){


$name = $_POST['name'];

$position = $_POST['position'];

$bio = $_POST['bio'];


$photo = '';



if(!empty($_FILES['photo']['name'])){


    $photo = time().'_'.basename($_FILES['photo']['name']);


    move_uploaded_file(
        $_FILES['photo']['tmp_name'],
        "../../uploads/staff/".$photo
    );


}



$stmt=mysqli_prepare(

$conn,

"INSERT INTO website_staff
(
name,
position,
bio,
photo
)
VALUES
(?,?,?,?)"

);



mysqli_stmt_bind_param(

$stmt,

"ssss",

$name,

$position,

$bio,

$photo

);



mysqli_stmt_execute($stmt);



$success="Staff member added successfully.";


}



$staff=mysqli_query(

$conn,

"SELECT *
FROM website_staff
ORDER BY staff_id DESC"

);


?>


<!DOCTYPE html>

<html>

<head>

<title>
Staff Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>
Staff Management
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?=e($success);?>

</div>

<?php endif; ?>



<form method="POST" enctype="multipart/form-data">


<label>
Name
</label>


<input

type="text"

name="name"

class="form-control mb-3"

required>



<label>
Position
</label>


<input

type="text"

name="position"

class="form-control mb-3"

placeholder="Head Teacher"

required>



<label>
Biography
</label>


<textarea

name="bio"

class="form-control mb-3"

rows="4">

</textarea>



<label>
Photo
</label>


<input

type="file"

name="photo"

class="form-control mb-3">



<button

name="save"

class="btn btn-primary">

Add Staff

</button>


</form>



<hr>



<h3>
Existing Staff
</h3>



<table class="table table-bordered">


<tr>

<th>
Photo
</th>

<th>
Name
</th>

<th>
Position
</th>

<th>
Action
</th>

</tr>



<?php while($row=mysqli_fetch_assoc($staff)): ?>


<tr>


<td>

<?php if(!empty($row['photo'])): ?>


<img

src="../../uploads/staff/<?=e($row['photo']);?>"

width="80">


<?php endif; ?>

</td>



<td>

<?=e($row['name']);?>

</td>



<td>

<?=e($row['position']);?>

</td>



<td>


<a

href="edit_staff.php?id=<?=$row['staff_id'];?>"

class="btn btn-warning btn-sm">

Edit

</a>



<a

href="delete_staff.php?id=<?=$row['staff_id'];?>"

onclick="return confirm('Delete this staff member?')"

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