<?php
declare(strict_types=1);
session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;
use App\Announcements\AnnouncementsRepository;
use App\Announcements\AnnouncementBarRenderer;

// --- app boot ---
date_default_timezone_set('Australia/Perth');

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

$annRepo = new AnnouncementsRepository(__DIR__ . '/config/announcement.xml');
$annBar  = new AnnouncementBarRenderer($annRepo, [
  'autoplay'   => true,
  'interval'   => 4000,
  'size'       => 'md',
  'width'      => '100%',
  'extraClass' => '',
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Smart Community Portal</title>
  <link rel="stylesheet" href="./styles/styles.css" />
  <link rel="stylesheet" href="./styles/annoucementBar.css" />
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
</head>
<body class="sb-expanded">
  <?= $nav->render($current) ?>
  <main>
    <h1 class="page-title">Smart Community Portal</h1>
    <?= $annBar->render() ?>
  </main>
  <footer>
    &copy; 2025 CityLink Initiatives.
    <a href="privacy.php">Privacy Policy</a>
  </footer>
  <script src="./js/slider.js"></script>
  <script src="./js/script.js"></script>
</body>
</html>
