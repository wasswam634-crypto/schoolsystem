
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';


require_role(['admin', 'teacher', 'student']);


/*
|--------------------------------------------------------------------------
| CHECK REQUIRED PARAMETERS
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {

    die("Student ID missing");

}

$student_id = (int) $_GET['id'];


if (!isset($_GET['period_id'])) {

    die("Academic period missing");

}

$period_id = (int) $_GET['period_id'];



/*
|--------------------------------------------------------------------------
| GET STUDENT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM students
     WHERE student_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($stmt);

$student_result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($student_result);

mysqli_stmt_close($stmt);


if (!$student) {

    die("Student not found");

}



/*
|--------------------------------------------------------------------------
| GET ACADEMIC PERIOD
|--------------------------------------------------------------------------
*/

$period_stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM academic_periods
     WHERE period_id = ?"
);

mysqli_stmt_bind_param(
    $period_stmt,
    "i",
    $period_id
);

mysqli_stmt_execute($period_stmt);

$period_result = mysqli_stmt_get_result($period_stmt);

$period = mysqli_fetch_assoc($period_result);

mysqli_stmt_close($period_stmt);


if (!$period) {

    die("Academic period not found");

}



/*
|--------------------------------------------------------------------------
| GET ACTIVE GRADING SYSTEM
|--------------------------------------------------------------------------
|
| Only the grading system marked Active is used on the report card.
|
*/

$grading_system = null;

$grading_system_query = mysqli_query(
    $conn,
    "SELECT grading_id, name
     FROM grading_systems
     WHERE status = 'Active'
     LIMIT 1"
);

if ($grading_system_query) {

    $grading_system = mysqli_fetch_assoc(
        $grading_system_query
    );

}



/*
|--------------------------------------------------------------------------
| GET GRADING RULES
|--------------------------------------------------------------------------
|
| We load the rules once instead of querying the database for every
| subject on the report card.
|
*/

$grading_rules = [];


if ($grading_system) {

    $grading_stmt = mysqli_prepare(
        $conn,
        "SELECT
            rule_id,
            grade,
            min_mark,
            max_mark,
            points,
            remark
         FROM grading_rules
         WHERE grading_id = ?
         ORDER BY min_mark DESC"
    );

    mysqli_stmt_bind_param(
        $grading_stmt,
        "i",
        $grading_system['grading_id']
    );

    mysqli_stmt_execute($grading_stmt);

    $grading_result = mysqli_stmt_get_result(
        $grading_stmt
    );


    while ($rule = mysqli_fetch_assoc($grading_result)) {

        $grading_rules[] = $rule;

    }


    mysqli_stmt_close($grading_stmt);
}



/*
|--------------------------------------------------------------------------
| FUNCTION: GET GRADE FROM CONFIGURED RULES
|--------------------------------------------------------------------------
*/

function get_configured_grade($mark, $grading_rules)
{

    foreach ($grading_rules as $rule) {

        if (
            $mark >= (float) $rule['min_mark'] &&
            $mark <= (float) $rule['max_mark']
        ) {

            return $rule;

        }

    }


    return null;
}



/*
|--------------------------------------------------------------------------
| GET RESULTS
|--------------------------------------------------------------------------
|
| Results are still taken from the marks table.
| Grading is now obtained from grading_rules.
|
*/

$result_stmt = mysqli_prepare(
    $conn,

    "SELECT
        subjects.subject_name,
        marks.marks

     FROM marks

     INNER JOIN subjects
        ON marks.subject_id = subjects.subject_id

     WHERE marks.student_id = ?
       AND marks.period_id = ?

     ORDER BY subjects.subject_name"
);


mysqli_stmt_bind_param(
    $result_stmt,
    "ii",
    $student_id,
    $period_id
);


mysqli_stmt_execute($result_stmt);


$result = mysqli_stmt_get_result(
    $result_stmt
);




/*
|--------------------------------------------------------------------------
| POSITION WITHIN HISTORICAL ACADEMIC GROUP
|--------------------------------------------------------------------------
|
| We determine the student's group from the academic subjects/marks
| belonging to the selected academic period.
|
| We do NOT simply use students.group_id because that may represent
| the student's current group rather than the group they belonged to
| during the selected historical period.
|
*/


$student_group_id = null;


/*
|--------------------------------------------------------------------------
| FIND STUDENT'S GROUP FOR THIS PERIOD
|--------------------------------------------------------------------------
*/

