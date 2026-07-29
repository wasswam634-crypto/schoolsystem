<?php

include '../../database/connection.php';
include '../../includes/auth.php';

require_role('admin');


$id = $_GET['id'];


// get image name first

$result = mysqli_query(
$conn,
"SELECT image 
 FROM website_sections
 WHERE section_id='$id'"
);


$row = mysqli_fetch_assoc($result);



if(!empty($row['image'])){


$file = "../../uploads/website/".$row['image'];


// delete physical image

if(file_exists($file)){

    unlink($file);

}


}



// remove image name from database

mysqli_query(
$conn,
"UPDATE website_sections
 SET image=NULL
 WHERE section_id='$id'"
);



header("Location: homepage.php");

exit;

?>