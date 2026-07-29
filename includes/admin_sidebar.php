<div class="col-md-2 bg-dark text-white min-vh-100 p-0">

    <div class="p-3">

     <?php
$school_query = mysqli_query(
    $conn,
    "SELECT school_name, logo
     FROM school_settings
     LIMIT 1"
);

$school = mysqli_fetch_assoc($school_query);
?>

<?php if(!empty($school['logo'])): ?>

    <div class="text-center mb-3">
        <img
       src="../uploads/logos/<?= e($school['logo']); ?>"
    width="80"
    class="img-fluid rounded-circle"
>
    </div>

<?php endif; ?>

<h3 class="text-center mb-4">
    <?= e($school['school_name'] ?? 'School ERP'); ?>
</h3>

        <hr class="bg-light">

        <ul class="nav flex-column">

            <li class="nav-item mb-2">
                <a href="../admin/dashboard.php" class="nav-link text-white">
                    <i class="fas fa-home me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../admin/students.php" class="nav-link text-white">
                    <i class="fas fa-user-graduate me-2"></i>
                    Students
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../admin/teachers.php" class="nav-link text-white">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Teachers
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../admin/subjects.php" class="nav-link text-white">
                    <i class="fas fa-book me-2"></i>
                    Subjects
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../admin/timetables.php" class="nav-link text-white">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Timetable
                </a>
            </li>

            <li class="nav-item mb-2">
               <a href="../admin/report_cards.php"  class="nav-link text-white">
                    <i class="fas fa-file-alt me-2"></i>
                    Report Cards
                </a>
            </li>

                

        <li class="nav-item">
        <a href="academic_periods.php" class="nav-link">
        📅 Academic Periods
        </a>
</li>

            <li class="nav-item mb-2">
                <a href="../admin/marks.php" class="nav-link text-white">
                    <i class="fas fa-chart-bar me-2"></i>
                    marks
                </a>
            </li>

            <li  class="nav-item mb-2">
             <a href="../admin/view_students.php"  class="nav-link text-white">
                 view students
             </a>
             </li>
              
              <li class="nav-item mb-2">
            <a href="../admin/announcements.php" class="nav-link text-white">
                   <i class="fas fa-bullhorn me-2"></i>
                 Announcements
                </a>
               </li>

               <li class="nav-item mb-2">
       <a href="../admin/gallery.php" class="nav-link text-white">
        <i class="fas fa-images me-2"></i>
        Gallery
       </a>
       </li>

           <li class="nav-item mb-2">
    <a href="../admin/vacancies.php" class="nav-link text-white">
        <i class="fas fa-briefcase me-2"></i>
        Vacancies
    </a>
</li>

       <li class="nav-item mb-2">
    <a href="../admin/backup.php" class="nav-link text-white">
        <i class="fas fa-download me-2"></i>
        Backup Database
    </a>
</li>

         <li class="nav-item mb-2">
    <a href="../admin/audit_logs.php" class="nav-link text-white">
        <i class="fas fa-history me-2"></i>
        Audit Logs
    </a>
</li>


<li  class="nav-item mb-2">
<a href="website/index.php"  class="nav-link text-white">
    Website Management
</a>
</li>

<li class="nav-item mb-2">
    <a href="../admin/backup.php" class="nav-link text-white">
        <i class="fas fa-download me-2"></i>
        Backup Database
    </a>
</li>

    <li class="nav-item mb-2">
    <a href="../admin/restore.php" class="nav-link text-white">
        <i class="fas fa-upload me-2"></i>
        Restore Database
    </a>
</li>

            <li class="nav-item mb-2">
                <a href="../admin/settings.php" class="nav-link text-white">
                    <i class="fas fa-cog me-2"></i>
                    Settings
                </a>
            </li>

            <li class="nav-item mt-4">
                <a href="<?= BASE_URL ?>logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </a>
            </li>
             
        </ul>

    </div>

</div>