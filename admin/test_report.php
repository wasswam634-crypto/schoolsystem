<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "<h3>STEP 1: PHP is working</h3>";

include '../database/connection.php';

echo "<h3>STEP 2: connection.php loaded</h3>";

if (!isset($conn)) {
    die("<h3 style='color:red;'>ERROR: \$conn does not exist.</h3>");
}

echo "<h3>STEP 3: \$conn exists</h3>";

if (!$conn) {
    die("<h3 style='color:red;'>ERROR: Database connection failed.</h3>");
}

echo "<h3>STEP 4: Database connection is working</h3>";

$result = mysqli_query(
    $conn,
    "SELECT grading_id, name, status FROM grading_systems"
);

echo "<h3>STEP 5: grading_systems query works</h3>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<p>";
    echo "ID: " . htmlspecialchars($row['grading_id']);
    echo " | Name: " . htmlspecialchars($row['name']);
    echo " | Status: " . htmlspecialchars($row['status']);
    echo "</p>";
}

$result = mysqli_query(
    $conn,
    "SELECT * FROM report_card_profiles LIMIT 1"
);

echo "<h3>STEP 6: report_card_profiles query works</h3>";

$result = mysqli_query(
    $conn,
    "SELECT * FROM academic_groups LIMIT 1"
);

echo "<h3>STEP 7: academic_groups query works</h3>";

echo "<h2 style='color:green;'>DATABASE TEST COMPLETED SUCCESSFULLY</h2>";

?>