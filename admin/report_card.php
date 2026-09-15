<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role(['admin', 'teacher', 'student']);


/*
|--------------------------------------------------------------------------
| REQUIRED PARAMETERS
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
| HELPER FUNCTIONS
|--------------------------------------------------------------------------
*/

/*
 * Convert report profile type into a standard internal value.
 */
function report_profile_type($value)
{
    $value = strtolower(trim((string) $value));

    return match ($value) {

        'lower_primary',
        'lower primary',
        'primary_lower',
        'primary lower'
            => 'lower_primary',

        'primary_upper',
        'upper_primary',
        'upper primary',
        'primary upper'
            => 'upper_primary',

        'olevel',
        'o_level',
        'o-level',
        'ordinary level',
        'ordinary_level'
            => 'olevel',

        'alevel',
        'a_level',
        'a-level',
        'advanced level',
        'advanced_level'
            => 'alevel',

        'university',
        'tertiary'
            => 'university',

        default
            => 'general',
    };
}


/*
 * Find grading rule matching a mark.
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
| AGGREGATE FROM GRADE
|--------------------------------------------------------------------------
|
| Examples:
|
| D1 -> 1
| D2 -> 2
| C3 -> 3
| C4 -> 4
| C5 -> 5
| C6 -> 6
| P7 -> 7
|
| IMPORTANT:
|
| Aggregate is NOT taken from grading_rules.points.
|
|--------------------------------------------------------------------------
*/

function get_aggregate_from_grade($grade)
{
    if ($grade === null || trim((string) $grade) === '') {
        return null;
    }

    $grade = trim((string) $grade);

    if (preg_match('/(\d+)$/', $grade, $matches)) {
        return (int) $matches[1];
    }

    return null;
}


/*
 * Safe output helper.
 */
function report_escape($value)
{
    return e((string) ($value ?? ''));
}


/*
|--------------------------------------------------------------------------
| GET STUDENT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM students
     WHERE student_id = ?
     LIMIT 1"
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
     WHERE period_id = ?
     LIMIT 1"
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
| FIND HISTORICAL ACADEMIC GROUP
|--------------------------------------------------------------------------
|
| The academic group is still required internally.
|
| We use:
|
| marks
|   ↓
| academic_subjects
|   ↓
| academic_groups
|
| This ensures that an old report card uses the correct historical
| group/profile instead of the student's current group.
|
| IMPORTANT:
| The academic group is NOT displayed to the user.
|
|--------------------------------------------------------------------------
*/

$student_group_id = null;

$academic_group = null;


$group_stmt = mysqli_prepare(
    $conn,

    "SELECT
        a.group_id,
        ag.group_name,
        ag.group_type,
        ag.report_profile_id,
        COUNT(*) AS mark_count

     FROM marks m

     INNER JOIN academic_subjects a
        ON m.academic_subject_id = a.academic_subject_id

     INNER JOIN academic_groups ag
        ON a.group_id = ag.group_id

     WHERE m.student_id = ?
       AND a.period_id = ?

     GROUP BY
        a.group_id,
        ag.group_name,
        ag.group_type,
        ag.report_profile_id

     ORDER BY
        mark_count DESC,
        a.group_id ASC

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $group_stmt,
    "ii",
    $student_id,
    $period_id
);


mysqli_stmt_execute($group_stmt);


$group_result = mysqli_stmt_get_result(
    $group_stmt
);


$academic_group = mysqli_fetch_assoc(
    $group_result
);


mysqli_stmt_close(
    $group_stmt
);


if ($academic_group) {

    $student_group_id =
        (int) $academic_group['group_id'];
}


/*
|--------------------------------------------------------------------------
| GET REPORT CARD PROFILE
|--------------------------------------------------------------------------
*/

$report_profile = null;

$grading_system = null;


if ($student_group_id !== null) {

    $profile_stmt = mysqli_prepare(
        $conn,

        "SELECT

            rcp.*,

            gs.grading_id,

            gs.name AS grading_system_name,

            gs.description AS grading_system_description

         FROM academic_groups ag

         LEFT JOIN report_card_profiles rcp

            ON ag.report_profile_id =
               rcp.profile_id

         LEFT JOIN grading_systems gs

            ON rcp.grading_system_id =
               gs.grading_id

         WHERE ag.group_id = ?

         LIMIT 1"
    );


    mysqli_stmt_bind_param(
        $profile_stmt,
        "i",
        $student_group_id
    );


    mysqli_stmt_execute(
        $profile_stmt
    );


    $profile_result =
        mysqli_stmt_get_result(
            $profile_stmt
        );


    $report_profile =
        mysqli_fetch_assoc(
            $profile_result
        );


    mysqli_stmt_close(
        $profile_stmt
    );


    if (
        $report_profile &&
        !empty($report_profile['grading_id'])
    ) {

        $grading_system = [

            'grading_id'
                => $report_profile['grading_id'],

            'name'
                => $report_profile['grading_system_name'],

            'description'
                => $report_profile['grading_system_description'],

        ];
    }
}


