<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Report Card Profiles";

$message = "";
$message_type = "success";


/*
|--------------------------------------------------------------------------
| CREATE REPORT CARD PROFILE
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['create_profile'])
) {

    $profile_name = trim($_POST['profile_name'] ?? '');
    $education_level = trim($_POST['education_level'] ?? '');
    $report_type = trim($_POST['report_type'] ?? '');
    $grading_system_id = (int) ($_POST['grading_system_id'] ?? 0);
    $ranking_method = trim($_POST['ranking_method'] ?? 'Total Marks');

    $show_marks = isset($_POST['show_marks']) ? 1 : 0;
    $show_grade = isset($_POST['show_grade']) ? 1 : 0;
    $show_points = isset($_POST['show_points']) ? 1 : 0;
    $show_remark = isset($_POST['show_remark']) ? 1 : 0;
    $show_total = isset($_POST['show_total']) ? 1 : 0;
    $show_average = isset($_POST['show_average']) ? 1 : 0;
    $show_aggregate = isset($_POST['show_aggregate']) ? 1 : 0;
    $show_division = isset($_POST['show_division']) ? 1 : 0;
    $show_position = isset($_POST['show_position']) ? 1 : 0;


    if (
        $profile_name === ''
        || $education_level === ''
        || $report_type === ''
        || $grading_system_id <= 0
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "danger";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "
            INSERT INTO report_card_profiles
            (
                profile_name,
                education_level,
                report_type,
                grading_system_id,
                ranking_method,
                show_marks,
                show_grade,
                show_points,
                show_remark,
                show_total,
                show_average,
                show_aggregate,
                show_division,
                show_position,
                status
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')
            "
        );

        /*
        | 3 strings
        | 1 integer
        | 1 string
        | 9 integers
        |
        | s s s i s i i i i i i i i i
        | = sssisiiiiiiiii
        */

        mysqli_stmt_bind_param(
            $stmt,
            "sssisiiiiiiiii",
            $profile_name,
            $education_level,
            $report_type,
            $grading_system_id,
            $ranking_method,
            $show_marks,
            $show_grade,
            $show_points,
            $show_remark,
            $show_total,
            $show_average,
            $show_aggregate,
            $show_division,
            $show_position
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $message = "Report card profile created successfully.";
        $message_type = "success";
    }
}


