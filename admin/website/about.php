<?php

include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');


$sections = [
    'intro',
    'mission',
    'vision',
    'values',
    'history'
];


if(isset($_POST['save'])){


    foreach($sections as $section){


        $title = $_POST[$section.'_title'] ?? '';

        $content = $_POST[$section.'_content'] ?? '';



        $existing = get_section(
            $conn,
            'about',
            $section
        );



        if($existing){


            $stmt = mysqli_prepare(
                $conn,

                "UPDATE website_sections
                 SET title=?,
                     content=?
                 WHERE page_name='about'
                 AND section_key=?"
            );


            mysqli_stmt_bind_param(
                $stmt,
                "sss",

                $title,
                $content,
                $section
            );


        }else{


            $stmt = mysqli_prepare(
                $conn,

                "INSERT INTO website_sections
                (
                    page_name,
                    section_key,
                    title,
                    content
                )

                VALUES
                (
                    'about',
                    ?,
                    ?,
                    ?
                )"
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


    $success = "About page updated successfully.";

}


?>


<!DOCTYPE html>

<html>

<head>

<title>
About Page Management
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


</head>


<body>


<div class="container mt-5">


<h2>
About Page Management
</h2>



<?php if(isset($success)): ?>

<div class="alert alert-success">

<?= e($success); ?>

</div>

<?php endif; ?>



<form method="POST">



<?php foreach($sections as $section): ?>


<?php

$data = get_section(
    $conn,
    'about',
    $section
);

?>



<div class="card mb-4 shadow">


<div class="card-header">

<h4>
<?= ucfirst($section); ?>
</h4>

</div>



<div class="card-body">


<label class="form-label">
Title
</label>


<input

type="text"

name="<?= $section; ?>_title"

class="form-control mb-3"

value="<?= e($data['title'] ?? ''); ?>"

>



<label class="form-label">
Content
</label>


<textarea

name="<?= $section; ?>_content"

rows="5"

class="form-control"

><?= e($data['content'] ?? ''); ?></textarea>



</div>


</div>



<?php endforeach; ?>



<button

type="submit"

name="save"

class="btn btn-primary"

>

Save About Page

</button>



</form>


</div>


</body>


</html>