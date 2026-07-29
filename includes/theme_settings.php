<?php

$appearance = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT 
            primary_color,
            secondary_color,
            accent_color,
            font_family
         FROM school_settings
         LIMIT 1"
    )
);


$primary_color = $appearance['primary_color'] ?? '#0d6efd';

$secondary_color = $appearance['secondary_color'] ?? '#198754';

$accent_color = $appearance['accent_color'] ?? '#ffc107';

$font_family = $appearance['font_family'] ?? 'Arial';

?>