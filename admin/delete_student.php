<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "DELETE FROM students WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

redirect("view_students.php");

?>
