<?php


session_start();

require_once "../includes/auth.php";

require_role('admin');

include "../database/connection.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Class Rankings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<h2>Student Rankings</h2>

<table class="table table-bordered">

<tr>
<th>Position</th>
<th>Student</th>
<th>Total Marks</th>
<th>Average</th>
</tr>

<?php

$sql = "

SELECT
students.student_id,
students.full_name,

SUM(marks.marks) AS total_marks,

AVG(marks.marks) AS average_marks

FROM students

JOIN marks

ON students.student_id = marks.student_id

GROUP BY students.student_id

ORDER BY average_marks DESC

";

$result = mysqli_query($conn,$sql);

$position = 1;

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $position; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['total_marks']; ?></td>

<td><?php echo round($row['average_marks'],2); ?></td>

</tr>

<?php

$position++;

}

?>

</table>

</div>

</body>
</html>