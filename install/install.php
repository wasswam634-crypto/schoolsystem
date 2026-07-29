<?php
session_start();

if(file_exists("lock.php")){
    die("The system has already been installed.");
}

$host=$_SESSION['db_host'];
$db=$_SESSION['db_name'];
$user=$_SESSION['db_user'];
$pass=$_SESSION['db_pass'];

$conn=mysqli_connect($host,$user,$pass,$db);

if(!$conn){
    die("Database connection failed.");
}

/* Import Database */

$sql=file_get_contents("school.sql");

$queries=explode(";",$sql);

foreach($queries as $query){

    $query=trim($query);

    if(!empty($query)){
        mysqli_query($conn,$query);
    }

}

/* Save School Settings */

$school_name=mysqli_real_escape_string(
$conn,
$_SESSION['school_name']
);

$motto=mysqli_real_escape_string(
$conn,
$_SESSION['motto']
);

$address=mysqli_real_escape_string(
$conn,
$_SESSION['address']
);

$phone=mysqli_real_escape_string(
$conn,
$_SESSION['phone']
);

$email=mysqli_real_escape_string(
$conn,
$_SESSION['email']
);

$theme=mysqli_real_escape_string(
$conn,
$_SESSION['theme']
);

$logo=$_SESSION['logo'] ?? '';

mysqli_query(
$conn,
"INSERT INTO school_settings
(
school_name,
motto,
address,
phone,
email,
logo,
theme
)
VALUES
(
'$school_name',
'$motto',
'$address',
'$phone',
'$email',
'$logo',
'$theme'
)"
);

/* Create Administrator */

$fullname=mysqli_real_escape_string(
$conn,
$_SESSION['admin_fullname']
);

$username=mysqli_real_escape_string(
$conn,
$_SESSION['admin_username']
);

$email=mysqli_real_escape_string(
$conn,
$_SESSION['admin_email']
);

$password=mysqli_real_escape_string(
$conn,
$_SESSION['admin_password']
);

mysqli_query(
$conn,
"INSERT INTO users
(
full_name,
username,
email,
password,
role
)
VALUES
(
'$fullname',
'$username',
'$email',
'$password',
'admin'
)"
);

/* Generate config.php */

$config="<?php

\$host='".$host."';

\$username='".$user."';

\$password='".$pass."';

\$database='".$db."';

\$conn=mysqli_connect(
\$host,
\$username,
\$password,
\$database
);

if(!\$conn){
    die('Database Connection Failed');
}

?>";

file_put_contents(
"../includes/config.php",
$config
);

/* Lock Installer */

file_put_contents(
"lock.php",
"<?php die('Installer Locked'); ?>"
);

header("Location: finish.php");
exit;
?>