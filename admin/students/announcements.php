<?php
include '../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('student');

if(isset($_POST['save'])){

    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $audience = $_POST['audience'];

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO announcements
        (title, message, audience, created_by)
        VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $title,
        $message,
        $audience,
        $_SESSION['user_id']
    );

    mysqli_stmt_execute($stmt);

    $success = "Announcement published successfully.";
}
?>

<?php if(isset($success)): ?>
<div class="alert alert-success">
    <?= e($success); ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Title</label>
<input
type="text"
name="title"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Message</label>
<textarea
name="message"
rows="5"
class="form-control"
required></textarea>
</div>

<div class="mb-3">
<label>Audience</label>

<select
name="audience"
class="form-control">

<option value="all">Everyone</option>
<option value="students">Students</option>
<option value="teachers">Teachers</option>
<option value="admin">Administrators</option>

</select>

</div>

<button
name="save"
class="btn btn-primary">

Publish Announcement

</button>

</form>