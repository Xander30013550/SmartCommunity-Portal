<?php
declare(strict_types= 1);
libxml_use_internal_errors(true);

function e (string$s): string {
    return htmlspecialchars($s, ENT_QUOTES,"UTF-8");
}

/**
 * Load and parse an XML file safely. Returns SimpleXMLElement|null.
 */
function loadXml(string $path): ?SimpleXMLElement {
    if (!is_file($path)) return null;
    $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    return $xml !== false ? $xml : null;
}

function getPrimaryMenuItems(string $menusPath): array {
    $xml = loadXml($menusPath);
    if (!$xml) return [];

    // If you use namespaces later, switch to XPath with registerXPathNamespace(...)
    $menu = $xml->menu; // assumes only one primary menu
    if (!$menu) {
        // Try to find <menu id="primary"> if multiple menus exist
        foreach ($xml->menu as $m) {
            if ((string)($m['id'] ?? '') === 'primary') {
                $menu = $m;
                break;
            }
        }
    }
    if (!$menu) return [];

    // Normalize each <item> into an array
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

    // Sort by weight (then label)
    usort($items, function ($a, $b) {
        return [$a['weight'], $a['label']] <=> [$b['weight'], $b['label']];
    });

    return $items;
}

$menusPath = __DIR__ . '/config/menus.xml';

$menuItems = getPrimaryMenuItems($menusPath);
if (empty($menuItems)) {
    $menuItems = [
        ['id'=>'home',     'label'=>'Home',     'url'=>'/index.php',     'icon'=>'bx bx-home-circle', 'weight'=>10],
        ['id'=>'login',    'label'=>'Login',    'url'=>'/login.php',     'icon'=>'bx bx-user',        'weight'=>20],
        ['id'=>'feedback', 'label'=>'Feedback', 'url'=>'/feedback.php',  'icon'=>'bx bx-chat',        'weight'=>30],
        ['id'=>'bookings', 'label'=>'Bookings', 'url'=>'/bookings.php',  'icon'=>'bx bx-book-open',   'weight'=>40],
        ['id'=>'about',    'label'=>'About',    'url'=>'/about.php',     'icon'=>'bx bx-info-square', 'weight'=>50],
    ];
}

// Determine "active" item based on current path
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');
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
    <!-- Navigation Section -->
    <nav id="sidebar">
        <ul>
            <!-- Collapse/Expand -->
            <li>
                <button onclick="toggleSidebar()" id="toggle-btn" aria-label="Toggle sidebar">
                    <i id="icon-expand" class="bx bx-chevrons-right hidden"></i>
                    <i id="icon-collapse" class="bx bx-chevrons-left"></i>
                </button>
            </li>

            <!-- XML-driven menu -->
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
    <!-- End Navigation Section -->

    <!-- Page Content -->
    <main>
        <section>
                <!--    Login Form  -->
                <form id="login-form">
                    <!--    Company logo    -->
                    <img src="./images/CityLinkIcon.png" width="33%" style="margin: auto;" />
                    
                    <!--    Section header  -->
                    <h2> Sign in </h2>

                    <!--    Username section    -->
                    <label for="username"> Name </label>
                    <input type="text" id="username" name="username" required>

                    <!--    Password section    -->
                    <label for="password"> Password </label>
                    <input type="password" id="password" name="password" required>

                    <!--    Submission Button   -->
                    <button type="submit"> Submit </button>

                    <a href="#"> Forgot Password? </a>

                    <p> Don't have an account? </p>
                    <p>Register <a href="./signup.php"> Here</a>. </p>
                </form>
            </section>
    </main>
    <!-- End page content -->

    <!-- Footer section -->
    <footer>
        &copy; 2025 CityLink Initiatives.
        &nbsp;<a href="privacy.php">Privacy Policy</a>
    </footer>

    <script type="text/javascript" src="./js/script.js" defer></script>
</body>
</html>