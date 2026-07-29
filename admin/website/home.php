<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');



$sections = [

    'hero',

    'principal_message',

    'why_choose_us',

    'academics',

    'call_to_action'

];



if(isset($_POST['save'])){


foreach($sections as $section){



$title = $_POST[$section.'_title'];

$content = $_POST[$section.'_content'];



$image = null;



if(!empty($_FILES[$section.'_image']['name'])){


$image = time().'_'.$_FILES[$section.'_image']['name'];


move_uploaded_file(

$_FILES[$section.'_image']['tmp_name'],

"../../uploads/website/".$image

);


}




$existing = get_section(

$conn,

'home',

$section

);



if($existing){



if($image){


$stmt=mysqli_prepare(

$conn,

"UPDATE website_sections

SET title=?,

content=?,

image=?

WHERE page_name='home'

AND section_key=?"

);


mysqli_stmt_bind_param(

$stmt,

"ssss",

$title,

$content,

$image,

$section

);



}else{



$stmt=mysqli_prepare(

$conn,

"UPDATE website_sections

SET title=?,

content=?

WHERE page_name='home'

AND section_key=?"

);



mysqli_stmt_bind_param(

$stmt,

"sss",

$title,

$content,

$section

);



}



}else{



$stmt=mysqli_prepare(

$conn,

"INSERT INTO website_sections

(page_name,section_key,title,content,image)

VALUES

('home',?,?,?,?)"

);



mysqli_stmt_bind_param(

$stmt,

"ssss",

$section,

$title,

$content,

$image

);



}



mysqli_stmt_execute($stmt);



}



$success="Homepage updated successfully.";

}



?>



<!DOCTYPE html>

<html>

<head>

<title>

Homepage Management

</title>


<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">

</head>


<body>


<div class="container mt-5">


<h2>

Homepage Management

</h2>



<?php if(isset($success)){ ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php } ?>




<form method="POST" enctype="multipart/form-data">



<?php foreach($sections as $section):


$data=get_section(

$conn,

'home',

$section

);



?>



<div class="card mb-4 shadow">


<div class="card-header">


<?= ucfirst(str_replace('_',' ',$section)); ?>


</div>



<div class="card-body">



<label>

Title

</label>


<input

type="text"

name="<?= $section ?>_title"

class="form-control mb-3"

value="<?= e($data['title'] ?? '') ?>">





<label>

Content

</label>


<textarea

name="<?= $section ?>_content"

class="form-control mb-3"

rows="5">

<?= e($data['content'] ?? '') ?>

</textarea>





<?php if(in_array($section,['hero','principal_message'])){ ?>


<label>

Image

</label>


<input

type="file"

name="<?= $section ?>_image"

class="form-control">


<?php } ?>



</div>


</div>



<?php endforeach; ?>




<button

name="save"

class="btn btn-primary">

Save Homepage

</button>



</form>


</div>


</body>

</html>