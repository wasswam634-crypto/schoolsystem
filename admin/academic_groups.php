<?php

include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Academic Groups";

include '../includes/header.php';
include '../includes/navbar.php';


$message = "";
$message_type = "success";


// =====================================================
// SAVE GROUP
// =====================================================

if (isset($_POST['save_group'])) {

    $group_name = trim($_POST['group_name'] ?? '');
    $group_type = trim($_POST['group_type'] ?? '');


    // =================================================
    // VALIDATION
    // =================================================

    if ($group_name === '') {

        $message = "Group name is required.";
        $message_type = "danger";

    } elseif ($group_type === '') {

        $message = "Please select a group type.";
        $message_type = "danger";

    } else {


        // =============================================
        // CHECK DUPLICATE
        // =============================================

        $check = mysqli_prepare(
            $conn,
            "SELECT group_id
             FROM academic_groups
             WHERE LOWER(TRIM(group_name)) = LOWER(TRIM(?))
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $group_name
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);


        if (mysqli_num_rows($result) > 0) {

            $message = "This academic group already exists.";
            $message_type = "danger";

        } else {


            // =========================================
            // INSERT GROUP
            // =========================================

            $stmt = mysqli_prepare(
                $conn,

                "INSERT INTO academic_groups
                (
                    group_name,
                    group_type,
                    status
                )
                VALUES
                (?, ?, 'Active')"
            );


            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $group_name,
                $group_type
            );


            if (mysqli_stmt_execute($stmt)) {

                log_activity(
                    $conn,
                    $_SESSION['user_id'],
                    "Created academic group " . $group_name
                );


                $message =
                    "Academic group '"
                    . e($group_name)
                    . "' created successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Failed to create academic group: "
                    . mysqli_error($conn);

                $message_type = "danger";
            }


            mysqli_stmt_close($stmt);
        }


        mysqli_stmt_close($check);
    }
}


// =====================================================
// GET GROUPS WITH STUDENT COUNTS
// =====================================================

$groups = mysqli_query(
    $conn,

    "SELECT
        g.group_id,
        g.group_name,
        g.group_type,
        g.status,
        COUNT(s.student_id) AS student_count

     FROM academic_groups g

     LEFT JOIN students s
        ON s.group_id = g.group_id
        AND s.status = 'Active'

     GROUP BY
        g.group_id,
        g.group_name,
        g.group_type,
        g.status

     ORDER BY g.group_id DESC"
);

?>



<div class="container-fluid">

<div class="row">


<?php include '../includes/admin_sidebar.php'; ?>


<div class="col-md-10 p-4">


<h2 class="mb-4">
    Academic Groups
</h2>



<!-- =====================================================
     MESSAGE
====================================================== -->

<?php if ($message): ?>

<div class="alert alert-<?= e($message_type); ?>">

    <?= $message; ?>

</div>

<?php endif; ?>



<!-- =====================================================
     ADD GROUP
====================================================== -->

<div class="card shadow">


<div class="card-header bg-primary text-white">

    Add Class / Course / Department

</div>


<div class="card-body">


<form method="POST">


<div class="row">


<!-- GROUP NAME -->

<div class="col-md-5">

<label class="form-label">

    Group Name

</label>


<input
    type="text"
    name="group_name"
    class="form-control"
    placeholder="Example: S6, 23, CS"
    required
>


<small class="text-muted">

    Enter the actual class or academic group name.

</small>

</div>



<!-- GROUP TYPE -->

<div class="col-md-4">


<label class="form-label">

    Group Type

</label>


<select
    name="group_type"
    class="form-control"
    required
>


<option value="Class">

    Class

</option>


<option value="Course">

    Course

</option>


<option value="Department">

    Department

</option>


</select>


</div>



<!-- SAVE -->

<div class="col-md-3 d-flex align-items-end">


<button
    type="submit"
    class="btn btn-success w-100"
    name="save_group"
>

    Save Group

</button>


</div>


</div>


</form>


</div>

</div>



<!-- =====================================================
     EXISTING GROUPS
====================================================== -->

<div class="card shadow mt-4">


<div class="card-header bg-dark text-white">

    Existing Groups

</div>


<div class="card-body">


<div class="table-responsive">


<table class="table table-bordered table-hover">


<thead>


<tr>

<th>#</th>

<th>Group Name</th>

<th>Type</th>

<th>Students</th>

<th>Status</th>

</tr>


</thead>


<tbody>


<?php if ($groups && mysqli_num_rows($groups) > 0): ?>


<?php

$count = 1;

while ($row = mysqli_fetch_assoc($groups)):

?>


<tr>


<td>

    <?= $count++; ?>

</td>


<td>

    <strong>

        <?= e($row['group_name']); ?>

    </strong>

</td>


<td>

    <?= e($row['group_type']); ?>

</td>


<td>

    <span class="badge bg-info">

        <?= (int)$row['student_count']; ?>

    </span>

</td>


<td>


<?php if ($row['status'] === "Active"): ?>


<span class="badge bg-success">

    Active

</span>


<?php else: ?>


<span class="badge bg-secondary">

    Inactive

</span>


<?php endif; ?>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
    colspan="5"
    class="text-center text-muted"
>

    No academic groups have been created yet.

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
