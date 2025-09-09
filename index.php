<?php
declare(strict_types=1);
session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Smart Community Portal</title>
  <link rel="stylesheet" href="./styles/styles.css" />
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
</head>

<body class="sb-expanded">
  <?= $nav->render($current) ?>

  <main>
    <h1>Welcome to CityLink Initiatives</h1>
    <div class="annbar" data-interval="4000" data-duration="600">

      <div class="annbar-viewport" role="region" aria-label="Site announcements">
        <ul class="annbar-track">
          <!-- These <li> will be rendered by PHP or hardcoded -->
          <li class="annbar-slide"><a href="#">Maintenance: Server reboot at 02:00 UTC.</a></li>
          <li class="annbar-slide"><a href="#">New build: Frontend framework v1.2 shipped</a></li>
        </ul>
      </div>

      <!-- Screen reader live updates -->
      <span class="annbar-sr" aria-live="polite"></span>
    </div>
  </main>

  <footer>
    &copy; 2025 CityLink Initiatives. &nbsp;<a href="privacy.php">Privacy Policy</a>
  </footer>
  <script src="./js/script.js" defer></script>
</body>
</html>