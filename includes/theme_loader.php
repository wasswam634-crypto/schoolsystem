<?php

$result = mysqli_query(
    $conn,
    "SELECT * FROM school_settings LIMIT 1"
);

$settings = mysqli_fetch_assoc($result);


$theme = $settings['theme'] ?? 'modern';

?>