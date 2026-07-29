<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');



if(isset($_POST['save'])){


    $name = $_POST['name'];

    $role = $_POST['role'];

    $message = $_POST['message'];



    $stmt = mysqli_prepare(

        $conn,

        "INSERT INTO website_testimonials
        (
            name,
            role,
            message
        )

        VALUES
        (?,?,?)"

    );


    mysqli_stmt_bind_param(

        $stmt,

        "sss",

        $name,

        $role,

        $message

    );


    mysqli_stmt_execute($stmt);



    $success = "Testimonial added successfully.";

}




$testimonials = mysqli_query(

    $conn,

    "SELECT *
     FROM website_testimonials
     ORDER BY testimonial_id DESC"

);

?>


<!DOCTYPE html>

<html>

<head>

<title>
Testimonials Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>
Student Testimonials
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>



<form method="POST">


<label>
Name
</label>


<input

type="text"

name="name"

class="form-control mb-3"

required>



<label>
Role
</label>


<input

type="text"

name="role"

class="form-control mb-3"

placeholder="Student / Alumni"

required>



<label>
Message
</label>


<textarea

name="message"

rows="5"

class="form-control mb-3"

required></textarea>



<button

name="save"

class="btn btn-primary">

Add Testimonial

</button>


</form>



<hr>



<h3>
Existing Testimonials
</h3>



<table class="table table-bordered">


<tr>

<th>Name</th>

<th>Role</th>

<th>Message</th>

<th>Action</th>

</tr>



<?php while($row=mysqli_fetch_assoc($testimonials)): ?>


<tr>


<td>

<?= e($row['name']); ?>

</td>


<td>

<?= e($row['role']); ?>

</td>


<td>

<?= e($row['message']); ?>

</td>



<td>


<a

href="edit_testimonial.php?id=<?= $row['testimonial_id']; ?>"

class="btn btn-warning btn-sm">

Edit

</a>



<a

href="delete_testimonial.php?id=<?= $row['testimonial_id']; ?>"

onclick="return confirm('Delete this testimonial?')"

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