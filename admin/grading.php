
<?php

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Grading Systems";

include '../includes/header.php';
include '../includes/navbar.php';


/*
|--------------------------------------------------------------------------
| MESSAGES
|--------------------------------------------------------------------------
*/

$message = "";
$message_type = "success";


/*
|--------------------------------------------------------------------------
| CREATE GRADING SYSTEM
|--------------------------------------------------------------------------
*/

if (isset($_POST['save_system'])) {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {

        $message = "Grading system name is required.";
        $message_type = "danger";

    } else {

        // Check duplicate name
        $check = mysqli_prepare(
            $conn,
            "SELECT grading_id
             FROM grading_systems
             WHERE name = ?"
        );

        mysqli_stmt_bind_param($check, "s", $name);
        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {

            $message = "A grading system with this name already exists.";
            $message_type = "danger";

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO grading_systems
                (name, description, status)
                VALUES (?, ?, 'Inactive')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $name,
                $description
            );

            if (mysqli_stmt_execute($stmt)) {

                log_activity(
                    $conn,
                    $_SESSION['user_id'],
                    "Created grading system: " . $name
                );

                $message = "Grading system created successfully.";

            } else {

                $message = "Failed to create grading system.";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}


/*
|--------------------------------------------------------------------------
| ACTIVATE GRADING SYSTEM
|--------------------------------------------------------------------------
*/

if (isset($_POST['activate_system'])) {

    $grading_id = (int) ($_POST['grading_id'] ?? 0);

    if ($grading_id > 0) {

        mysqli_begin_transaction($conn);

        try {

            // First deactivate all systems
            $deactivate = mysqli_prepare(
                $conn,
                "UPDATE grading_systems
                 SET status = 'Inactive'"
            );

            mysqli_stmt_execute($deactivate);
            mysqli_stmt_close($deactivate);


            // Activate selected system
            $activate = mysqli_prepare(
                $conn,
                "UPDATE grading_systems
                 SET status = 'Active'
                 WHERE grading_id = ?"
            );

            mysqli_stmt_bind_param(
                $activate,
                "i",
                $grading_id
            );

            mysqli_stmt_execute($activate);

            if (mysqli_stmt_affected_rows($activate) < 1) {
                throw new Exception("Grading system not found.");
            }

            mysqli_stmt_close($activate);

            mysqli_commit($conn);

            log_activity(
                $conn,
                $_SESSION['user_id'],
                "Activated grading system ID: " . $grading_id
            );

            $message = "Grading system activated successfully.";

        } catch (Exception $e) {

            mysqli_rollback($conn);

            $message = "Failed to activate grading system.";
            $message_type = "danger";
        }
    }
}


/*
|--------------------------------------------------------------------------
| ADD GRADING RULE
|--------------------------------------------------------------------------
*/

if (isset($_POST['save_rule'])) {

    $grading_id = (int) ($_POST['grading_id'] ?? 0);
    $grade = trim($_POST['grade'] ?? '');

    $min_mark = isset($_POST['min_mark'])
        ? (float) $_POST['min_mark']
        : 0;

    $max_mark = isset($_POST['max_mark'])
        ? (float) $_POST['max_mark']
        : 0;

    $points = ($_POST['points'] !== '' ?? false)
        ? (float) $_POST['points']
        : null;

    $remark = trim($_POST['remark'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | BASIC VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($grading_id <= 0) {

        $message = "Please select a grading system.";
        $message_type = "danger";

    } elseif ($grade === '') {

        $message = "Grade is required.";
        $message_type = "danger";

    } elseif ($min_mark < 0 || $max_mark > 100) {

        $message = "Marks must be between 0 and 100.";
        $message_type = "danger";

    } elseif ($min_mark > $max_mark) {

        $message = "Minimum mark cannot be greater than maximum mark.";
        $message_type = "danger";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CHECK OVERLAPPING RANGE
        |--------------------------------------------------------------------------
        |
        | Example:
        | Existing: 70 - 79
        | New:      75 - 85
        |
        | This must be rejected because the ranges overlap.
        |
        */

        $overlap = mysqli_prepare(
            $conn,
            "SELECT rule_id
             FROM grading_rules
             WHERE grading_id = ?
             AND min_mark <= ?
             AND max_mark >= ?"
        );

        mysqli_stmt_bind_param(
            $overlap,
            "idd",
            $grading_id,
            $max_mark,
            $min_mark
        );

        mysqli_stmt_execute($overlap);

        $overlap_result = mysqli_stmt_get_result($overlap);

        if (mysqli_num_rows($overlap_result) > 0) {

            $message = "This mark range overlaps with an existing grading rule.";
            $message_type = "danger";

        } else {

            /*
            |--------------------------------------------------------------------------
            | INSERT RULE
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO grading_rules
                (
                    grading_id,
                    grade,
                    min_mark,
                    max_mark,
                    points,
                    remark
                )
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "isddds",
                $grading_id,
                $grade,
                $min_mark,
                $max_mark,
                $points,
                $remark
            );

            if (mysqli_stmt_execute($stmt)) {

                log_activity(
                    $conn,
                    $_SESSION['user_id'],
                    "Added grading rule " .
                    $grade .
                    " to grading system ID: " .
                    $grading_id
                );

                $message = "Grading rule added successfully.";

            } else {

                $message = "Failed to add grading rule.";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($overlap);
    }
}


/*
|--------------------------------------------------------------------------
| DELETE GRADING RULE
|--------------------------------------------------------------------------
*/

if (isset($_POST['delete_rule'])) {

    $rule_id = (int) ($_POST['rule_id'] ?? 0);

    if ($rule_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT grade, grading_id
             FROM grading_rules
             WHERE rule_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $rule_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $rule = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if ($rule) {

            $delete = mysqli_prepare(
                $conn,
                "DELETE FROM grading_rules
                 WHERE rule_id = ?"
            );

            mysqli_stmt_bind_param(
                $delete,
                "i",
                $rule_id
            );

            if (mysqli_stmt_execute($delete)) {

                log_activity(
                    $conn,
                    $_SESSION['user_id'],
                    "Deleted grading rule " .
                    $rule['grade'] .
                    " from grading system ID: " .
                    $rule['grading_id']
                );

                $message = "Grading rule deleted successfully.";

            } else {

                $message = "Failed to delete grading rule.";
                $message_type = "danger";
            }

            mysqli_stmt_close($delete);

        } else {

            $message = "Grading rule not found.";
            $message_type = "danger";
        }
    }
}


/*
|--------------------------------------------------------------------------
| LOAD GRADING SYSTEMS
|--------------------------------------------------------------------------
*/

$systems = mysqli_query(
    $conn,
    "SELECT *
     FROM grading_systems
     ORDER BY
        status = 'Active' DESC,
        name ASC"
);


/*
|--------------------------------------------------------------------------
| LOAD RULES
|--------------------------------------------------------------------------
*/

$rules = mysqli_query(
    $conn,
    "SELECT
        r.rule_id,
        r.grading_id,
        r.grade,
        r.min_mark,
        r.max_mark,
        r.points,
        r.remark,
        g.name AS grading_name
     FROM grading_rules r
     INNER JOIN grading_systems g
        ON r.grading_id = g.grading_id
     ORDER BY
        g.name ASC,
        r.min_mark DESC"
);


/*
|--------------------------------------------------------------------------
| GET SYSTEM FOR RULE FORM
|--------------------------------------------------------------------------
*/

$rule_systems = mysqli_query(
    $conn,
    "SELECT grading_id, name
     FROM grading_systems
     ORDER BY name ASC"
);

?>

<div class="container-fluid py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                Grading Systems
            </h2>

            <p class="text-muted mb-0">
                Configure grades, marks, points and remarks used by report cards.
            </p>
        </div>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="alert alert-<?= e($message_type) ?> alert-dismissible fade show">

            <?= e($message) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <div class="row g-4">


        <!-- =========================================================
             CREATE GRADING SYSTEM
        ========================================================== -->

        <div class="col-lg-5">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-primary text-white">

                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i>
                        Create Grading System
                    </h5>

                </div>

                <div class="card-body">

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Grading System Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Example: Standard 5-Point Grading"
                                required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Description
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Describe this grading system..."></textarea>

                        </div>


                        <button
                            type="submit"
                            name="save_system"
                            class="btn btn-primary">

                            <i class="bi bi-save"></i>
                            Create Grading System

                        </button>

                    </form>

                </div>

            </div>


            <!-- =====================================================
                 ADD RULE
            ====================================================== -->

            <div class="card shadow-sm border-0 mt-4">

                <div class="card-header bg-success text-white">

                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i>
                        Add Grading Rule
                    </h5>

                </div>

                <div class="card-body">

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Grading System
                            </label>

                            <select
                                name="grading_id"
                                class="form-select"
                                required>

                                <option value="">
                                    Select grading system
                                </option>

                                <?php while ($system = mysqli_fetch_assoc($rule_systems)): ?>

                                    <option value="<?= (int) $system['grading_id'] ?>">

                                        <?= e($system['name']) ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Minimum Mark
                                </label>

                                <input
                                    type="number"
                                    name="min_mark"
                                    class="form-control"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="0"
                                    required>

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Maximum Mark
                                </label>

                                <input
                                    type="number"
                                    name="max_mark"
                                    class="form-control"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="49"
                                    required>

                            </div>

                        </div>


                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Grade
                            </label>

                            <input
                                type="text"
                                name="grade"
                                class="form-control"
                                placeholder="Example: A"
                                required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Points
                            </label>

                            <input
                                type="number"
                                name="points"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="Example: 5">

                        </div>


                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Remark
                            </label>

                            <input
                                type="text"
                                name="remark"
                                class="form-control"
                                placeholder="Example: Excellent">

                        </div>


                        <button
                            type="submit"
                            name="save_rule"
                            class="btn btn-success">

                            <i class="bi bi-plus-lg"></i>
                            Add Rule

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- =========================================================
             GRADING SYSTEMS
        ========================================================== -->

        <div class="col-lg-7">

            <div class="card shadow-sm border-0">

                <div class="card-header">

                    <h5 class="mb-0">
                        Existing Grading Systems
                    </h5>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="text-end">
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if (mysqli_num_rows($systems) > 0): ?>

                                    <?php while ($system = mysqli_fetch_assoc($systems)): ?>

                                        <tr>

                                            <td class="fw-semibold">

                                                <?= e($system['name']) ?>

                                            </td>

                                            <td>

                                                <?= e(
                                                    $system['description'] ?: '—'
                                                ) ?>

                                            </td>

                                            <td>

                                                <?php if ($system['status'] === 'Active'): ?>

                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-secondary">
                                                        Inactive
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td class="text-end">

                                                <?php if ($system['status'] !== 'Active'): ?>

                                                    <form
                                                        method="POST"
                                                        class="d-inline">

                                                        <input
                                                            type="hidden"
                                                            name="grading_id"
                                                            value="<?= (int) $system['grading_id'] ?>">

                                                        <button
                                                            type="submit"
                                                            name="activate_system"
                                                            class="btn btn-sm btn-outline-success"
                                                            onclick="return confirm('Activate this grading system? The currently active system will become inactive.');">

                                                            <i class="bi bi-check-circle"></i>
                                                            Activate

                                                        </button>

                                                    </form>

                                                <?php else: ?>

                                                    <span class="text-success fw-semibold">

                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Currently Active

                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="4"
                                            class="text-center text-muted py-4">

                                            No grading systems created yet.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 GRADING RULES
            ====================================================== -->

            <div class="card shadow-sm border-0 mt-4">

                <div class="card-header">

                    <h5 class="mb-0">
                        Grading Rules
                    </h5>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>System</th>
                                    <th>Range</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                    <th>Remark</th>
                                    <th class="text-end">
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if (mysqli_num_rows($rules) > 0): ?>

                                    <?php while ($rule = mysqli_fetch_assoc($rules)): ?>

                                        <tr>

                                            <td>

                                                <span class="fw-semibold">

                                                    <?= e($rule['grading_name']) ?>

                                                </span>

                                            </td>

                                            <td>

                                                <?= number_format(
                                                    (float) $rule['min_mark'],
                                                    2
                                                ) ?>

                                                -

                                                <?= number_format(
                                                    (float) $rule['max_mark'],
                                                    2
                                                ) ?>

                                            </td>

                                            <td>

                                                <span class="badge bg-primary">

                                                    <?= e($rule['grade']) ?>

                                                </span>

                                            </td>

                                            <td>

                                                <?= $rule['points'] !== null
                                                    ? number_format(
                                                        (float) $rule['points'],
                                                        2
                                                    )
                                                    : '—'
                                                ?>

                                            </td>

                                            <td>

                                                <?= e(
                                                    $rule['remark'] ?: '—'
                                                ) ?>

                                            </td>

                                            <td class="text-end">

                                                <form
                                                    method="POST"
                                                    class="d-inline">

                                                    <input
                                                        type="hidden"
                                                        name="rule_id"
                                                        value="<?= (int) $rule['rule_id'] ?>">

                                                    <button
                                                        type="submit"
                                                        name="delete_rule"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Delete this grading rule?');">

                                                        <i class="bi bi-trash"></i>

                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="6"
                                            class="text-center text-muted py-4">

                                            No grading rules created yet.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php include '../includes/footer.php'; ?>
