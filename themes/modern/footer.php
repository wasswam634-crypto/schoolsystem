<footer class="bg-dark text-white pt-5 pb-3 mt-5">

<div class="container">

<div class="row">

    <!-- School Information -->
    <div class="col-md-4 mb-4">

        <h4>
            <?= e($school_settings['school_name'] ?? 'School Name'); ?>
        </h4>

        <p>
            Excellence in Education and Character Development.
        </p>

        <p>
            Building future leaders through quality education.
        </p>

    </div>

    <!-- Quick Links -->
    <div class="col-md-4 mb-4">

        <h5>Quick Links</h5>

        <ul class="list-unstyled">

            <li>
                <a href="index.php" class="text-white text-decoration-none">
                    Home
                </a>
            </li>

            <li>
                <a href="index.php?page=about" class="text-white text-decoration-none">
                    About Us
                </a>
            </li>

            <li>
                <a href="index.php?page=academics" class="text-white text-decoration-none">
                    Academics
                </a>
            </li>

            <li>
                <a href="index.php?page=admissions" class="text-white text-decoration-none">
                    Admissions
                </a>
            </li>

            <li>
                <a href="index.php?page=staff" class="text-white text-decoration-none">
                    Staff
                </a>
            </li>

            <li>
                <a href="index.php?page=contact" class="text-white text-decoration-none">
                    Contact Us
                </a>
            </li>

        </ul>

    </div>

    <!-- Contact Information -->
    <div class="col-md-4 mb-4">

        <h5>Contact Information</h5>

        <p>
            <strong>Phone:</strong>
            <?= e($school_settings['phone'] ?? '+256700000000'); ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?= e($school_settings['email'] ?? 'info@school.com'); ?>
        </p>

        <p>
            <strong>Address:</strong>
            <?= e($school_settings['address'] ?? 'Uganda'); ?>
        </p>

        <!-- Social Links -->
        <?php
        $socials = mysqli_query(
            $conn,
            "SELECT * FROM website_social_links"
        );

        while($social = mysqli_fetch_assoc($socials)){
        ?>

            <a
                href="<?= e($social['url']); ?>"
                target="_blank"
                class="text-white me-3"
            >
                <i class="<?= e($social['icon']); ?>"></i>
            </a>

        <?php } ?>

    </div>

</div>

<hr class="border-light">

<div class="text-center">

    <p class="mb-0">

        &copy; <?= date('Y'); ?>
        <?= e($school_settings['school_name'] ?? 'School Name'); ?>

        | All Rights Reserved.

    </p>

</div>

</div>

</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>