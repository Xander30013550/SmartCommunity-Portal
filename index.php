<?php
declare(strict_types=1);
session_start();
libxml_use_internal_errors(true);

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function loadXml(string $path): ?SimpleXMLElement {
    if (!is_file($path)) return null;
    $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    return $xml !== false ? $xml : null;
}
function getPrimaryMenuItems(string $menusPath): array {
    $xml = loadXml($menusPath);
    if (!$xml) return [];

    $menu = $xml->menu;
    if (!$menu) {
        foreach ($xml->menu as $m) {
            if ((string)($m['id'] ?? '') === 'primary') {
                $menu = $m;
                break;
            }
        }
    }
    if (!$menu) return [];

    $items = [];
    foreach ($menu->item as $item) {
        $items[] = [
            'id'    => (string)($item['id'] ?? ''),
            'label' => trim((string)($item->label ?? 'Untitled')),
            'url'   => trim((string)($item->url ?? '#')),
            'icon'  => trim((string)($item->icon ?? 'bx bx-link')),
            'weight'=> (int)($item['weight'] ?? 0),
        ];
    }
    usort($items, function ($a, $b) {
        return [$a['weight'], $a['label']] <=> [$b['weight'], $b['label']];
    });

    return $items;
}

$menusPath = __DIR__ . '/config/menus.xml';
$menuItems = getPrimaryMenuItems($menusPath);
if (empty($menuItems)) {
    $menuItems = [
        ['id'=>'home', 'label'=>'Home', 'url'=>'/index.php', 'icon'=>'bx bx-home-circle', 'weight'=>10],
        ['id'=>'login', 'label'=>'Login', 'url'=>'/login.php', 'icon'=>'bx bx-user', 'weight'=>20],
        ['id'=>'register', 'label'=>'Register', 'url'=>'/register.php', 'icon'=>'bx bx-user-plus', 'weight'=>25],
        ['id'=>'feedback', 'label'=>'Feedback', 'url'=>'/feedback.php', 'icon'=>'bx bx-chat', 'weight'=>30],
        ['id'=>'bookings', 'label'=>'Bookings', 'url'=>'/bookings.php', 'icon'=>'bx bx-book-open', 'weight'=>40],
        ['id'=>'about', 'label'=>'About', 'url'=>'/about.php', 'icon'=>'bx bx-info-square', 'weight'=>50],
    ];
}

$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

// Handle logout if requested
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
<nav id="sidebar">
<ul>
<li>
<button onclick="toggleSidebar()" id="toggle-btn" aria-label="Toggle sidebar">
    <i id="icon-expand" class="bx bx-chevrons-right hidden"></i>
    <i id="icon-collapse" class="bx bx-chevrons-left"></i>
</button>
</li>
<?php foreach ($menuItems as $item):
    $target = basename(parse_url($item['url'], PHP_URL_PATH) ?: '');
    $isActive = $target === $current || ($target === '' && $current === 'index.php');
?>
<li class="<?= $isActive ? 'active' : '' ?>">
    <a href="<?= e($item['url']) ?>">
        <i class="<?= e($item['icon']) ?>"></i>
        <span><?= e($item['label']) ?></span>
    </a>
</li>
<?php endforeach; ?>
</ul>
</nav>
<main>
    <h1>Welcome to CityLink Initiatives</h1>
    <?php if ($user): ?>
        <p>Hello, <?= e($user['name']) ?>!</p>
        <form method="GET" action="index.php" style="margin-top:20px;">
            <button type="submit" name="logout" value="1" style="padding:10px 20px; font-size:1rem;">
                <i class="bx bx-log-out"></i> Logout
            </button>
        </form>
    <?php else: ?>
        <p>
            Lorem, ipsum dolor sit amet consectetur adipisicing elit. Blanditiis corporis dolore ad nostrum,
            atque dolor ut, explicabo accusamus vel, omnis magni facere? Cum veritatis eligendi, impedit
            voluptatum doloremque numquam! Perferendis?
        </p>
    <?php endif; ?>
</main>
<footer>
    &copy; 2025 CityLink Initiatives.
    &nbsp;<a href="privacy.php">Privacy Policy</a>
</footer>
<script type="text/javascript" src="./js/script.js" defer></script>
</body>
</html>