/*
|--------------------------------------------------------------------------
| NORMALISE PROFILE SETTINGS
|--------------------------------------------------------------------------
*/

if ($report_profile) {

    $report_type =
        report_profile_type(
            $report_profile['report_type']
                ?? 'general'
        );

    $profile_name =
        $report_profile['profile_name']
            ?? 'General Report Card';

    $ranking_method =
        $report_profile['ranking_method']
            ?? 'Average';

} else {

    $report_type = 'general';

    $profile_name =
        'Report Card';

    $ranking_method =
        'Average';
}


/*
|--------------------------------------------------------------------------
| GET GRADING RULES
|--------------------------------------------------------------------------
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


    mysqli_stmt_execute(
        $grading_stmt
    );


    $grading_result =
        mysqli_stmt_get_result(
            $grading_stmt
        );


    while (
        $rule =
            mysqli_fetch_assoc(
                $grading_result
            )
    ) {

        $grading_rules[] =
            $rule;
    }


    mysqli_stmt_close(
        $grading_stmt
    );
}


/*
|--------------------------------------------------------------------------
| GET SCHOOL SETTINGS
|--------------------------------------------------------------------------
*/

$school = [];


$school_query = mysqli_query(
    $conn,

    "SELECT *
     FROM school_settings
     LIMIT 1"
);


if ($school_query) {

    $school =
        mysqli_fetch_assoc(
            $school_query
        ) ?: [];
}


/*
|--------------------------------------------------------------------------
| GET RESULTS
|--------------------------------------------------------------------------
*/

$result_stmt = mysqli_prepare(
    $conn,

    "SELECT

        m.marks,

        s.subject_id,

        s.subject_name,

        a.academic_subject_id,

        a.group_id,

        a.teacher_id

     FROM marks m

     INNER JOIN academic_subjects a
        ON m.academic_subject_id =
           a.academic_subject_id

     INNER JOIN subjects s
        ON a.subject_id =
           s.subject_id

     WHERE m.student_id = ?

       AND a.period_id = ?

     ORDER BY
        s.subject_name ASC"
);


mysqli_stmt_bind_param(
    $result_stmt,
    "ii",
    $student_id,
    $period_id
);


mysqli_stmt_execute(
    $result_stmt
);


$result =
    mysqli_stmt_get_result(
        $result_stmt
    );


/*
|--------------------------------------------------------------------------
| BUILD RESULT DATA
|--------------------------------------------------------------------------
*/

$subjects_data = [];

$total = 0;

$count = 0;


/*
|--------------------------------------------------------------------------
| POINTS
|--------------------------------------------------------------------------
|
| These are separate from aggregate.
|
|--------------------------------------------------------------------------
*/

$total_points = 0;

$points_count = 0;


/*
|--------------------------------------------------------------------------
| AGGREGATE
|--------------------------------------------------------------------------
|
| Aggregate is calculated from the numeric part of the grade.
|
| D1 = 1
| D2 = 2
| C3 = 3
|
|--------------------------------------------------------------------------
*/

$total_aggregate = 0;

$aggregate_count = 0;


while (
    $row =
        mysqli_fetch_assoc(
            $result
        )
) {

    $mark =
        (float) $row['marks'];


    /*
     * Find configured grading rule.
     */
    $grade_rule =
        get_configured_grade(
            $mark,
            $grading_rules
        );


    if ($grade_rule) {

        $grade =
            $grade_rule['grade'];

        $remark =
            $grade_rule['remark'];

        $points =
            $grade_rule['points'];

    } else {

        $grade = 'N/A';

        $remark = 'No grading rule';

        $points = null;
    }


    /*
    |--------------------------------------------------------------------------
    | CORRECT AGGREGATE CALCULATION
    |--------------------------------------------------------------------------
    |
    | DO NOT use $points here.
    |
    | If grade = D1:
    |     aggregate = 1
    |
    | If grade = D2:
    |     aggregate = 2
    |
    |--------------------------------------------------------------------------
    */

    $subject_aggregate =
        get_aggregate_from_grade($grade);


    if ($subject_aggregate !== null) {

        $total_aggregate +=
            $subject_aggregate;

        $aggregate_count++;
    }


    /*
     * Normal total marks.
     */
    $total += $mark;

    $count++;


    /*
     |--------------------------------------------------------------------------
     | POINTS CALCULATION
     |--------------------------------------------------------------------------
     |
     | Points remain independent from aggregate.
     |
     |--------------------------------------------------------------------------
     */

    if (
        $points !== null &&
        $points !== ''
    ) {

        $total_points +=
            (float) $points;

        $points_count++;
    }


    $subjects_data[] = [

        'subject_name'
            => $row['subject_name'],

        'marks'
            => $mark,

        'grade'
            => $grade,

        'points'
            => $points,

        'remark'
            => $remark,

    ];
}