$group_stmt = mysqli_prepare(
    $conn,

    "SELECT
        academic_subjects.group_id

     FROM marks

     INNER JOIN academic_subjects
        ON marks.academic_subject_id =
           academic_subjects.academic_subject_id

     WHERE marks.student_id = ?
       AND academic_subjects.period_id = ?

     GROUP BY academic_subjects.group_id

     ORDER BY COUNT(*) DESC

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $group_stmt,
    "ii",
    $student_id,
    $period_id
);


mysqli_stmt_execute(
    $group_stmt
);


$group_result = mysqli_stmt_get_result(
    $group_stmt
);


$group_row = mysqli_fetch_assoc(
    $group_result
);


mysqli_stmt_close($group_stmt);


if ($group_row) {

    $student_group_id = (int) $group_row['group_id'];

}



/*
|--------------------------------------------------------------------------
| CALCULATE POSITION
|--------------------------------------------------------------------------
*/

$position = 1;

$student_position = "N/A";


if ($student_group_id !== null) {


    /*
    |--------------------------------------------------------------------------
    | GET STUDENTS IN SAME GROUP
    |--------------------------------------------------------------------------
    |
    | Each student's average is calculated only from marks belonging
    | to the selected academic period and selected group.
    |
    */

    $rank_stmt = mysqli_prepare(
        $conn,

        "SELECT
            m.student_id,
            AVG(m.marks) AS average

         FROM marks m

         INNER JOIN academic_subjects a
            ON m.academic_subject_id =
               a.academic_subject_id

         WHERE a.period_id = ?
           AND a.group_id = ?

         GROUP BY m.student_id

         ORDER BY average DESC,
                  m.student_id ASC"
    );


    mysqli_stmt_bind_param(
        $rank_stmt,
        "ii",
        $period_id,
        $student_group_id
    );


    mysqli_stmt_execute(
        $rank_stmt
    );


    $rank_result = mysqli_stmt_get_result(
        $rank_stmt
    );


    while ($rank_row = mysqli_fetch_assoc($rank_result)) {


        if (
            (int) $rank_row['student_id']
            === $student_id
        ) {

            $student_position = $position;

            break;

        }


        $position++;

    }


    mysqli_stmt_close($rank_stmt);

}




/*
|--------------------------------------------------------------------------
| TOTALS
|--------------------------------------------------------------------------
*/

$total = 0;
$count = 0;
$total_points = 0;
$points_count = 0;

?>

<!DOCTYPE html>

<html>

<head>

<title>
Student Report Card
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<style>

body {

    background: #f2f4f7;

    font-family: Arial, sans-serif;

}


.report-card {

    background: white;

    max-width: 900px;

    margin: auto;

    padding: 30px;

    border-radius: 15px;

    box-shadow: 0 5px 20px #ccc;

}


.school-header {

    text-align: center;

}


.school-logo {

    width: 100px;

    height: 100px;

    object-fit: contain;

}


.results th {

    background: #212529;

    color: white;

    text-align: center;

}


.results td {

    text-align: center;

    vertical-align: middle;

}


.summary {

    margin-top: 20px;

    border-top: 2px solid #ddd;

    padding-top: 15px;

}


.grading-info {

    background: #f8f9fa;

    border-left: 4px solid #0d6efd;

    padding: 10px 15px;

    margin-bottom: 15px;

}


@media print {

    .btn {

        display: none;

    }


    .report-card {

        box-shadow: none;

    }


    body {

        background: white;

    }

}

</style>


</head>


<body>


<div class="container mt-4">


<!-- PRINT BUTTON -->

<button
onclick="window.print()"
class="btn btn-primary mb-3">

    Print Report Card

</button>



<div class="report-card">



<!-- =========================================================
     SCHOOL HEADER
========================================================== -->


<div class="school-header">


<?php

$school_query = mysqli_query(
    $conn,
    "SELECT *
     FROM school_settings
     LIMIT 1"
);

$school = mysqli_fetch_assoc(
    $school_query
);

?>


<?php if (!empty($school['logo'])): ?>

<img
src="../uploads/logos/<?= e($school['logo']); ?>"
class="school-logo">

<?php endif; ?>


<h2>

<?= e($school['school_name'] ?? ''); ?>

</h2>


<p>

<?= e($school['address'] ?? ''); ?>

<br>

<?= e($school['phone'] ?? ''); ?>

</p>



<h3>

STUDENT REPORT CARD

</h3>


<p>

Academic Year:

