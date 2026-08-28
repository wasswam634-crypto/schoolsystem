
<?php
include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');
?>

<!DOCTYPE html>
<html>
<head>

    <title>View Students</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        .table-container {
            overflow-x: auto;
            white-space: nowrap;
        }

        .student-photo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }

        th {
            background-color: #212529 !important;
            color: white !important;
        }

        td {
            vertical-align: middle;
        }

    </style>

</head>

<body>

<div class="container-fluid mt-5">

    <h2 class="mb-4">All Students</h2>

    <div class="table-container">

        <table class="table table-bordered table-striped table-hover">

            <thead>

            <tr>

                <th>ID</th>
                <th>Photo</th>
                <th>Reg No</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Class</th>
                <th>Stream</th>
                <th>Class / Course</th>
                <th>Date of Birth</th>
                <th>Admission Date</th>
                <th>Status</th>

                <th>Nationality</th>
                <th>Religion</th>

                <th>Parent / Guardian</th>
                <th>Parent Contact</th>
                <th>Parent Email</th>
                <th>Parent Address</th>
                <th>Occupation</th>

                <th>Blood Group</th>
                <th>Allergies</th>
                <th>Medical Condition</th>

                <th>Previous School</th>
                <th>Notes</th>

                <th>Actions</th>

            </tr>

            </thead>

            <tbody>

            <?php

            /*
             * Join students with academic_groups
             * so we can display group_name instead of group_id.
             */

            $sql = "
                SELECT 
                    students.*,
                    academic_groups.group_name
                FROM students

                LEFT JOIN academic_groups
                    ON students.group_id = academic_groups.group_id

                ORDER BY students.full_name
            ";

            $result = mysqli_query($conn, $sql);

            if (!$result) {

                die("Database error: " . mysqli_error($conn));

            }

            while ($row = mysqli_fetch_assoc($result)) {

            ?>

            <tr>

                <!-- ID -->

                <td>
                    <?php echo e($row['student_id']); ?>
                </td>


                <!-- PHOTO -->

                <td>

                    <?php if (!empty($row['photo'])): ?>

                        <img
                            src="../uploads/students/<?php echo e($row['photo']); ?>"
                            class="student-photo"
                            alt="Student Photo">

                    <?php else: ?>

                        No Photo

                    <?php endif; ?>

                </td>


                <!-- REGISTRATION NUMBER -->

                <td>
                    <?php echo e($row['reg_no']); ?>
                </td>


                <!-- FULL NAME -->

                <td>
                    <strong>
                        <?php echo e($row['full_name']); ?>
                    </strong>
                </td>


                <!-- GENDER -->

                <td>
                    <?php echo e($row['gender']); ?>
                </td>


                <!-- CLASS -->

                <td>
                    <?php echo e($row['class']); ?>
                </td>


                <!-- STREAM -->

                <td>
                    <?php echo e($row['stream']); ?>
                </td>


                <!-- CLASS / COURSE -->

                <td>

                    <?php

                    if (!empty($row['group_name'])) {

                        echo e($row['group_name']);

                    } else {

                        echo "Not assigned";

                    }

                    ?>

                </td>


                <!-- DATE OF BIRTH -->

                <td>
                    <?php echo e($row['dob']); ?>
                </td>


                <!-- ADMISSION DATE -->

                <td>
                    <?php echo e($row['admission_date']); ?>
                </td>


                <!-- STATUS -->

                <td>

                    <?php if ($row['status'] == 'Active'): ?>

                        <span class="badge bg-success">
                            Active
                        </span>

                    <?php else: ?>

                        <span class="badge bg-secondary">
                            <?php echo e($row['status']); ?>
                        </span>

                    <?php endif; ?>

                </td>


                <!-- NATIONALITY -->

                <td>
                    <?php echo e($row['nationality']); ?>
                </td>


                <!-- RELIGION -->

                <td>
                    <?php echo e($row['religion']); ?>
                </td>


                <!-- PARENT NAME -->

                <td>
                    <?php echo e($row['parent_name']); ?>
                </td>


                <!-- PARENT CONTACT -->

                <td>
                    <?php echo e($row['parent_contact']); ?>
                </td>


                <!-- PARENT EMAIL -->

                <td>
                    <?php echo e($row['parent_email']); ?>
                </td>


                <!-- PARENT ADDRESS -->

                <td>
                    <?php echo e($row['parent_address']); ?>
                </td>


                <!-- OCCUPATION -->

                <td>
                    <?php echo e($row['occupation']); ?>
                </td>


                <!-- BLOOD GROUP -->

                <td>
                    <?php echo e($row['blood_group']); ?>
                </td>


                <!-- ALLERGIES -->

                <td>
                    <?php echo e($row['allergies']); ?>
                </td>


                <!-- MEDICAL CONDITION -->

                <td>
                    <?php echo e($row['medical_condition']); ?>
                </td>


                <!-- PREVIOUS SCHOOL -->

                <td>
                    <?php echo e($row['previous_school']); ?>
                </td>


                <!-- NOTES -->

                <td>
                    <?php echo e($row['notes']); ?>
                </td>


                <!-- ACTIONS -->

                <td>

                    <a
                        href="edit_student.php?id=<?php echo e($row['student_id']); ?>"
                        class="btn btn-warning btn-sm">

                        Edit

                    </a>


                    <a
                        href="delete_student.php?id=<?php echo e($row['student_id']); ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Are you sure you want to delete this student?');">

                        Delete

                    </a>

                </td>

            </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
```