mysqli_stmt_close(
    $result_stmt
);


/*
|--------------------------------------------------------------------------
| BASIC CALCULATIONS
|--------------------------------------------------------------------------
*/

$average =
    ($count > 0)
        ? ($total / $count)
        : 0;


$average_points =
    ($points_count > 0)
        ? ($total_points / $points_count)
        : null;


/*
|--------------------------------------------------------------------------
| RANKING VALUE
|--------------------------------------------------------------------------
*/

$student_ranking_value = null;


switch ($ranking_method) {

    case 'Total Marks':

        $student_ranking_value =
            $total;

        break;


    case 'Average':

        $student_ranking_value =
            $average;

        break;


    case 'Aggregate':

        /*
         * IMPORTANT:
         *
         * Aggregate is grade-derived.
         *
         * D1 + D1 + D2
         *
         * = 1 + 1 + 2
         *
         * = 4
         */
        $student_ranking_value =
            $total_aggregate;

        break;


    case 'Total Points':

        /*
         * Total Points still uses the configured
         * points column.
         */
        $student_ranking_value =
            $total_points;

        break;


    case 'GPA':

        $student_ranking_value =
            $average_points;

        break;


    default:

        $student_ranking_value =
            $average;

        break;
}


/*
|--------------------------------------------------------------------------
| CALCULATE POSITION
|--------------------------------------------------------------------------
*/

$student_position = "N/A";


