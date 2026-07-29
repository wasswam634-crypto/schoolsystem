<?php
include '../includes/config.php';
include '../includes/auth.php';

require_role('admin');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=teachers_report.xls");

echo "Teacher ID\tFull Name\tPhone\tEmail\n";

$query = mysqli_query(
    $conn,
    "SELECT
        teacher_id,
        full_name,
        phone,
        email
     FROM teachers
     ORDER BY full_name ASC"
);

while($row = mysqli_fetch_assoc($query)){

    echo
        $row['teacher_id'] . "\t" .
        $row['full_name'] . "\t" .
        $row['phone'] . "\t" .
        $row['email'] . "\n";
}

exit;
?>