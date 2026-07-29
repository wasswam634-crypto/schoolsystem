<?php

include 'header.php';
include 'navbar.php';



$intro = get_section(
    $conn,
    'academics',
    'intro'
);



$departments = get_section(
    $conn,
    'academics',
    'departments'
);



$programs = get_section(
    $conn,
    'academics',
    'programs'
);



$learning = get_section(
    $conn,
    'academics',
    'learning'
);



$research = get_section(
    $conn,
    'academics',
    'research'
);


?>


<div class="container py-5">


<h1 class="text-center mb-4">

<?= e($intro['title'] ?? 'Academic Programs'); ?>

</h1>



<p class="text-center mb-5">

<?= nl2br(e($intro['content'] ?? '')); ?>

</p>




<div class="row">



<?php

$sections = [

$departments,

$programs,

$learning,

$research

];


foreach($sections as $section):

?>


<div class="col-md-6 mb-4">


<div class="card shadow h-100">


<div class="card-body">


<h3 class="text-primary">

<?= e($section['title'] ?? ''); ?>

</h3>



<p>

<?= nl2br(e($section['content'] ?? '')); ?>

</p>



</div>


</div>


</div>


<?php endforeach; ?>



</div>



</div>


<?php include 'footer.php'; ?>