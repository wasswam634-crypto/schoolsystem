```php
<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| DELETE TIMETABLE
|--------------------------------------------------------------------------
*/

if (isset($_POST['delete_timetable'])) {

    $class = trim($_POST['class'] ?? '');
    $year  = trim($_POST['year'] ?? '');
    $term  = trim($_POST['term'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validate details
    |--------------------------------------------------------------------------
    */

    if ($class === '' || $year === '' || $term === '') {

        set_error("Invalid timetable details.");

        redirect("timetables.php");
    }


    /*
    |--------------------------------------------------------------------------
    | Delete timetable
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,

        "DELETE FROM timetables
         WHERE class = ?
         AND academic_year = ?
         AND term = ?"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $class,
        $year,
        $term
    );


    if (mysqli_stmt_execute($stmt)) {

        set_success("Timetable deleted successfully.");

    } else {

        set_error(
            "Failed to delete timetable: "
            . mysqli_stmt_error($stmt)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect after POST
    |--------------------------------------------------------------------------
    */

    redirect("timetables.php");
}

?>
```
