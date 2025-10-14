<?php
declare(strict_types=1);
session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

// --- Ensure user is logged in and is an admin ---
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: user_home.php'); // Redirect general users elsewhere
    exit;
}

$user = $_SESSION['user'];

// --- Handle logout ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// --- App boot ---
date_default_timezone_set('Australia/Perth');

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'admin.php');
?>

<!DOCTYPE html>

<html lang="en">
    <?php include './shared/header.php'; ?>

<!--    Main Section      -->

<body class="sb-expanded">
    <?= $nav->render($current) ?>

    <!--    Page Content      -->

    <main>
        <h1 class="page-title">Admin Dashboard</h1>

        <div class="user-info" style="margin-bottom: 1em; font-size: 1rem;">
            Logged in as <strong><?= htmlspecialchars($user['name']) ?></strong>
            (<?= htmlspecialchars($user['role']) ?>)
            &nbsp;|&nbsp;
            <a href="?logout=true">Logout</a>
        </div>

        <section>
            <p>Welcome, <?= htmlspecialchars($user['name']) ?>. You have administrator access.</p>

            <ul>
                <li><a href="user_management.php">Manage Users</a></li>
                <li><a href="#">Edit Announcements</a></li>
            </ul>
        </section>
    </main> <!--    End page content    -->

    <!--    Footer section      -->
    <footer>
        &copy; 2025 CityLink Initiatives.
        <a href="privacy.php">Privacy Policy</a>
    </footer>

    <script src="./js/script.js"></script>
</body>

</html>