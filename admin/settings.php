<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/config.php';
include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$page_title = "School Settings";

include '../includes/header.php';
include '../includes/navbar.php';

$message = "";

/* Save changes */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $school_name = trim($_POST['school_name']);
    $motto = trim($_POST['motto']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $theme = trim($_POST['theme']);
    $logo_name = $settings['logo'] ?? '';

if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0){

    $allowed_types = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    $file_type = mime_content_type($_FILES['logo']['tmp_name']);

    if(in_array($file_type, $allowed_types, true)){

        $extension = pathinfo(
            $_FILES['logo']['name'],
            PATHINFO_EXTENSION
        );

        $new_name = uniqid('logo_', true) . '.' . $extension;

        $destination =
            '../uploads/logos/' . $new_name;

        move_uploaded_file(
            $_FILES['logo']['tmp_name'],
            $destination
        );

        $logo_name = $new_name;
    }
}

    $check = mysqli_query($conn, "SELECT id FROM school_settings LIMIT 1");

    if (mysqli_num_rows($check) > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE school_settings
             SET school_name=?,
                 motto=?,
                 address=?,
                 phone=?,
                 email=?,
                 logo=?,
                 theme=?
             WHERE id=1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssssss",
            $school_name,
            $motto,
            $address,
            $phone,
            $email,
            $logo_name,
            $theme
        );

        mysqli_stmt_execute($stmt);

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO school_settings
            (school_name,motto,address,phone,email)
            VALUES (?,?,?,?,?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $school_name,
            $motto,
            $address,
            $phone,
            $email
        );

        mysqli_stmt_execute($stmt);
    }

    $message = "Settings saved successfully.";
}

/* Load current settings */
$result = mysqli_query(
    $conn,
    "SELECT * FROM school_settings LIMIT 1"
);

$settings = mysqli_fetch_assoc($result);
?>


<div class="container-fluid">

    <div class="row">

        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="col-md-10 p-4">

            <h2 class="mb-4">
                School Settings
            </h2>

            <?php if($message): ?>
                <div class="alert alert-success">
                    <?= e($message); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow">

                <div class="card-body">

                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label>School Name</label>

                            <input
                                type="text"
                                name="school_name"
                                class="form-control"
                                value="<?= e($settings['school_name'] ?? ''); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label>Motto</label>

                            <input
                                type="text"
                                name="motto"
                                class="form-control"
                                value="<?= e($settings['motto'] ?? ''); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label>Address</label>

                            <input
                                type="text"
                                name="address"
                                class="form-control"
                                value="<?= e($settings['address'] ?? ''); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label>Phone</label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?= e($settings['phone'] ?? ''); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label>Email</label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= e($settings['email'] ?? ''); ?>"
                            >
                        </div>

                        <select name="theme" class="form-control">

    <option value="modern"
        <?= ($settings['theme'] ?? '') == 'modern' ? 'selected' : ''; ?>>
        Modern Theme
    </option>

    <option value="classic"
        <?= ($settings['theme'] ?? '') == 'classic' ? 'selected' : ''; ?>>
        Classic Theme
    </option>

    <option value="university"
        <?= ($settings['theme'] ?? '') == 'university' ? 'selected' : ''; ?>>
        University Theme
    </option>

</select>
<br>

                        <div class="mb-3">

                              <label>School Logo</label>

                               <input
                                  type="file"
                                   name="logo"
                                      class="form-control"
                                       accept=".jpg,.jpeg,.png,.webp"
                                         >

                                     </div>
                                      
                                     <?php if (!empty($settings['logo'])): ?>

    <div class="mt-3">
        <p>Current Logo:</p>

        <img
            src="../uploads/logos/<?= e($settings['logo']); ?>"
            width="120"
            class="img-thumbnail"
        >
    </div>
    
        

<?php endif; ?>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Save Settings
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php
include '../includes/footer.php';
?>