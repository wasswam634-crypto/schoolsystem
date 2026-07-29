<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();

    $timeout_duration = 1800;

if (
    isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration
) {
    session_unset();
    session_destroy();

    header("Location: /schlwb/login.php");
exit;
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();
}

function require_role($roles)
{
    $roles = (array) $roles;

    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $roles, true)) {
        header('Location: /schlwb/login.php');
exit;
        exit;
    }
}

function current_user_role()
{
    return $_SESSION['role'] ?? null;
}
?>
