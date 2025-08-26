<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav      = new NavRenderer($menuRepo);

$current = $_SERVER['REQUEST_URI'] ?? '/index.php';
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
    <h2>Test</h2>
    <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. At aliquam itaque earum neque eaque cum laboriosam iure sit accusantium, amet illo error, optio debitis consectetur, eum vitae tempore corrupti. Veniam.</p>
  </main>

  <footer>
    &copy; 2025 CityLink Initiatives. &nbsp;<a href="privacy.php">Privacy Policy</a>
  </footer>
  <script src="./js/script.js" defer></script>
</body>
</html>
