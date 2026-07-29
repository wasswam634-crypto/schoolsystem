<?php
session_start();

include 'database/connection.php';
include 'includes/functions.php';

if(isset($_POST['login'])){

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            user_id,
            username,
            password,
            role,
            failed_attempts,
            locked_until
         FROM users
         WHERE username = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $username
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if($user = mysqli_fetch_assoc($result)){

        /* CHECK IF ACCOUNT IS LOCKED */
        if(
            !empty($user['locked_until']) &&
            strtotime($user['locked_until']) > time()
        ){

            $error =
                "Account locked until "
                . $user['locked_until'];

        }
        else{

            $valid_password =
                password_verify(
                    $password,
                    $user['password']
                ) ||
                hash_equals(
                    $user['password'],
                    $password
                );

            if($valid_password){

                /* RESET FAILED ATTEMPTS */
                mysqli_query(
                    $conn,
                    "UPDATE users
                     SET failed_attempts = 0,
                         locked_until = NULL
                     WHERE user_id = {$user['user_id']}"
                );

                session_regenerate_id(true);

                $_SESSION['user_id'] =
                    (int)$user['user_id'];

                $_SESSION['username'] =
                    $user['username'];

                $_SESSION['role'] =
                    $user['role'];

                if($user['role'] == 'admin'){

                    redirect(
                        'admin/dashboard.php'
                    );

                }
                elseif($user['role'] == 'teacher'){

                    redirect(
                        'teacher/dashboard.php'
                    );

                }
                elseif($user['role'] == 'student'){

                    redirect(
                        'student/dashboard.php'
                    );

                }
                else{

                    session_destroy();

                    $error =
                        "Your account role is not configured.";
                }

            }
            else{

                $attempts =
                    $user['failed_attempts'] + 1;

                if($attempts >= 5){

                    $locked_until =
                        date(
                            'Y-m-d H:i:s',
                            strtotime('+15 minutes')
                        );

                    mysqli_query(
                        $conn,
                        "UPDATE users
                         SET failed_attempts = $attempts,
                             locked_until = '$locked_until'
                         WHERE user_id = {$user['user_id']}"
                    );

                    $error =
                        "Account locked for 15 minutes after 5 failed attempts.";

                }
                else{

                    mysqli_query(
                        $conn,
                        "UPDATE users
                         SET failed_attempts = $attempts
                         WHERE user_id = {$user['user_id']}"
                    );

                    $remaining =
                        5 - $attempts;

                    $error =
                        "Invalid username or password. "
                        . $remaining .
                        " attempts remaining.";
                }

            }

        }

    }
    else{

        $error =
            "Invalid username or password.";
    }

}
?>

<!DOCTYPE html>
<html>
<head>

<title>School Login</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-4">

<div class="card shadow">

<div class="card-header">

<h3>
School Portal Login
</h3>

</div>

<div class="card-body">

<?php if(isset($error)): ?>

<div class="alert alert-danger">

<?= e($error); ?>

</div>

<?php endif; ?>

<form method="POST">

<input
type="text"
name="username"
placeholder="Username"
class="form-control mb-3"
required>

<input
type="password"
name="password"
placeholder="Password"
class="form-control mb-3"
required>

<button
name="login"
class="btn btn-primary w-100">

Login

</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>