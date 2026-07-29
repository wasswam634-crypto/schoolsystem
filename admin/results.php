<?php
include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';
require_role('admin');

$message = null;
$error = null;

if(isset($_POST['save'])){

    $student_id = (int) ($_POST['student_id'] ?? 0);
    $subject_id = (int) ($_POST['subject_id'] ?? 0);
    $marks = (int) ($_POST['marks'] ?? 0);
    $term = $_POST['term'] ?? '';

    $stmt = mysqli_prepare($conn, "INSERT INTO marks(student_id, subject_id, marks, term) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiis", $student_id, $subject_id, $marks, $term);

    if(mysqli_stmt_execute($stmt)){
        $message = "Marks saved successfully.";
    }else{
        $error = mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Results Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<h2>Enter Student Results</h2>

<?php if($message): ?>
<div class="alert alert-success"><?php echo e($message); ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo e($error); ?></div>
<?php endif; ?>

<form method="POST">

    <div class="mb-3">
        <label>Student</label>
        <select name="student_id" class="form-control" required>

            <option value="">Select Student</option>

            <?php
            $students = mysqli_query($conn,"SELECT * FROM students ORDER BY full_name");

            while($row = mysqli_fetch_assoc($students)){
                echo "<option value='".e($row['student_id'])."'>".e($row['full_name'])."</option>";
            }
            ?>

        </select>
    </div>

    <div class="mb-3">
        <label>Subject</label>
        <select name="subject_id" class="form-control" required>

            <option value="">Select Subject</option>

            <?php
            $subjects = mysqli_query($conn,"SELECT * FROM subjects ORDER BY subject_name");

            while($row = mysqli_fetch_assoc($subjects)){
                echo "<option value='".e($row['subject_id'])."'>".e($row['subject_name'])."</option>";
            }
            ?>

        </select>
    </div>

    <div class="mb-3">
        <label>Marks</label>
        <input type="number" name="marks" class="form-control" min="0" max="100" required>
    </div>

    <div class="mb-3">
        <label>Term</label>
        <select name="term" class="form-control">
            <option>Term I</option>
            <option>Term II</option>
            <option>Term III</option>
        </select>
    </div>

    <button type="submit" name="save" class="btn btn-primary">
        Save Result
    </button>

</form>

</div>

</body>
</html>
