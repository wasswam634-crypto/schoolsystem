```php
<?php

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = null;
$error = null;


/*
|--------------------------------------------------------------------------
| SAVE SUBJECT ASSIGNMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST['save'])) {

    $period_id  = (int) ($_POST['period_id'] ?? 0);
    $group_id   = (int) ($_POST['group_id'] ?? 0);
    $subject_id = (int) ($_POST['subject_id'] ?? 0);
    $teacher_id = !empty($_POST['teacher_id'])
                    ? (int) $_POST['teacher_id']
                    : null;


    /*
    |--------------------------------------------------------------------------
    | BASIC VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($period_id <= 0 || $group_id <= 0 || $subject_id <= 0) {

        $error = "Please select an academic period, class/group and subject.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | CHECK FOR DUPLICATE ASSIGNMENT
        |--------------------------------------------------------------------------
        |
        | The same subject cannot be assigned twice to the same
        | class/group in the same academic period.
        |
        */

        $check = mysqli_prepare(
            $conn,
            "SELECT academic_subject_id
             FROM academic_subjects
             WHERE period_id = ?
             AND group_id = ?
             AND subject_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "iii",
            $period_id,
            $group_id,
            $subject_id
        );

        mysqli_stmt_execute($check);

        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {

            $error = "This subject has already been assigned to this class/group for this academic period.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | INSERT ASSIGNMENT
            |--------------------------------------------------------------------------
            */

            if ($teacher_id === null) {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO academic_subjects
                    (period_id, group_id, subject_id, teacher_id)
                    VALUES (?, ?, ?, NULL)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $period_id,
                    $group_id,
                    $subject_id
                );

            } else {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO academic_subjects
                    (period_id, group_id, subject_id, teacher_id)
                    VALUES (?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iiii",
                    $period_id,
                    $group_id,
                    $subject_id,
                    $teacher_id
                );
            }


            if (mysqli_stmt_execute($stmt)) {

                $message = "Subject assigned successfully.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | DATABASE UNIQUE CONSTRAINT PROTECTION
                |--------------------------------------------------------------------------
                */

                if (mysqli_errno($conn) == 1062) {

                    $error = "This subject is already assigned to this class/group for this academic period.";

                } else {

                    $error = "Unable to assign subject: " . mysqli_error($conn);
                }
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}


/*
|--------------------------------------------------------------------------
| REMOVE SUBJECT ASSIGNMENT
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $academic_subject_id = (int) $_GET['delete'];

    if ($academic_subject_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM academic_subjects
             WHERE academic_subject_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $academic_subject_id
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Subject assignment removed successfully.";

        } else {

            $error = "Unable to remove subject assignment: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}


/*
|--------------------------------------------------------------------------
| GET ACADEMIC PERIODS
|--------------------------------------------------------------------------
*/

$periods = mysqli_query(
    $conn,
    "SELECT period_id, academic_year, period_name, start_date, end_date
     FROM academic_periods
     ORDER BY start_date DESC, period_id DESC"
);


/*
|--------------------------------------------------------------------------
| GET ACADEMIC GROUPS
|--------------------------------------------------------------------------
*/

$groups = mysqli_query(
    $conn,
    "SELECT group_id, group_name, group_type
     FROM academic_groups
     WHERE status = 'Active'
     ORDER BY group_name ASC"
);


/*
|--------------------------------------------------------------------------
| GET SUBJECTS
|--------------------------------------------------------------------------
*/

$subjects = mysqli_query(
    $conn,
    "SELECT subject_id, subject_code, subject_name
     FROM subjects
     ORDER BY subject_name ASC"
);


/*
|--------------------------------------------------------------------------
| GET TEACHERS
|--------------------------------------------------------------------------
*/

$teachers = mysqli_query(
    $conn,
    "SELECT teacher_id, full_name
     FROM teachers
     ORDER BY full_name ASC"
);


/*
|--------------------------------------------------------------------------
| GET EXISTING SUBJECT ASSIGNMENTS
|--------------------------------------------------------------------------
*/

$assignments = mysqli_query(
    $conn,
    "SELECT
        a.academic_subject_id,
        a.status,

        p.academic_year,
        p.period_name,

        g.group_name,
        g.group_type,

        s.subject_code,
        s.subject_name,

        t.full_name AS teacher_name

     FROM academic_subjects a

     INNER JOIN academic_periods p
        ON a.period_id = p.period_id

     INNER JOIN academic_groups g
        ON a.group_id = g.group_id

     INNER JOIN subjects s
        ON a.subject_id = s.subject_id

     LEFT JOIN teachers t
        ON a.teacher_id = t.teacher_id

     ORDER BY
        p.start_date DESC,
        g.group_name ASC,
        s.subject_name ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Academic Subjects</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>

<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Academic Subjects</h2>

            <p class="text-muted mb-0">
                Assign subjects to classes/groups for specific academic periods.
            </p>

        </div>

        <a href="dashboard.php" class="btn btn-secondary">
            Dashboard
        </a>

    </div>


    <?php if ($message): ?>

        <div class="alert alert-success">
            <?= e($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <!--
    |--------------------------------------------------------------------------
    | ASSIGN SUBJECT FORM
    |--------------------------------------------------------------------------
    -->

    <div class="card shadow-sm mb-5">

        <div class="card-header">

            <h5 class="mb-0">
                Assign Subject
            </h5>

        </div>


        <div class="card-body">

            <form method="POST">

                <div class="row">


                    <!-- Academic Period -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Academic Period
                        </label>

                        <select
                            name="period_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Academic Period
                            </option>

                            <?php while ($row = mysqli_fetch_assoc($periods)): ?>

                                <option value="<?= e($row['period_id']) ?>">

                                    <?= e($row['academic_year']) ?>
                                    -
                                    <?= e($row['period_name']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- Academic Group -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Class / Group
                        </label>

                        <select
                            name="group_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Class / Group
                            </option>

                            <?php while ($row = mysqli_fetch_assoc($groups)): ?>

                                <option value="<?= e($row['group_id']) ?>">

                                    <?= e($row['group_name']) ?>

                                    (<?= e($row['group_type']) ?>)

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- Subject -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Subject
                        </label>

                        <select
                            name="subject_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Subject
                            </option>

                            <?php while ($row = mysqli_fetch_assoc($subjects)): ?>

                                <option value="<?= e($row['subject_id']) ?>">

                                    <?php if (!empty($row['subject_code'])): ?>

                                        <?= e($row['subject_code']) ?> -

                                    <?php endif; ?>

                                    <?= e($row['subject_name']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- Teacher -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Teacher
                            <span class="text-muted">
                                (Optional)
                            </span>
                        </label>

                        <select
                            name="teacher_id"
                            class="form-select"
                        >

                            <option value="">
                                Not Assigned
                            </option>

                            <?php while ($row = mysqli_fetch_assoc($teachers)): ?>

                                <option value="<?= e($row['teacher_id']) ?>">

                                    <?= e($row['full_name']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                </div>


                <button
                    type="submit"
                    name="save"
                    class="btn btn-primary"
                >
                    Assign Subject
                </button>

            </form>

        </div>

    </div>



    <!--
    |--------------------------------------------------------------------------
    | EXISTING ASSIGNMENTS
    |--------------------------------------------------------------------------
    -->

    <div class="card shadow-sm">

        <div class="card-header">

            <h5 class="mb-0">
                Assigned Subjects
            </h5>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>Academic Period</th>

                            <th>Class / Group</th>

                            <th>Subject</th>

                            <th>Teacher</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (mysqli_num_rows($assignments) > 0): ?>

                        <?php $number = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($assignments)): ?>

                            <tr>

                                <td>
                                    <?= $number++ ?>
                                </td>


                                <td>

                                    <?= e($row['academic_year']) ?>
                                    -
                                    <?= e($row['period_name']) ?>

                                </td>


                                <td>

                                    <?= e($row['group_name']) ?>

                                    <small class="text-muted">

                                        (<?= e($row['group_type']) ?>)

                                    </small>

                                </td>


                                <td>

                                    <?php if (!empty($row['subject_code'])): ?>

                                        <strong>
                                            <?= e($row['subject_code']) ?>
                                        </strong>

                                        -

                                    <?php endif; ?>

                                    <?= e($row['subject_name']) ?>

                                </td>


                                <td>

                                    <?php if (!empty($row['teacher_name'])): ?>

                                        <?= e($row['teacher_name']) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Not assigned
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($row['status'] === 'Active'): ?>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <a
                                        href="?delete=<?= e($row['academic_subject_id']) ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Remove this subject assignment?');"
                                    >
                                        Remove
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >

                                No subject assignments found.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>