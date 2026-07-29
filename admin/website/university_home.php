<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$sections = [

    'hero',
    'faculties',
    'departments',
    'programmes',
    'research',
    'campus_life',
    'admissions',
    'call_to_action'

];



if(isset($_POST['save'])){


foreach($sections as $section){


$title = $_POST[$section.'_title'];

$content = $_POST[$section.'_content'];



$existing = get_section(

$conn,

'university_home',

$section

);



if($existing){



$stmt = mysqli_prepare(

$conn,

"UPDATE website_sections

SET title=?,
content=?

WHERE page_name='university_home'

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
else{



$stmt = mysqli_prepare(

$conn,

"INSERT INTO website_sections

(page_name,section_key,title,content)

VALUES

('university_home',?,?,?)"

);



mysqli_stmt_bind_param(

$stmt,

"sss",

$section,

$title,

$content

);



}



mysqli_stmt_execute($stmt);



}



$success = "University homepage updated successfully.";

}


?>


<!DOCTYPE html>

<html>

<head>

<title>

University Homepage Management

</title>


<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>

University Homepage Management

</h2>



<?php if(isset($success)){ ?>


<div class="alert alert-success">

<?= e($success); ?>

</div>


<?php } ?>



<form method="POST">



<?php foreach($sections as $section):


$data = get_section(

$conn,

'university_home',

$section

);

?>



<div class="card shadow mb-4">


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

rows="5"

class="form-control">

<?= e($data['content'] ?? '') ?>

</textarea>



</div>


</div>



<?php endforeach; ?>




<button

name="save"

class="btn btn-primary">

Save University Homepage

</button>



</form>


</div>


</body>


</html>