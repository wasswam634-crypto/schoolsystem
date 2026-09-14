```php
<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| BULK DELETE STUDENTS
|--------------------------------------------------------------------------
*/

if (!isset($_POST['delete_selected'])) {

    set_error("Invalid request.");

    redirect("view_students.php");
}


$student_ids = $_POST['student_ids'] ?? [];


if (!is_array($student_ids) || empty($student_ids)) {

    set_error("Please select at least one student.");

    redirect("view_students.php");
}


/*
|--------------------------------------------------------------------------
| CLEAN IDS
|--------------------------------------------------------------------------
*/

$student_ids = array_map('intval', $student_ids);

$student_ids = array_filter(
    $student_ids,
    function ($id) {
        return $id > 0;
    }
);


if (empty($student_ids)) {

    set_error("Invalid student selection.");

    redirect("view_students.php");
}


/*
|--------------------------------------------------------------------------
| CREATE PLACEHOLDERS
|--------------------------------------------------------------------------
*/

$placeholders = implode(
    ',',
    array_fill(
        0,
        count($student_ids),
        '?'
    )
);


/*
|--------------------------------------------------------------------------
| CREATE TYPE STRING
|--------------------------------------------------------------------------
*/

$types = str_repeat(
    'i',
    count($student_ids)
);


/*
|--------------------------------------------------------------------------
| DELETE SELECTED STUDENTS
|--------------------------------------------------------------------------
*/

$sql = "
    DELETE FROM students
    WHERE student_id IN ($placeholders)
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$student_ids
);


if (mysqli_stmt_execute($stmt)) {

    $deleted_count =
        mysqli_stmt_affected_rows($stmt);


    set_success(
        $deleted_count .
        " student(s) deleted successfully."
    );

} else {

    set_error(
        "Failed to delete students: "
        . mysqli_stmt_error($stmt)
    );
}


/*
|--------------------------------------------------------------------------
| REDIRECT AFTER POST
|--------------------------------------------------------------------------
*/

redirect("view_students.php");

?>
```