if (
    $student_group_id !== null &&
    $count > 0
) {


    /*
    |--------------------------------------------------------------------------
    | TOTAL MARKS RANKING
    |--------------------------------------------------------------------------
    */

    if (
        $ranking_method === 'Total Marks'
    ) {

        $rank_stmt = mysqli_prepare(
            $conn,

            "SELECT

                m.student_id,

                SUM(m.marks) AS ranking_value

             FROM marks m

             INNER JOIN academic_subjects a

                ON m.academic_subject_id =
                   a.academic_subject_id

             WHERE a.period_id = ?

               AND a.group_id = ?

             GROUP BY m.student_id

             ORDER BY
                ranking_value DESC,
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


        $rank_result =
            mysqli_stmt_get_result(
                $rank_stmt
            );


        $position = 1;


        while (
            $rank_row =
                mysqli_fetch_assoc(
                    $rank_result
                )
        ) {

            if (
                (int)
                $rank_row['student_id']
                === $student_id
            ) {

                $student_position =
                    $position;

                break;
            }


            $position++;
        }


        mysqli_stmt_close(
            $rank_stmt
        );


    /*
    |--------------------------------------------------------------------------
    | AVERAGE RANKING
    |--------------------------------------------------------------------------
    */

    } elseif (
        $ranking_method === 'Average'
    ) {

        $rank_stmt = mysqli_prepare(
            $conn,

            "SELECT

                m.student_id,

                AVG(m.marks) AS ranking_value

             FROM marks m

             INNER JOIN academic_subjects a

                ON m.academic_subject_id =
                   a.academic_subject_id

             WHERE a.period_id = ?

               AND a.group_id = ?

             GROUP BY m.student_id

             ORDER BY
                ranking_value DESC,
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


        $rank_result =
            mysqli_stmt_get_result(
                $rank_stmt
            );


        $position = 1;


        while (
            $rank_row =
                mysqli_fetch_assoc(
                    $rank_result
                )
        ) {

            if (
                (int)
                $rank_row['student_id']
                === $student_id
            ) {

                $student_position =
                    $position;

                break;
            }


            $position++;
        }


        mysqli_stmt_close(
            $rank_stmt
        );


    /*
    |--------------------------------------------------------------------------
    | AGGREGATE / POINTS / GPA RANKING
    |--------------------------------------------------------------------------
    */

    } else {

        $rank_stmt = mysqli_prepare(
            $conn,

            "SELECT

                m.student_id,

                m.marks

             FROM marks m

             INNER JOIN academic_subjects a

                ON m.academic_subject_id =
                   a.academic_subject_id

             WHERE a.period_id = ?

               AND a.group_id = ?

             ORDER BY m.student_id ASC"
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


        $rank_result =
            mysqli_stmt_get_result(
                $rank_stmt
            );


        $student_marks_for_rank = [];


        while (
            $rank_row =
                mysqli_fetch_assoc(
                    $rank_result
                )
        ) {

            $sid =
                (int) $rank_row['student_id'];


            $student_marks_for_rank[$sid][] =
                (float) $rank_row['marks'];
        }


        mysqli_stmt_close(
            $rank_stmt
        );


        $ranking_rows = [];


        foreach (
            $student_marks_for_rank
            as $sid => $marks_list
        ) {

            /*
             * POINTS
             */
            $rank_points = 0;

            $rank_point_count = 0;


            /*
             * AGGREGATE
             */
            $rank_aggregate = 0;

            $rank_aggregate_count = 0;


            foreach (
                $marks_list
                as $rank_mark
            ) {

                $rank_rule =
                    get_configured_grade(
                        $rank_mark,
                        $grading_rules
                    );


                if ($rank_rule) {


                    /*
                    |--------------------------------------------------------------------------
                    | AGGREGATE FROM GRADE
                    |--------------------------------------------------------------------------
                    */

                    $rank_grade =
                        $rank_rule['grade'];


                    $grade_aggregate =
                        get_aggregate_from_grade(
                            $rank_grade
                        );


                    if (
                        $grade_aggregate !== null
                    ) {

                        $rank_aggregate +=
                            $grade_aggregate;

                        $rank_aggregate_count++;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | POINTS FROM GRADING RULE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $rank_rule['points'] !== null &&
                        $rank_rule['points'] !== ''
                    ) {

                        $rank_points +=
                            (float)
                            $rank_rule['points'];

                        $rank_point_count++;
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | DETERMINE RANKING VALUE
            |--------------------------------------------------------------------------
            */

            if (
                $ranking_method === 'Aggregate'
            ) {

                /*
                 * Lower aggregate is better.
                 */
                $rank_value =
                    $rank_aggregate;

            } elseif (
                $ranking_method === 'GPA'
            ) {

                $rank_value =
                    ($rank_point_count > 0)
                        ? (
                            $rank_points /
                            $rank_point_count
                        )
                        : 0;

            } else {

                /*
                 * Total Points.
                 */
                $rank_value =
                    $rank_points;
            }


            $ranking_rows[] = [

                'student_id'
                    => (int) $sid,

                'ranking_value'
                    => $rank_value,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SORT RANKING
        |--------------------------------------------------------------------------
        |
        | Aggregate:
        |     LOWER is better.
        |
        | Total Points:
        |     HIGHER is better.
        |
        | GPA:
        |     HIGHER is better.
        |
        |--------------------------------------------------------------------------
        */

        usort(
            $ranking_rows,

            function ($a, $b)
                use ($ranking_method)
            {

                if (
                    $a['ranking_value']
                    ==
                    $b['ranking_value']
                ) {

                    return
                        $a['student_id']
                        <=>
                        $b['student_id'];
                }


                if (
                    $ranking_method === 'Aggregate'
                ) {

                    return
                        ($a['ranking_value']
                            >
                         $b['ranking_value'])
                            ? 1
                            : -1;
                }


                return
                    ($a['ranking_value']
                        <
                     $b['ranking_value'])
                        ? 1
                        : -1;
            }
        );


        $position = 1;


        foreach (
            $ranking_rows
            as $rank_row
        ) {

            if (
                (int)
                $rank_row['student_id']
                === $student_id
            ) {

                $student_position =
                    $position;

                break;
            }


            $position++;
        }
    }
}


/*
|--------------------------------------------------------------------------
| AGGREGATE
|--------------------------------------------------------------------------
*/

$aggregate = null;

$division = null;


if (
    (int)
    ($report_profile['show_aggregate'] ?? 0)
    === 1
) {

    if ($aggregate_count > 0) {

        /*
         * THIS IS THE CORRECT AGGREGATE.
         *
         * It comes from the grades.
         *
         * Example:
         *
         * D1 = 1
         * D1 = 1
         * D2 = 2
         *
         * Aggregate = 4
         */
        $aggregate =
            $total_aggregate;
    }
}


/*
|--------------------------------------------------------------------------
| DIVISION
|--------------------------------------------------------------------------
|
| Current division boundaries are retained exactly as before.
|--------------------------------------------------------------------------
*/

if (
    (int)
    ($report_profile['show_division'] ?? 0)
    === 1
    &&
    $aggregate !== null
) {

    if ($aggregate <= 12) {

        $division = 'I';

    } elseif ($aggregate <= 24) {

        $division = 'II';

    } elseif ($aggregate <= 28) {

        $division = 'III';

    } elseif ($aggregate <= 32) {

        $division = 'IV';

    } else {

        $division = 'U';
    }
}


/*
|--------------------------------------------------------------------------
| DISPLAY FLAGS
|--------------------------------------------------------------------------
*/

$show_marks =
    (int)
    ($report_profile['show_marks'] ?? 1)
    === 1;


$show_grade =
    (int)
    ($report_profile['show_grade'] ?? 1)
    === 1;


$show_points =
    (int)
    ($report_profile['show_points'] ?? 0)
    === 1;


$show_remark =
    (int)
    ($report_profile['show_remark'] ?? 1)
    === 1;


$show_total =
    (int)
    ($report_profile['show_total'] ?? 1)
    === 1;


$show_average =
    (int)
    ($report_profile['show_average'] ?? 0)
    === 1;


$show_aggregate =
    (int)
    ($report_profile['show_aggregate'] ?? 0)
    === 1;


$show_division =
    (int)
    ($report_profile['show_division'] ?? 0)
    === 1;


$show_position =
    (int)
    ($report_profile['show_position'] ?? 1)
    === 1;


/*
|--------------------------------------------------------------------------
| VISUAL PROFILE
|--------------------------------------------------------------------------
*/

$profile_class = match ($report_type) {

    'lower_primary'
        => 'report-lower-primary',

    'upper_primary'
        => 'report-upper-primary',

    'olevel'
        => 'report-olevel',

    'alevel'
        => 'report-alevel',

    'university'
        => 'report-university',

    default
        => 'report-general',
};


$level_label = match ($report_type) {

    'lower_primary'
        => 'LOWER PRIMARY',

    'upper_primary'
        => 'UPPER PRIMARY',

    'olevel'
        => 'ORDINARY LEVEL',

    'alevel'
        => 'ADVANCED LEVEL',

    'university'
        => 'UNIVERSITY',

    default
        => 'ACADEMIC REPORT',
};


$logo =
    !empty($school['logo'])
        ? '../uploads/logos/' . $school['logo']
        : '';

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= report_escape(
            $school['school_name']
                ?? 'School'
        ); ?>

        -

        Report Card
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        :root {

            --primary:
                <?= report_escape(
                    $school['primary_color']
                    ?? '#17365D'
                ); ?>;

            --secondary:
                <?= report_escape(
                    $school['secondary_color']
                    ?? '#D9A441'
                ); ?>;

            --ink: #172033;

            --muted: #64748b;

            --line: #d9e1ea;

            --soft: #f5f7fa;
        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background: #eef2f6;

            color: var(--ink);

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;
        }


        /*
        |--------------------------------------------------------------------------
        | SCREEN ACTIONS
        |--------------------------------------------------------------------------
        */

        .screen-actions {

            max-width: 1050px;

            margin:
                25px
                auto
                10px;

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 10px;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN REPORT
        |--------------------------------------------------------------------------
        */

        .report-card {

            width: 210mm;

            min-height: 297mm;

            margin:
                20px
                auto
                40px;

            padding: 16mm;

            background: #fff;

            box-shadow:
                0 10px 35px
                rgba(
                    15,
                    23,
                    42,
                    .12
                );

            border-radius: 12px;

            position: relative;

            overflow: hidden;
        }


        .report-card::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            right: 0;

            height: 7px;

            background:
                linear-gradient(
                    90deg,
                    var(--primary),
                    var(--secondary)
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SCHOOL HEADER
        |--------------------------------------------------------------------------
        */

        .school-header {

            display: grid;

            grid-template-columns:
                90px
                1fr
                120px;

            align-items: center;

            gap: 18px;

            padding-bottom: 15px;

            border-bottom:
                2px solid
                var(--primary);
        }


        .school-logo {

            width: 78px;

            height: 78px;

            object-fit: contain;

            display: block;

            margin: auto;
        }


        .school-title {

            text-align: center;
        }


        .school-title h1 {

            margin: 0;

            font-size: 25px;

            font-weight: 800;

            letter-spacing: .4px;

            text-transform: uppercase;
        }


        .school-title .motto {

            color: var(--primary);

            font-weight: 600;

            font-style: italic;

            margin-top: 4px;

            font-size: 12px;
        }


        .school-contact {

            text-align: center;

            font-size: 10px;

            color: var(--muted);

            line-height: 1.45;
        }


        /*
        |--------------------------------------------------------------------------
        | DOCUMENT HEADING
        |--------------------------------------------------------------------------
        */

        .document-heading {

            text-align: center;

            margin:
                17px
                0;
        }


        .document-heading .level {

            display: inline-block;

            padding:
                5px
                14px;

            background:
                var(--primary);

            color: #fff;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: 1.2px;
        }


        .document-heading h2 {

            margin:
                8px
                0
                3px;

            font-size: 20px;

            font-weight: 800;

            letter-spacing: .5px;
        }


        .document-heading p {

            margin: 0;

            color: var(--muted);

            font-size: 11px;
        }


        /*
        |--------------------------------------------------------------------------
        | STUDENT INFORMATION
        |--------------------------------------------------------------------------
        */

        .student-panel {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            border:
                1px solid
                var(--line);

            border-radius: 9px;

            overflow: hidden;

            margin-bottom: 18px;
        }


        .student-field {

            padding:
                8px
                12px;

            border-bottom:
                1px solid
                var(--line);
        }


        .student-field:nth-child(odd) {

            border-right:
                1px solid
                var(--line);
        }


        .student-field:nth-last-child(-n+2) {

            border-bottom: 0;
        }


        .student-field .label {

            display: block;

            font-size: 8px;

            color: var(--muted);

            text-transform: uppercase;

            letter-spacing: .7px;

            margin-bottom: 2px;
        }


        .student-field .value {

            font-size: 12px;

            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | SECTION TITLES
        |--------------------------------------------------------------------------
        */

        .section-title {

            margin:
                18px
                0
                8px;

            padding:
                8px
                12px;

            background:
                var(--soft);

            border-left:
                4px solid
                var(--primary);

            font-size: 11px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: .8px;
        }


        /*
        |--------------------------------------------------------------------------
        | RESULTS TABLE
        |--------------------------------------------------------------------------
        */

        .results {

            width: 100%;

            border-collapse:
                collapse;

            margin-top: 4px;
        }


        .results th {

            background:
                var(--primary);

            color: #fff;

            border:
                1px solid
                var(--primary);

            padding:
                8px
                6px;

            font-size: 9px;

            text-transform: uppercase;

            letter-spacing: .4px;

            text-align: center;
        }


        .results td {

            border:
                1px solid
                var(--line);

            padding:
                7px
                6px;

            font-size: 10px;

            vertical-align: middle;
        }


        .results td:not(:first-child) {

            text-align: center;
        }


        .results tbody tr:nth-child(even) {

            background:
                #fafbfd;
        }


        .subject-name {

            font-weight: 650;
        }


        .grade-pill {

            display: inline-block;

            min-width: 32px;

            padding:
                3px
                7px;

            border-radius: 20px;

            background:
                #edf2f7;

            font-weight: 800;
        }


        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    4,
                    1fr
                );

            gap: 8px;

            margin-top: 14px;
        }


        .summary-item {

            border:
                1px solid
                var(--line);

            border-radius: 8px;

            padding: 9px;

            text-align: center;

            background: #fff;
        }


        .summary-item .label {

            display: block;

            font-size: 8px;

            text-transform: uppercase;

            color: var(--muted);

            letter-spacing: .6px;

            margin-bottom: 3px;
        }


        .summary-item .value {

            font-size: 16px;

            font-weight: 800;

            color:
                var(--primary);
        }


        /*
        |--------------------------------------------------------------------------
        | GRADING INFORMATION
        |--------------------------------------------------------------------------
        */

        .grading-box {

            margin-top: 16px;

            padding:
                9px
                12px;

            border:
                1px solid
                var(--line);

            border-radius: 8px;

            background:
                var(--soft);

            font-size: 9px;
        }


        .grading-box strong {

            color:
                var(--primary);
        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURES
        |--------------------------------------------------------------------------
        */

        .signatures {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 50px;

            margin-top: 50px;
        }


        .signature {

            text-align: center;

            font-size: 9px;

            color: #334155;
        }


        .signature-line {

            border-top:
                1px solid
                #334155;

            margin-bottom: 6px;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer-note {

            margin-top: 22px;

            padding-top: 8px;

            border-top:
                1px solid
                var(--line);

            text-align: center;

            font-size: 8px;

            color: var(--muted);
        }


        /*
        |--------------------------------------------------------------------------
        | WARNING
        |--------------------------------------------------------------------------
        */

        .status-warning {

            padding:
                10px
                12px;

            border:
                1px solid
                #f1c40f;

            background:
                #fff9db;

            border-radius: 7px;

            color:
                #6b5600;

            font-size: 10px;

            margin-bottom: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | PROFILE-SPECIFIC VISUAL TOUCHES
        |--------------------------------------------------------------------------
        */

        .report-upper-primary
        .document-heading
        .level {

            border-radius: 5px;
        }


        .report-upper-primary
        .summary-item {

            background:
                #f8fafc;
        }


        .report-olevel
        .results th {

            letter-spacing: .8px;
        }


        .report-alevel
        .summary-item
        .value {

            font-size: 18px;
        }


        .report-university
        .student-panel {

            border-radius: 5px;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 900px) {

            .report-card {

                width: 96%;

                min-height: auto;

                padding: 25px;
            }


            .school-header {

                grid-template-columns:
                    1fr;
            }


            .student-panel,
            .summary-grid,
            .signatures {

                grid-template-columns:
                    1fr;
            }


            .student-field:nth-child(odd) {

                border-right: 0;
            }


            .student-field:nth-last-child(-n+2) {

                border-bottom:
                    1px solid
                    var(--line);
            }


            .student-field:last-child {

                border-bottom: 0;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */

        @media print {

            @page {

                size: A4;

                margin: 0;
            }


            body {

                background: #fff;
            }


            .screen-actions {

                display: none !important;
            }


            .report-card {

                width: 210mm;

                min-height: 297mm;

                margin: 0;

                padding: 14mm;

                box-shadow: none;

                border-radius: 0;
            }
        }

    </style>

</head>


<body>


<!-- =============================================================
     SCREEN ACTIONS
============================================================== -->

<div class="screen-actions">

    <div>

        <a
            href="javascript:history.back()"
            class="btn btn-outline-secondary btn-sm"
        >
            ← Back
        </a>

    </div>


    <div>

        <button
            onclick="window.print()"
            class="btn btn-primary btn-sm"
        >
            🖨 Print Report Card
        </button>

    </div>

</div>



<!-- =============================================================
     REPORT CARD
============================================================== -->

<div class="report-card <?= report_escape($profile_class); ?>">


    <!-- =========================================================
         SCHOOL HEADER
    ========================================================== -->

    <div class="school-header">


        <div>

            <?php if ($logo): ?>

                <img
                    src="<?= report_escape($logo); ?>"
                    class="school-logo"
                    alt="School Logo"
                >

            <?php endif; ?>

        </div>


        <div class="school-title">

            <h1>

                <?= report_escape(
                    $school['school_name']
                    ?? 'SCHOOL NAME'
                ); ?>

            </h1>


            <?php if (!empty($school['motto'])): ?>

                <div class="motto">

                    <?= report_escape(
                        $school['motto']
                    ); ?>

                </div>

            <?php endif; ?>

        </div>


        <div class="school-contact">

            <?= report_escape(
                $school['address']
                ?? ''
            ); ?>


            <?php if (!empty($school['phone'])): ?>

                <br>

                <?= report_escape(
                    $school['phone']
                ); ?>

            <?php endif; ?>


            <?php if (!empty($school['email'])): ?>

                <br>

                <?= report_escape(
                    $school['email']
                ); ?>

            <?php endif; ?>

        </div>

    </div>



    <!-- =========================================================
         DOCUMENT HEADING
    ========================================================== -->

    <div class="document-heading">


        <span class="level">

            <?= report_escape(
                $level_label
            ); ?>

        </span>


        <h2>

            STUDENT REPORT CARD

        </h2>


        <p>

            <?= report_escape(
                $profile_name
            ); ?>

            &nbsp; • &nbsp;

            <?= report_escape(
                $period['academic_year']
                ?? ''
            ); ?>

            &nbsp; • &nbsp;

            <?= report_escape(
                $period['period_name']
                ?? ''
            ); ?>

        </p>

    </div>



    <!-- =========================================================
         STUDENT INFORMATION
============================================================== -->

    <div class="student-panel">


        <div class="student-field">

            <span class="label">
                Student Name
            </span>

            <span class="value">

                <?= report_escape(
                    $student['full_name']
                    ?? ''
                ); ?>

            </span>

        </div>


        <div class="student-field">

            <span class="label">
                Registration Number
            </span>

            <span class="value">

                <?= report_escape(
                    $student['reg_no']
                    ?? ''
                ); ?>

            </span>

        </div>


        <div
            class="student-field"
            style="grid-column: 1 / -1;"
        >

            <span class="label">
                Class / Level
            </span>

            <span class="value">

                <?= report_escape(
                    $student['class']
                    ?? ''
                ); ?>

            </span>

        </div>


    </div>



    <!-- =========================================================
         PROFILE WARNINGS
    ========================================================== -->

    <?php if (!$report_profile): ?>

        <div class="status-warning">

            <strong>
                Report profile not assigned.
            </strong>

            This academic group does not currently have
            a report-card profile.

        </div>

    <?php elseif (!$grading_system): ?>

        <div class="status-warning">

            <strong>
                Grading system not configured.
            </strong>

            The assigned report profile does not have
            a valid grading system.

        </div>

    <?php endif; ?>



    <!-- =========================================================
         ACADEMIC PERFORMANCE
    ========================================================== -->

    <div class="section-title">

        Academic Performance

    </div>


    <table class="results">


        <thead>

            <tr>


                <th style="width: 35px;">
                    #
                </th>


                <th style="text-align:left;">
                    Subject
                </th>


                <?php if ($show_marks): ?>

                    <th>
                        Marks
                    </th>

                <?php endif; ?>


                <?php if ($show_grade): ?>

                    <th>
                        Grade
                    </th>

                <?php endif; ?>


                <?php if ($show_points): ?>

                    <th>
                        Points
                    </th>

                <?php endif; ?>


                <?php if ($show_remark): ?>

                    <th>
                        Remark
                    </th>

                <?php endif; ?>


            </tr>

        </thead>


        <tbody>


        <?php if (count($subjects_data) > 0): ?>


            <?php foreach (
                $subjects_data
                as $index => $row
            ): ?>


                <tr>


                    <td>

                        <?= $index + 1; ?>

                    </td>


                    <td
                        class="subject-name"
                        style="text-align:left;"
                    >

                        <?= report_escape(
                            $row['subject_name']
                        ); ?>

                    </td>


                    <?php if ($show_marks): ?>

                        <td>

                            <?= number_format(
                                $row['marks'],
                                2
                            ); ?>

                        </td>

                    <?php endif; ?>


                    <?php if ($show_grade): ?>

                        <td>


                            <?php if (
                                $row['grade']
                                !== 'N/A'
                            ): ?>


                                <span
                                    class="grade-pill"
                                >

                                    <?= report_escape(
                                        $row['grade']
                                    ); ?>

                                </span>


                            <?php else: ?>


                                <span
                                    class="text-danger"
                                >

                                    N/A

                                </span>


                            <?php endif; ?>


                        </td>

                    <?php endif; ?>


                    <?php if ($show_points): ?>

                        <td>


                            <?php if (
                                $row['points']
                                !== null
                                &&
                                $row['points']
                                !== ''
                            ): ?>

                                <?= number_format(
                                    (float)
                                    $row['points'],
                                    2
                                ); ?>

                            <?php else: ?>

                                —

                            <?php endif; ?>


                        </td>

                    <?php endif; ?>


                    <?php if ($show_remark): ?>

                        <td>

                            <?= report_escape(
                                $row['remark']
                                ?: '—'
                            ); ?>

                        </td>

                    <?php endif; ?>


                </tr>


            <?php endforeach; ?>


        <?php else: ?>


            <tr>

                <td
                    colspan="6"
                    style="
                        text-align:center;
                        padding:25px;
                    "
                    class="text-muted"
                >

                    No marks have been entered
                    for this student in this
                    academic period.

                </td>

            </tr>


        <?php endif; ?>


        </tbody>

    </table>



    <!-- =========================================================
         PERFORMANCE SUMMARY
    ========================================================== -->

    <?php

    $summary_items = [];


    if ($show_total) {

        $summary_items[] = [

            'label'
                => 'Total Marks',

            'value'
                => number_format(
                    $total,
                    2
                ),

        ];
    }


    if ($show_average) {

        $summary_items[] = [

            'label'
                => 'Average',

            'value'
                => number_format(
                    $average,
                    2
                ) . '%',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CORRECT AGGREGATE DISPLAY
    |--------------------------------------------------------------------------
    |
    | This displays $aggregate, which is now calculated from the grade.
    |
    |--------------------------------------------------------------------------
    */

    if (
        $show_aggregate
        &&
        $aggregate !== null
    ) {

        $summary_items[] = [

            'label'
                => 'Aggregate',

            'value'
                => number_format(
                    $aggregate,
                    2
                ),

        ];
    }


    if (
        $show_division
        &&
        $division !== null
    ) {

        $summary_items[] = [

            'label'
                => 'Division',

            'value'
                => $division,

        ];
    }


    /*
     * Total Points remains separate.
     */
    if (
        $show_points
        &&
        $total_points > 0
    ) {

        $summary_items[] = [

            'label'
                => 'Total Points',

            'value'
                => number_format(
                    $total_points,
                    2
                ),

        ];
    }


    if ($show_position) {

        $summary_items[] = [

            'label'
                => 'Position',

            'value'
                => $student_position,

        ];
    }


    if (
        $points_count > 0
        &&
        $report_type === 'university'
    ) {

        $summary_items[] = [

            'label'
                => 'Average Points',

            'value'
                => number_format(
                    $average_points,
                    2
                ),

        ];
    }

    ?>


    <?php if (
        count($summary_items) > 0
    ): ?>


        <div class="section-title">

            Performance Summary

        </div>


        <div class="summary-grid">


            <?php foreach (
                $summary_items
                as $item
            ): ?>


                <div class="summary-item">


                    <span class="label">

                        <?= report_escape(
                            $item['label']
                        ); ?>

                    </span>


                    <span class="value">

                        <?= report_escape(
                            $item['value']
                        ); ?>

                    </span>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>



    <!-- =========================================================
         GRADING INFORMATION
    ========================================================== -->

    <?php if ($grading_system): ?>


        <div class="grading-box">

            <strong>
                Grading System:
            </strong>

            <?= report_escape(
                $grading_system['name']
            ); ?>


            <?php if (
                !empty(
                    $grading_system['description']
                )
            ): ?>

                &nbsp; — &nbsp;

                <?= report_escape(
                    $grading_system['description']
                ); ?>

            <?php endif; ?>

        </div>


    <?php endif; ?>



    <!-- =========================================================
         SIGNATURES
    ========================================================== -->

    <div class="signatures">


        <div class="signature">

            <div class="signature-line"></div>

            <strong>
                Class Teacher
            </strong>

            <br>

            Signature &amp; Date

        </div>


        <div class="signature">

            <div class="signature-line"></div>

            <strong>
                Head Teacher / Principal
            </strong>

            <br>

            Signature &amp; Date

        </div>


    </div>



    <!-- =========================================================
         FOOTER
    ========================================================== -->

    <div class="footer-note">

        Official Academic Report

        &nbsp; • &nbsp;

        <?= report_escape(
            $school['school_name']
            ?? 'School'
        ); ?>

        &nbsp; • &nbsp;

        <?= report_escape(
            $period['academic_year']
            ?? ''
        ); ?>

    </div>


</div>


</body>

</html>