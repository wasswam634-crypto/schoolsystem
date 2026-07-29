<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');



if(isset($_POST['save'])){


    $title = $_POST['title'] ?? '';

    $message = $_POST['message'] ?? '';

    $status = $_POST['status'] ?? 'active';



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



    $success = "Announcement added successfully.";

}




$announcements = mysqli_query(

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
Announcements Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>


<body>


<div class="container mt-5">


<h2>
Homepage News / Announcements
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>



<form method="POST">


<label class="form-label">
Title
</label>


<input

type="text"

name="title"

class="form-control mb-3"

required

>



<label class="form-label">
Message
</label>


<textarea

name="message"

rows="5"

class="form-control mb-3"

required

></textarea>



<label class="form-label">
Status
</label>


<select

name="status"

class="form-control mb-3"

>


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

Add Announcement

</button>


</form>



<hr>



<h3>
Existing Announcements
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

</tr>



<?php while($row=mysqli_fetch_assoc($announcements)): ?>


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


</tr>


<?php endwhile; ?>


</table>



</div>


</body>

</html>