<?php

if(file_exists("lock.php")){
    die("The system has already been installed.");
}

$checks=[];

$checks[]=[
    "PHP Version (8.0+)",
    version_compare(PHP_VERSION,'8.0.0','>='),
    PHP_VERSION
];

$checks[]=[
    "MySQLi Extension",
    extension_loaded('mysqli'),
    extension_loaded('mysqli') ? "Installed":"Missing"
];

$checks[]=[
    "File Uploads",
    ini_get('file_uploads'),
    ini_get('file_uploads') ? "Enabled":"Disabled"
];

$checks[]=[
    "Uploads Folder",
    is_writable("../uploads"),
    is_writable("../uploads") ? "Writable":"Not Writable"
];

$checks[]=[
    "GD Extension",
    extension_loaded('gd'),
    extension_loaded('gd') ? "Installed":"Missing"
];

$checks[]=[
    "JSON Extension",
    extension_loaded('json'),
    extension_loaded('json') ? "Installed":"Missing"
];

$checks[]=[
    "ZIP Extension",
    extension_loaded('zip'),
    extension_loaded('zip') ? "Installed":"Missing"
];

$checks[]=[
    "OpenSSL",
    extension_loaded('openssl'),
    extension_loaded('openssl') ? "Installed":"Missing"
];

$checks[]=[
    "Maximum Upload Size",
    true,
    ini_get('upload_max_filesize')
];

$passed=true;

foreach($checks as $check){

    if(!$check[1]){
        $passed=false;
    }

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>System Requirements</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-9">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>

Step 1 : System Requirements

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<thead>

<tr>

<th>Requirement</th>

<th>Status</th>

<th>Current Value</th>

</tr>

</thead>

<tbody>

<?php foreach($checks as $row): ?>

<tr>

<td>

<?= $row[0]; ?>

</td>

<td>

<?php if($row[1]): ?>

<span class="badge bg-success">

PASS

</span>

<?php else: ?>

<span class="badge bg-danger">

FAIL

</span>

<?php endif; ?>

</td>

<td>

<?= $row[2]; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<div class="d-flex justify-content-between">

<a
href="index.php"
class="btn btn-secondary">

Back

</a>

<?php if($passed): ?>

<a
href="database.php"
class="btn btn-primary">

Continue

</a>

<?php else: ?>

<button
class="btn btn-danger"
disabled>

Fix Errors First

</button>

<?php endif; ?>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>