/*
|--------------------------------------------------------------------------
| DELETE REPORT CARD PROFILE
|--------------------------------------------------------------------------
|
| This is a PERMANENT deletion.
|
| First:
|   Remove the profile from every academic group.
|
| Then:
|   Delete the profile itself from report_card_profiles.
|
| The grading system is NOT deleted.
|
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_profile'])
) {

    $profile_id = (int) ($_POST['profile_id'] ?? 0);


    if ($profile_id <= 0) {

        $message = "Invalid report card profile.";
        $message_type = "danger";

    } else {

        try {

            mysqli_begin_transaction($conn);


            /*
            |--------------------------------------------------------------------------
            | STEP 1: GET PROFILE
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $conn,
                "
                SELECT
                    profile_id,
                    profile_name
                FROM report_card_profiles
                WHERE profile_id = ?
                LIMIT 1
                "
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $profile_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $profile = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);


            if (!$profile) {

                throw new Exception(
                    "The selected report card profile does not exist."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 2: REMOVE PROFILE FROM ALL ACADEMIC GROUPS
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $conn,
                "
                UPDATE academic_groups
                SET report_profile_id = NULL
                WHERE report_profile_id = ?
                "
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $profile_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);


            /*
            |--------------------------------------------------------------------------
            | STEP 3: DELETE THE PROFILE
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $conn,
                "
                DELETE FROM report_card_profiles
                WHERE profile_id = ?
                LIMIT 1
                "
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $profile_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);


            /*
            |--------------------------------------------------------------------------
            | STEP 4: COMMIT
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);


            $message =
                "Report card profile \""
                . htmlspecialchars(
                    $profile['profile_name'],
                    ENT_QUOTES,
                    'UTF-8'
                )
                . "\" was permanently deleted.";

            $message_type = "success";


        } catch (Throwable $e) {

            mysqli_rollback($conn);

            $message =
                "The report card profile could not be deleted. "
                . $e->getMessage();

            $message_type = "danger";
        }
    }
}


/*
|--------------------------------------------------------------------------
| REMOVE PROFILE FROM ONE ACADEMIC GROUP
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This DOES NOT delete the profile.
|
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['remove_group_assignment'])
) {

    $group_id = (int) ($_POST['group_id'] ?? 0);


    if ($group_id <= 0) {

        $message = "Invalid academic group.";
        $message_type = "danger";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "
            UPDATE academic_groups
            SET report_profile_id = NULL
            WHERE group_id = ?
            "
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $group_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        $message =
            "The report card profile was removed from the academic group. "
            . "The profile itself was not deleted.";

        $message_type = "success";
    }
}


/*
|--------------------------------------------------------------------------
| ASSIGN PROFILE TO ACADEMIC GROUP
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['assign_profile'])
) {

    $group_id = (int) ($_POST['group_id'] ?? 0);
    $profile_id = (int) ($_POST['profile_id'] ?? 0);


    if (
        $group_id <= 0
        || $profile_id <= 0
    ) {

        $message = "Please select both an academic group and a report card profile.";
        $message_type = "danger";

    } else {

        /*
        |--------------------------------------------------------------------------
        | VERIFY PROFILE EXISTS
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "
            SELECT profile_id
            FROM report_card_profiles
            WHERE profile_id = ?
            LIMIT 1
            "
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $profile_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $profile_exists = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$profile_exists) {

            $message = "The selected report card profile does not exist.";
            $message_type = "danger";

        } else {

            /*
            |--------------------------------------------------------------------------
            | ASSIGN
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $conn,
                "
                UPDATE academic_groups
                SET report_profile_id = ?
                WHERE group_id = ?
                "
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $profile_id,
                $group_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);


            $message =
                "Report card profile assigned to the academic group successfully.";

            $message_type = "success";
        }
    }
}


/*
|--------------------------------------------------------------------------
| LOAD GRADING SYSTEMS
|--------------------------------------------------------------------------
*/

$grading_systems = [];

$result = mysqli_query(
    $conn,
    "
    SELECT
        grading_id,
        name,
        description,
        status
    FROM grading_systems
    ORDER BY name ASC
    "
);

while ($row = mysqli_fetch_assoc($result)) {

    $grading_systems[] = $row;
}


/*
|--------------------------------------------------------------------------
| LOAD REPORT CARD PROFILES
|--------------------------------------------------------------------------
|
| We also count how many academic groups currently use each profile.
|
*/

$profiles = [];

$result = mysqli_query(
    $conn,
    "
    SELECT
        rcp.*,
        gs.name AS grading_name,
        COUNT(ag.group_id) AS assigned_groups
    FROM report_card_profiles rcp

    LEFT JOIN grading_systems gs
        ON gs.grading_id = rcp.grading_system_id

    LEFT JOIN academic_groups ag
        ON ag.report_profile_id = rcp.profile_id

    GROUP BY rcp.profile_id

    ORDER BY rcp.profile_name ASC
    "
);

while ($row = mysqli_fetch_assoc($result)) {

    $profiles[] = $row;
}


/*
|--------------------------------------------------------------------------
| LOAD ACADEMIC GROUPS
|--------------------------------------------------------------------------
*/

$groups = [];

$result = mysqli_query(
    $conn,
    "
    SELECT
        ag.group_id,
        ag.group_name,
        ag.group_type,
        ag.report_profile_id,
        rcp.profile_name
    FROM academic_groups ag

    LEFT JOIN report_card_profiles rcp
        ON rcp.profile_id = ag.report_profile_id

    ORDER BY ag.group_name ASC
    "
);

