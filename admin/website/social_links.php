<?php
include '../../database/connection.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

require_role('admin');

if(isset($_POST['add'])){

    $platform = $_POST['platform'];
    $url = $_POST['url'];
    $icon = $_POST['icon'];

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO website_social_links
        (
            platform,
            url,
            icon
        )
        VALUES
        (
            ?, ?, ?
        )"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $platform,
        $url,
        $icon
    );

    mysqli_stmt_execute($stmt);
}

if(isset($_GET['delete'])){

    $id = (int)$_GET['delete'];

    mysqli_query(
        $conn,
        "DELETE FROM website_social_links
         WHERE social_id = $id");
}

$links = mysqli_query(
    $conn,
    "SELECT *
     FROM website_social_links
     ORDER BY social_id DESC");
?>

<!DOCTYPE html>
<html>
<head>

<title>Social Links Management</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2>Social Links Management</h2>

<div class="card mb-4">

<div class="card-body">

<form method="POST">

<input
type="text"
name="platform"
placeholder="Platform Name"
class="form-control mb-3"
required>

<input
type="text"
name="url"
placeholder="URL"
class="form-control mb-3"
required>

<input
type="text"
name="icon"
placeholder="Font Awesome Icon"
class="form-control mb-3">

<button
name="add"
class="btn btn-primary">

Add Link

</button>

</form>

</div>

</div>

<table class="table table-bordered">

<tr>

<th>Platform</th>
<th>URL</th>
<th>Icon</th>
<th>Action</th>

</tr>

<?php while($row = mysqli_fetch_assoc($links)){ ?>

<tr>

<td><?= e($row['platform']); ?></td>

<td><?= e($row['url']); ?></td>

<td><?= e($row['icon']); ?></td>

<td>

<a
href="?delete=<?= $row['social_id']; ?>"
class="btn btn-danger btn-sm">

Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>