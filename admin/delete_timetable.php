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


    $period_id = (int) ($_POST['period_id'] ?? 0);

    $class = trim($_POST['class'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($period_id <= 0 || $class === '') {

        set_error("Invalid timetable details.");

        redirect("timetables.php");
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,

        "DELETE FROM timetables
         WHERE period_id = ?
         AND class = ?"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $period_id,
        $class
    );


    if (mysqli_stmt_execute($stmt)) {


        /*
        |--------------------------------------------------------------------------
        | CHECK WHETHER ANY ROWS WERE DELETED
        |--------------------------------------------------------------------------
        */

        if (mysqli_stmt_affected_rows($stmt) > 0) {

            set_success(
                "Timetable for " . $class . " deleted successfully."
            );

        } else {

            set_error(
                "No matching timetable was found."
            );
        }


    } else {


        set_error(
            "Failed to delete timetable: "
            . mysqli_stmt_error($stmt)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT AFTER POST
    |--------------------------------------------------------------------------
    */

    redirect("timetables.php");
}


set_error("Invalid request.");

redirect("timetables.php");

?>
```
