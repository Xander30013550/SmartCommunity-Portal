<?php
//  This script initializes the main application environment by setting the timezone, loading menu and 
//  announcement components, and determining the current user's role. It prepares everything needed to 
//  render the navigation and announcement bar, while checking if the user is an admin.

declare(strict_types=1);
session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;
use App\Announcements\AnnouncementBarRenderer;
use App\Announcements\DbAnnouncementsRepository;

// --- app boot ---
date_default_timezone_set('Australia/Perth');

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

$annRepo = new DbAnnouncementsRepository(db());
$annBar = new AnnouncementBarRenderer($annRepo, [
  'autoplay' => true,
  'interval' => 4000,
  'size' => 'md',
  'width' => '100%',
  'extraClass' => '',
]);
$user = $_SESSION['user'] ?? null;
$isAdmin = isset($user) && (($user['role'] ?? '') === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<!--  Moved the header to its on php file, and passing a reference to it in each page -->
<?php include './shared/header.php'; ?>

<body class="sb-expanded">
  <?= $nav->render($current) ?>
  <main class="home-page">
    <img src="./images/CityLinkLogo.png" alt="CityLink Initiatives" class="logo" />
    <section class="announceBar">
      <?= $annBar->render() ?>
      <?php if ($isAdmin): ?>
        <div class="announce-actions">
          <a class="btn btn-edit" href="announcements_management.php" title="Edit announcements">
            <i class='bx bx-edit'></i> Edit announcements
          </a>
        </div>
      <?php endif; ?>
    </section>

    <div class="home">
      <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Et molestiae, iusto mollitia eveniet iste at
        exercitationem veniam dolores aliquam autem, ipsum sint optio culpa nihil. Eligendi optio sint id eos.</p>
      <br>
      <img src="./images/building.jpg" alt="CityLink Initiatives building" />
    </div>
    <br><br>
  </main>
  <footer>
    &copy; 2025 CityLink Initiatives.
    <a href="privacy.php">Privacy Policy</a>
  </footer>
  <script src="./js/slider.js"></script>
  <script src="./js/script.js"></script>
</body>

</html>