<b>
<?= e($period['academic_year']); ?>
</b>


<br>


Period:

<b>
<?= e($period['period_name']); ?>
</b>

</p>


</div>



<hr>



<!-- =========================================================
     GRADING SYSTEM INFORMATION
========================================================== -->

<div class="grading-info">

<?php if ($grading_system): ?>

    <strong>
        Grading System:
    </strong>

    <?= e($grading_system['name']); ?>

<?php else: ?>

    <strong class="text-danger">
        Warning:
    </strong>

    No active grading system has been configured.

<?php endif; ?>

</div>



<!-- =========================================================
     STUDENT INFORMATION
========================================================== -->


<div class="card p-3">


<b>
Student Name:
</b>

<?= e($student['full_name']); ?>


<br>


<b>
Registration No:
</b>

<?= e($student['reg_no']); ?>


<br>


<b>
Class:
</b>

<?= e($student['class']); ?>


</div>




<!-- =========================================================
     ACADEMIC PERFORMANCE
========================================================== -->


<h4 class="mt-4">

Academic Performance

</h4>



<table class="table table-bordered results">


<thead>

<tr>

<th>
Subject
</th>

<th>
Marks
</th>

<th>
Grade
</th>

<th>
Points
</th>

<th>
Remark
</th>

</tr>

</thead>


<tbody>



<?php while ($row = mysqli_fetch_assoc($result)): ?>


<?php

/*
|--------------------------------------------------------------------------
| CALCULATE TOTAL
|--------------------------------------------------------------------------
*/

$mark = (float) $row['marks'];

$total += $mark;

$count++;



/*
|--------------------------------------------------------------------------
| FIND CONFIGURED GRADING RULE
|--------------------------------------------------------------------------
*/

$grade_rule = get_configured_grade(
    $mark,
    $grading_rules
);



if ($grade_rule) {

    $grade = $grade_rule['grade'];

    $remark = $grade_rule['remark'];

    $points = $grade_rule['points'];


    if ($points !== null) {

        $total_points += (float) $points;

        $points_count++;

    }

} else {

    $grade = "N/A";

    $remark = "No grading rule";

    $points = null;

}

?>


<tr>


<td>

<?= e($row['subject_name']); ?>

</td>


<td>

<?= number_format($mark, 2); ?>

</td>


<td>

<?php if ($grade !== "N/A"): ?>

    <strong>

        <?= e($grade); ?>

    </strong>

<?php else: ?>

    <span class="text-danger">

        N/A

    </span>

<?php endif; ?>

</td>


<td>

<?php if ($points !== null): ?>

    <?= number_format(
        (float) $points,
        2
    ); ?>

<?php else: ?>

    —

<?php endif; ?>

</td>


<td>

<?= e(
    $remark ?: '—'
); ?>

</td>


</tr>



<?php endwhile; ?>


<?php if ($count === 0): ?>


<tr>

<td
colspan="5"
class="text-center text-muted py-4">

No marks have been entered for this student in this academic period.

</td>

</tr>


<?php endif; ?>


</tbody>


</table>




<?php

/*
|--------------------------------------------------------------------------
| CALCULATE AVERAGE
|--------------------------------------------------------------------------
*/

$average = ($count > 0)
    ? $total / $count
    : 0;



/*
|--------------------------------------------------------------------------
| CALCULATE AVERAGE POINTS
|--------------------------------------------------------------------------
*/

$average_points = ($points_count > 0)
    ? $total_points / $points_count
    : null;

?>



<!-- =========================================================
     SUMMARY
========================================================== -->


<div class="summary">


<h5>

Total Marks:

<?= number_format($total, 2); ?>

</h5>


<h5>

Average:

<?= number_format($average, 2); ?>%

</h5>


<h5>

Subjects:

<?= e($count); ?>

</h5>


<?php if ($points_count > 0): ?>

<h5>

Total Points:

<?= number_format(
    $total_points,
    2
); ?>

</h5>


<h5>

Average Points:

<?= number_format(
    $average_points,
    2
); ?>

</h5>

<?php endif; ?>


<h5>

Position:

<?= e($student_position); ?>

</h5>



</div>




<!-- =========================================================
     SIGNATURES
========================================================== -->


<div class="mt-5">


<div class="row text-center">


<div class="col">

____________________

<br>

Class Teacher

</div>


<div class="col">

____________________

<br>

Head Teacher

</div>


</div>


</div>



</div>


</div>


</body>

</html>