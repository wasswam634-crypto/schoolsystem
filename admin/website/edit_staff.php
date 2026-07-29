<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$id = $_GET['id'];



$result = mysqli_query(

$conn,

"SELECT *
 FROM website_staff
 WHERE staff_id=$id"

);



$staff = mysqli_fetch_assoc($result);



if(isset($_POST['update'])){


$name = $_POST['name'];

$position = $_POST['position'];

$bio = $_POST['bio'];



$photo = $staff['photo'];



if(!empty($_FILES['photo']['name'])){


$photo = time().'_'.$_FILES['photo']['name'];



move_uploaded_file(

$_FILES['photo']['tmp_name'],

"../../uploads/website/".$photo

);


}



$stmt=mysqli_prepare(

$conn,

"UPDATE website_staff

SET name=?,
position=?,
bio=?,
photo=?

WHERE staff_id=?"

);



mysqli_stmt_bind_param(

$stmt,

"ssssi",

$name,

$position,

$bio,

$photo,

$id

);



mysqli_stmt_execute($stmt);



header("Location: staff.php");

exit;

}

?>


<div class="container mt-5">


<h2>
Edit Staff Member
</h2>


<form method="POST" enctype="multipart/form-data">


<label>
Name
</label>


<input

type="text"

name="name"

class="form-control mb-3"

value="<?=e($staff['name']);?>">


<label>
Position
</label>


<input

type="text"

name="position"

class="form-control mb-3"

value="<?=e($staff['position']);?>">



<label>
Biography
</label>


<textarea

name="bio"

class="form-control mb-3"

rows="5">

<?=e($staff['bio']);?>

</textarea>



<label>
Change Photo
</label>


<input

type="file"

name="photo"

class="form-control mb-3">



<?php if(!empty($staff['photo'])): ?>


<img

src="../../uploads/website/<?=e($staff['photo']);?>"

width="150">


<?php endif; ?>



<br><br>


<button

name="update"

class="btn btn-success">

Update Staff

</button>


</form>


</div>