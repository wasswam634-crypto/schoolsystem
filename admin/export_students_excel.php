<?php
include '../includes/config.php';
include '../includes/auth.php';

require_role('admin');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=students_report.xls");

echo "Registration Number\tFull Name\tGender\tClass\tStream\tDate of Birth\tParent Contact\n";

$query = mysqli_query(
    $conn,
    "SELECT
        reg_no,
        full_name,
        gender,
        class,
        stream,
        dob,
        parent_contact
     FROM students
     ORDER BY full_name ASC"
);

while($row = mysqli_fetch_assoc($query)){

    echo
        $row['reg_no'] . "\t" .
        $row['full_name'] . "\t" .
        $row['gender'] . "\t" .
        $row['class'] . "\t" .
        $row['stream'] . "\t" .
        $row['dob'] . "\t" .
        $row['parent_contact'] . "\n";
}
exit;
?>