while ($row = mysqli_fetch_assoc($result)) {

    $groups[] = $row;
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

    <title>
        <?php echo htmlspecialchars($page_title); ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f7fb;
        }

        .page-header {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card-header {
            background: #ffffff;
            border-bottom: 1px solid #eeeeee;
            font-weight: 700;
            padding: 16px 20px;
        }

        .table th {
            white-space: nowrap;
            font-size: 14px;
        }

        .table td {
            vertical-align: middle;
        }

        .badge-status {
            font-size: 12px;
        }

        .form-check-label {
            font-size: 14px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 15px;
        }

        .small-muted {
            font-size: 13px;
            color: #6c757d;
        }

    </style>

</head>

<body>

<div class="container-fluid py-4">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="page-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h2 class="mb-1">
                    Report Card Profiles
                </h2>

                <p class="text-muted mb-0">
                    Configure report-card formats and assign them to academic groups.
                </p>

            </div>

        </div>

    </div>


    <!-- =========================================================
         MESSAGE
    ========================================================== -->

    <?php if ($message !== ''): ?>

        <div
            class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show"
            role="alert"
        >

            <?php echo $message; ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- =========================================================
         CREATE PROFILE
    ========================================================== -->

    <div class="card mb-4">

        <div class="card-header">

            Create Report Card Profile

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row g-3">

                    <!-- PROFILE NAME -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Profile Name
                        </label>

                        <input
                            type="text"
                            name="profile_name"
                            class="form-control"
                            placeholder="e.g. Primary Upper"
                            required
                        >

                    </div>


                    <!-- EDUCATION LEVEL -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Education Level
                        </label>

                        <select
                            name="education_level"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select education level
                            </option>

                            <option value="Primary">
                                Primary
                            </option>

                            <option value="Secondary">
                                Secondary
                            </option>

                            <option value="University">
                                University
                            </option>

                        </select>

                    </div>


                    <!-- REPORT TYPE -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Report Type
                        </label>

                        <input
                            type="text"
                            name="report_type"
                            class="form-control"
                            placeholder="e.g. primary_upper"
                            required
                        >

                    </div>


                    <!-- GRADING SYSTEM -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Grading System
                        </label>

                        <select
                            name="grading_system_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select grading system
                            </option>

                            <?php foreach ($grading_systems as $grading): ?>

                                <option
                                    value="<?php echo (int) $grading['grading_id']; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $grading['name']
                                    );
                                    ?>

                                    <?php if ($grading['status'] === 'Active'): ?>

                                        (Active)

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- RANKING -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Ranking Method
                        </label>

                        <select
                            name="ranking_method"
                            class="form-select"
                        >

                            <option value="Total Marks">
                                Total Marks
                            </option>

                            <option value="Average">
                                Average
                            </option>

                            <option value="Aggregate">
                                Aggregate
                            </option>

                            <option value="Total Points">
                                Total Points
                            </option>

                            <option value="GPA">
                                GPA
                            </option>

                        </select>

                    </div>

                </div>


                <hr class="my-4">


                <!-- DISPLAY OPTIONS -->

                <div class="section-title">
                    Report Display Options
                </div>

                <div class="row g-3">

                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_marks"
                                id="show_marks"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="show_marks"
                            >
                                Show Marks
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_grade"
                                id="show_grade"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="show_grade"
                            >
                                Show Grade
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_points"
                                id="show_points"
                            >

                            <label
                                class="form-check-label"
                                for="show_points"
                            >
                                Show Points
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_remark"
                                id="show_remark"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="show_remark"
                            >
                                Show Remark
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_total"
                                id="show_total"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="show_total"
                            >
                                Show Total
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_average"
                                id="show_average"
                            >

                            <label
                                class="form-check-label"
                                for="show_average"
                            >
                                Show Average
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_aggregate"
                                id="show_aggregate"
                            >

                            <label
                                class="form-check-label"
                                for="show_aggregate"
                            >
                                Show Aggregate
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_division"
                                id="show_division"
                            >

                            <label
                                class="form-check-label"
                                for="show_division"
                            >
                                Show Division
                            </label>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="show_position"
                                id="show_position"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="show_position"
                            >
                                Show Position / Rank
                            </label>

                        </div>

                    </div>

                </div>


                <div class="mt-4">

                    <button
                        type="submit"
                        name="create_profile"
                        class="btn btn-primary"
                    >

                        Create Profile

                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- =========================================================
         EXISTING PROFILES
    ========================================================== -->

    <div class="card mb-4">

        <div class="card-header">

            Existing Report Card Profiles

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="table-light">

                    <tr>

                        <th>
                            Profile
                        </th>

                        <th>
                            Education
                        </th>

                        <th>
                            Report Type
                        </th>

                        <th>
                            Grading
                        </th>

                        <th>
                            Ranking
                        </th>

                        <th>
                            Assigned Groups
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($profiles)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center text-muted py-4"
                            >

                                No report card profiles have been created yet.

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($profiles as $profile): ?>

                            <tr>

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $profile['profile_name']
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $profile['education_level']
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $profile['report_type']
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $profile['grading_name']
                                            ?? 'Not assigned'
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $profile['ranking_method']
                                    );
                                    ?>

                                </td>


                                <td>

                                    <span class="badge bg-secondary">

                                        <?php
                                        echo (int) $profile['assigned_groups'];
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if ($profile['status'] === 'Active'): ?>

                                        <span class="badge bg-success badge-status">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary badge-status">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <form
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm(
                                            'WARNING: This will permanently delete this report card profile and automatically remove it from every academic group using it.\\n\\nThe grading system will NOT be deleted.\\n\\nDo you want to continue?'
                                        );"
                                    >

                                        <input
                                            type="hidden"
                                            name="profile_id"
                                            value="<?php echo (int) $profile['profile_id']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_profile"
                                            class="btn btn-sm btn-outline-danger"
                                        >

                                            Delete

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- =========================================================
         ASSIGN PROFILE TO GROUP
    ========================================================== -->

    <div class="card mb-4">

        <div class="card-header">

            Assign Report Card Profile to Academic Group

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row g-3 align-items-end">

                    <div class="col-md-5">

                        <label class="form-label fw-semibold">
                            Academic Group
                        </label>

                        <select
                            name="group_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select academic group
                            </option>

                            <?php foreach ($groups as $group): ?>

                                <option
                                    value="<?php echo (int) $group['group_id']; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $group['group_name']
                                    );
                                    ?>

                                    <?php if (!empty($group['group_type'])): ?>

                                        -
                                        <?php
                                        echo htmlspecialchars(
                                            $group['group_type']
                                        );
                                        ?>

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-5">

                        <label class="form-label fw-semibold">
                            Report Card Profile
                        </label>

                        <select
                            name="profile_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select report card profile
                            </option>

                            <?php foreach ($profiles as $profile): ?>

                                <option
                                    value="<?php echo (int) $profile['profile_id']; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $profile['profile_name']
                                    );
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-md-2">

                        <button
                            type="submit"
                            name="assign_profile"
                            class="btn btn-primary w-100"
                        >

                            Assign

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- =========================================================
         CURRENT GROUP ASSIGNMENTS
    ========================================================== -->

    <div class="card">

        <div class="card-header">

            Current Academic Group Assignments

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="table-light">

                    <tr>

                        <th>
                            Academic Group
                        </th>

                        <th>
                            Group Type
                        </th>

                        <th>
                            Assigned Report Card Profile
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($groups)): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="text-center text-muted py-4"
                            >

                                No academic groups found.

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($groups as $group): ?>

                            <tr>

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $group['group_name']
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $group['group_type']
                                            ?? '-'
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php if (!empty($group['profile_name'])): ?>

                                        <span class="badge bg-primary">

                                            <?php
                                            echo htmlspecialchars(
                                                $group['profile_name']
                                            );
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            No profile assigned

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if (!empty($group['report_profile_id'])): ?>

                                        <form
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm(
                                                'Remove this report card profile from this academic group? The profile itself will NOT be deleted.'
                                            );"
                                        >

                                            <input
                                                type="hidden"
                                                name="group_id"
                                                value="<?php echo (int) $group['group_id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="remove_group_assignment"
                                                class="btn btn-sm btn-outline-warning"
                                            >

                                                Remove From Group

                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span class="text-muted small">

                                            No assignment

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>

