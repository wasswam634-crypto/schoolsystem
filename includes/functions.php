<?php
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($path)
{
    header("Location: $path");
    exit;
}

function grade_from_marks($marks)
{
    $marks = (int) $marks;

    if ($marks >= 80) return 'A';
    if ($marks >= 75) return 'B+';
    if ($marks >= 70) return 'B';
    if ($marks >= 65) return 'C+';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';

    return 'F';
}


function log_activity($conn, $user_id, $action)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO audit_logs (user_id, action)
         VALUES (?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $user_id,
        $action
    );

    mysqli_stmt_execute($stmt);
}



function get_section($conn, $page, $key)
{
    $result = mysqli_query(
        $conn,
        "SELECT *
         FROM website_sections
         WHERE page_name='$page'
         AND section_key='$key'
         LIMIT 1"
    );


    if(mysqli_num_rows($result) > 0)
    {
        return mysqli_fetch_assoc($result);
    }


    return false;
}


/* ================================
   FLASH SUCCESS MESSAGE
   ================================ */

function set_success($message)
{
    $_SESSION['success_message'] = $message;
}


function get_success()
{
    $message = $_SESSION['success_message'] ?? null;

    unset($_SESSION['success_message']);

    return $message;
}


/* ================================
   FLASH ERROR MESSAGE
   ================================ */

function set_error($message)
{
    $_SESSION['error_message'] = $message;
}


function get_error()
{
    $message = $_SESSION['error_message'] ?? null;

    unset($_SESSION['error_message']);

    return $message;
}





?>



