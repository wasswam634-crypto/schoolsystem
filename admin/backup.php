<?php
include '../includes/config.php';
include '../includes/auth.php';

require_role('admin');

$database = "school-management";
$filename = "backup_" . date('Y-m-d_H-i-s') . ".sql";

header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=$filename");

$command =
    '"C:\xampp\mysql\bin\mysqldump.exe" ' .
    '-u root ' .
    $database;

passthru($command);

exit;
?>