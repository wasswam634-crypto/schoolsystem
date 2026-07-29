<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Debtors Report";

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">

    <div class="row">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">

            <h2 class="mb-4">
                Debtors Report
            </h2>

            <div class="card shadow">

                <div class="card-header bg-danger text-white">
                    Students With Outstanding Balances
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-striped">

                        <thead class="table-dark">

                            <tr>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Term</th>
                                <th>Academic Year</th>
                                <th>Amount Due</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $query = "
                        SELECT
                            students.full_name,
                            students.class,
                            fees.term,
                            fees.academic_year,
                            fees.amount_due,
                            fees.amount_paid,
                            fees.balance
                        FROM fees
                        INNER JOIN students
                            ON fees.student_id = students.student_id
                        WHERE fees.balance > 0
                        ORDER BY fees.balance DESC
                        ";

                        $result = mysqli_query($conn, $query);

                        if(mysqli_num_rows($result) > 0){

                            while($row = mysqli_fetch_assoc($result)){
                        ?>

                            <tr>

                                <td>
                                    <?= e($row['full_name']); ?>
                                </td>

                                <td>
                                    <?= e($row['class']); ?>
                                </td>

                                <td>
                                    <?= e($row['term']); ?>
                                </td>

                                <td>
                                    <?= e($row['academic_year']); ?>
                                </td>

                                <td>
                                    UGX <?= number_format($row['amount_due']); ?>
                                </td>

                                <td>
                                    UGX <?= number_format($row['amount_paid']); ?>
                                </td>

                                <td class="text-danger fw-bold">
                                    UGX <?= number_format($row['balance']); ?>
                                </td>

                            </tr>

                        <?php
                            }
                        }
                        else{
                        ?>

                            <tr>
                                <td colspan="7" class="text-center text-success">
                                    No debtors found.
                                </td>
                            </tr>

                        <?php
                        }
                        ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>