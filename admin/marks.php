```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| SELECTED VALUES
|--------------------------------------------------------------------------
*/

$period_id = (int) ($_POST['period_id'] ?? $_GET['period_id'] ?? 0);
$group_id  = (int) ($_POST['group_id'] ?? $_GET['group_id'] ?? 0);
$academic_subject_id = (int) (
    $_POST['academic_subject_id']
    ?? $_GET['academic_subject_id']
    ?? 0
);


/*
|--------------------------------------------------------------------------
| LOAD ACADEMIC PERIODS
|--------------------------------------------------------------------------
*/

$periods = [];

$period_result = mysqli_query(
    $conn,
    "SELECT period_id, academic_year, period_name, status
     FROM academic_periods
     ORDER BY academic_year DESC, start_date DESC"
);

while ($row = mysqli_fetch_assoc($period_result)) {
    $periods[] = $row;
}


/*
|--------------------------------------------------------------------------
| LOAD ACADEMIC GROUPS
|--------------------------------------------------------------------------
*/

$groups = [];

$group_result = mysqli_query(
    $conn,
    "SELECT group_id, group_name, group_type
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name"
);

while ($row = mysqli_fetch_assoc($group_result)) {
    $groups[] = $row;
}


/*
|--------------------------------------------------------------------------
| LOAD SUBJECTS OFFERED TO SELECTED GROUP IN SELECTED PERIOD
|--------------------------------------------------------------------------
*/

$academic_subjects = [];

if ($period_id > 0 && $group_id > 0) {

    $subject_stmt = mysqli_prepare(
        $conn,
        "SELECT
            a.academic_subject_id,
            a.subject_id,
            s.subject_code,
            s.subject_name,
            a.teacher_id,
            a.status
         FROM academic_subjects a
         INNER JOIN subjects s
            ON a.subject_id = s.subject_id
         WHERE a.period_id = ?
           AND a.group_id = ?
           AND a.status = 'Active'
         ORDER BY s.subject_name"
    );

    mysqli_stmt_bind_param(
        $subject_stmt,
        "ii",
        $period_id,
        $group_id
    );

    mysqli_stmt_execute($subject_stmt);

    $subject_result = mysqli_stmt_get_result($subject_stmt);

    while ($row = mysqli_fetch_assoc($subject_result)) {
        $academic_subjects[] = $row;
    }

    mysqli_stmt_close($subject_stmt);
}


/*
|--------------------------------------------------------------------------
| SAVE MARKS
|--------------------------------------------------------------------------
*/

if (isset($_POST['save_marks'])) {

    $period_id = (int) ($_POST['period_id'] ?? 0);
    $group_id = (int) ($_POST['group_id'] ?? 0);
    $academic_subject_id = (int) (
        $_POST['academic_subject_id'] ?? 0
    );

    /*
    |----------------------------------------------------------------------
    | Validate basic selections
    |----------------------------------------------------------------------
    */

    if (
        $period_id <= 0 ||
        $group_id <= 0 ||
        $academic_subject_id <= 0
    ) {

        $error = "Please select the academic period, group and subject.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | VERIFY THAT THE SUBJECT BELONGS TO THIS PERIOD AND GROUP
        |--------------------------------------------------------------------------
        */

        $verify_stmt = mysqli_prepare(
            $conn,
            "SELECT academic_subject_id
             FROM academic_subjects
             WHERE academic_subject_id = ?
               AND period_id = ?
               AND group_id = ?
               AND status = 'Active'
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $verify_stmt,
            "iii",
            $academic_subject_id,
            $period_id,
            $group_id
        );

        mysqli_stmt_execute($verify_stmt);

        $verify_result = mysqli_stmt_get_result($verify_stmt);

        if (mysqli_num_rows($verify_result) === 0) {

            $error = "Invalid subject assignment.";

        }

        mysqli_stmt_close($verify_stmt);
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE EACH STUDENT'S MARK
    |--------------------------------------------------------------------------
    */

    if ($error === "" && isset($_POST['marks'])) {

        /*
        |------------------------------------------------------------------
        | Prepare the INSERT/UPDATE statement once.
        |
        | Because the database has:
        |
        | UNIQUE(student_id, academic_subject_id)
        |
        | ON DUPLICATE KEY UPDATE allows us to update an existing mark
        | instead of creating a duplicate.
        |------------------------------------------------------------------
        */

        $save_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO marks
            (
                student_id,
                academic_subject_id,
                subject_id,
                period_id,
                marks
            )
            SELECT
                ?,
                a.academic_subject_id,
                a.subject_id,
                a.period_id,
                ?
            FROM academic_subjects a
            INNER JOIN students st
                ON st.group_id = a.group_id
            WHERE a.academic_subject_id = ?
              AND a.period_id = ?
              AND a.group_id = ?
              AND st.student_id = ?
            ON DUPLICATE KEY UPDATE
                marks = VALUES(marks)"
        );


        foreach ($_POST['marks'] as $student_id => $mark) {

            $student_id = (int) $student_id;

            /*
            |--------------------------------------------------------------
            | Empty mark = do not save anything.
            |--------------------------------------------------------------
            */

            if ($mark === "") {
                continue;
            }


            /*
            |--------------------------------------------------------------
            | Convert to numeric value.
            |--------------------------------------------------------------
            */

            $mark = (int) $mark;


            /*
            |--------------------------------------------------------------
            | Validate mark range.
            |--------------------------------------------------------------
            */

            if ($mark < 0 || $mark > 100) {

                $error = "Marks must be between 0 and 100.";

                break;
            }


            /*
            |--------------------------------------------------------------
            | Save / update mark.
            |--------------------------------------------------------------
            */

            mysqli_stmt_bind_param(
                $save_stmt,
                "iiiiii",
                $student_id,
                $mark,
                $academic_subject_id,
                $period_id,
                $group_id,
                $student_id
            );

            mysqli_stmt_execute($save_stmt);
        }


        mysqli_stmt_close($save_stmt);


        if ($error === "") {

            $message = "Marks saved successfully.";

        }

    } elseif ($error === "" && !isset($_POST['marks'])) {

        $error = "No marks were submitted.";

    }
}


/*
|--------------------------------------------------------------------------
| LOAD STUDENTS FOR SELECTED GROUP
|--------------------------------------------------------------------------
*/

$students = [];

if ($group_id > 0) {

    $student_stmt = mysqli_prepare(
        $conn,
        "SELECT
            student_id,
            reg_no,
            full_name
         FROM students
         WHERE group_id = ?
           AND status = 'Active'
         ORDER BY full_name"
    );

    mysqli_stmt_bind_param(
        $student_stmt,
        "i",
        $group_id
    );

    mysqli_stmt_execute($student_stmt);

    $student_result = mysqli_stmt_get_result($student_stmt);

    while ($row = mysqli_fetch_assoc($student_result)) {
        $students[] = $row;
    }

    mysqli_stmt_close($student_stmt);
}


/*
|--------------------------------------------------------------------------
| LOAD EXISTING MARKS
|--------------------------------------------------------------------------
|
| Array structure:
|
| $existing_marks[student_id] = mark
|
|--------------------------------------------------------------------------
*/

$existing_marks = [];

if (
    $academic_subject_id > 0 &&
    $group_id > 0 &&
    $period_id > 0
) {

    $marks_stmt = mysqli_prepare(
        $conn,
        "SELECT
            m.student_id,
            m.marks
         FROM marks m
         INNER JOIN academic_subjects a
            ON m.academic_subject_id = a.academic_subject_id
         WHERE m.academic_subject_id = ?
           AND a.period_id = ?
           AND a.group_id = ?"
    );

    mysqli_stmt_bind_param(
        $marks_stmt,
        "iii",
        $academic_subject_id,
        $period_id,
        $group_id
    );

    mysqli_stmt_execute($marks_stmt);

    $marks_result = mysqli_stmt_get_result($marks_stmt);

    while ($row = mysqli_fetch_assoc($marks_result)) {

        $existing_marks[
            $row['student_id']
        ] = $row['marks'];
    }

    mysqli_stmt_close($marks_stmt);
}


/*
|--------------------------------------------------------------------------
| GET SELECTED SUBJECT INFORMATION
|--------------------------------------------------------------------------
*/

$selected_subject = null;

if ($academic_subject_id > 0) {

    $selected_subject_stmt = mysqli_prepare(
        $conn,
        "SELECT
            a.academic_subject_id,
            a.subject_id,
            a.period_id,
            a.group_id,
            s.subject_code,
            s.subject_name
         FROM academic_subjects a
         INNER JOIN subjects s
            ON a.subject_id = s.subject_id
         WHERE a.academic_subject_id = ?
           AND a.period_id = ?
           AND a.group_id = ?
           AND a.status = 'Active'
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $selected_subject_stmt,
        "iii",
        $academic_subject_id,
        $period_id,
        $group_id
    );

    mysqli_stmt_execute($selected_subject_stmt);

    $selected_subject_result =
        mysqli_stmt_get_result($selected_subject_stmt);

    $selected_subject =
        mysqli_fetch_assoc($selected_subject_result);

    mysqli_stmt_close($selected_subject_stmt);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Marks Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>

<div class="container-fluid p-4">

    <h2 class="mb-4">
        📝 Marks Entry
    </h2>


    <?php if ($message): ?>

        <div class="alert alert-success">
            <?= e($message); ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?= e($error); ?>
        </div>

    <?php endif; ?>


    <!-- ==============================================================
         STEP 1: SELECT PERIOD, GROUP AND SUBJECT
         ============================================================== -->

    <div class="card mb-4">

        <div class="card-header bg-primary text-white">

            <strong>
                Select Academic Period, Group and Subject
            </strong>

        </div>


        <div class="card-body">

            <form method="POST">

                <div class="row g-3">


                    <!-- ACADEMIC PERIOD -->

                    <div class="col-md-4">

                        <label class="form-label">
                            Academic Period
                        </label>

                        <select
                            name="period_id"
                            class="form-select"
                            required
                            onchange="this.form.submit()"
                        >

                            <option value="">
                                -- Select Period --
                            </option>


                            <?php foreach ($periods as $period): ?>

                                <option
                                    value="<?= (int) $period['period_id']; ?>"
                                    <?= $period_id == $period['period_id']
                                        ? 'selected'
                                        : ''; ?>
                                >

                                    <?= e(
                                        $period['academic_year']
                                        . ' - '
                                        . $period['period_name']
                                    ); ?>

                                    (<?= e($period['status']); ?>)

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- ACADEMIC GROUP -->

                    <div class="col-md-4">

                        <label class="form-label">
                            Academic Group / Class
                        </label>

                        <select
                            name="group_id"
                            class="form-select"
                            required
                            onchange="this.form.submit()"
                        >

                            <option value="">
                                -- Select Group --
                            </option>


                            <?php foreach ($groups as $group): ?>

                                <option
                                    value="<?= (int) $group['group_id']; ?>"
                                    <?= $group_id == $group['group_id']
                                        ? 'selected'
                                        : ''; ?>
                                >

                                    <?= e($group['group_name']); ?>

                                    -
                                    <?= e($group['group_type']); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- SUBJECT -->

                    <div class="col-md-4">

                        <label class="form-label">
                            Subject
                        </label>

                        <select
                            name="academic_subject_id"
                            class="form-select"
                            required
                            onchange="this.form.submit()"
                            <?= empty($academic_subjects)
                                ? 'disabled'
                                : ''; ?>
                        >

                            <option value="">
                                -- Select Subject --
                            </option>


                            <?php foreach (
                                $academic_subjects
                                as $academic_subject
                            ): ?>

                                <option
                                    value="<?= (int) $academic_subject['academic_subject_id']; ?>"
                                    <?= $academic_subject_id
                                        == $academic_subject['academic_subject_id']
                                        ? 'selected'
                                        : ''; ?>
                                >

                                    <?= e(
                                        $academic_subject['subject_code']
                                        . ' - '
                                        . $academic_subject['subject_name']
                                    ); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <?php if (
                            $period_id > 0 &&
                            $group_id > 0 &&
                            empty($academic_subjects)
                        ): ?>

                            <div class="text-danger mt-2">

                                No subjects have been assigned to this
                                group for this academic period.

                            </div>

                        <?php endif; ?>

                    </div>


                </div>

            </form>

        </div>

    </div>


    <!-- ==============================================================
         SELECTED SUBJECT INFORMATION
         ============================================================== -->

    <?php if ($selected_subject): ?>

        <div class="alert alert-info">

            <strong>
                Subject:
            </strong>

            <?= e($selected_subject['subject_name']); ?>

            &nbsp; | &nbsp;

            <strong>
                Code:
            </strong>

            <?= e($selected_subject['subject_code']); ?>

        </div>

    <?php endif; ?>


    <!-- ==============================================================
         STEP 2: ENTER MARKS
         ============================================================== -->

    <?php if (
        $selected_subject &&
        !empty($students)
    ): ?>

        <form method="POST">


            <!-- Preserve selections -->

            <input
                type="hidden"
                name="period_id"
                value="<?= (int) $period_id; ?>"
            >

            <input
                type="hidden"
                name="group_id"
                value="<?= (int) $group_id; ?>"
            >

            <input
                type="hidden"
                name="academic_subject_id"
                value="<?= (int) $academic_subject_id; ?>"
            >


            <div class="card">

                <div class="card-header bg-success text-white">

                    <strong>
                        Enter Marks
                    </strong>

                </div>


                <div class="card-body">


                    <div class="table-responsive">

                        <table
                            class="table table-bordered table-hover align-middle"
                        >

                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        #
                                    </th>

                                    <th>
                                        Registration No.
                                    </th>

                                    <th>
                                        Student
                                    </th>

                                    <th style="width: 180px;">
                                        Marks / 100
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php
                                $number = 1;
                                ?>

                                <?php foreach ($students as $student): ?>

                                    <tr>

                                        <td>
                                            <?= $number++; ?>
                                        </td>


                                        <td>
                                            <?= e(
                                                $student['reg_no']
                                                ?: '-'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?= e(
                                                $student['full_name']
                                            ); ?>
                                        </td>


                                        <td>

                                            <input
                                                type="number"
                                                name="marks[<?= (int) $student['student_id']; ?>]"
                                                class="form-control"
                                                min="0"
                                                max="100"
                                                step="1"
                                                value="<?= isset(
                                                    $existing_marks[
                                                        $student['student_id']
                                                    ]
                                                )
                                                    ? e(
                                                        $existing_marks[
                                                            $student['student_id']
                                                        ]
                                                    )
                                                    : ''; ?>"
                                                placeholder="0 - 100"
                                            >

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                    <div class="mt-3">

                        <button
                            type="submit"
                            name="save_marks"
                            class="btn btn-success"
                        >

                            💾 Save Marks

                        </button>

                    </div>


                </div>

            </div>

        </form>


    <?php elseif (
        $selected_subject &&
        empty($students)
    ): ?>

        <div class="alert alert-warning">

            No active students are currently assigned to this
            academic group.

        </div>

    <?php endif; ?>


</div>


</body>

</html>
```
