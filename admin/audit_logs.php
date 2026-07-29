<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "Audit Logs";

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid">

    <div class="row">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">

            <h2 class="mb-4">
                Audit Logs
            </h2>

            <div class="card shadow">

                <div class="card-header bg-dark text-white">
                    System Activity Log
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-striped">

                        <thead class="table-dark">

                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Date & Time</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $logs = mysqli_query(
                            $conn,
                            "SELECT
                                audit_logs.*,
                                users.username
                             FROM audit_logs
                             LEFT JOIN users
                             ON audit_logs.user_id = users.user_id
                             ORDER BY audit_logs.created_at DESC"
                        );

                        while($log = mysqli_fetch_assoc($logs)){
                        ?>

                        <tr>

                            <td>
                                <?= e($log['username'] ?? 'Unknown User'); ?>
                            </td>

                            <td>
                                <?= e($log['action']); ?>
                            </td>

                            <td>
                                <?= e($log['created_at']); ?>
                            </td>

                        </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>