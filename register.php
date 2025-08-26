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

$errors = [];
$name = '';
$email = '';
$password = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    }
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($confirm_password === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Connect to DB
        $host = 'localhost';
        $db = 'citylink_users';
        $user = 'root';
        $pass = 'usbw';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);

            // Check if email or name exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR name = ?");
            $stmt->execute([$email, $name]);
            if ($stmt->fetch()) {
                $errors['general'] = 'Email or username already exists.';
            } else {
                // Insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword]);

                // Redirect to login or auto-login
                $_SESSION['user'] = [
                    'id' => $pdo->lastInsertId(),
                    'name' => $name,
                    'email' => $email
                ];
                header('Location: index.php');
                exit;
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . e($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Register - Smart Community Portal</title>
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
            
            <?php if (!empty($errors['general'])): ?>
                <div class="error"><?= e($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" novalidate>
                <!--    Company logo    -->
                <img src="./images/CityLinkIcon.png" width="33%" style="margin: auto;" alt="Two overlapping purple square outlines with a three-dimensional appearance, forming a layered geometric design." />

                <h1>Register</h1>

                <label for="name">Username</label>
                <input type="text" id="name" name="name" value="<?= e($name) ?>" required />
                <?php if (!empty($errors['name'])): ?>
                    <div class="error"><?= e($errors['name']) ?></div>
                <?php endif; ?>

                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required />
                <?php if (!empty($errors['email'])): ?>
                    <div class="error"><?= e($errors['email']) ?></div>
                <?php endif; ?>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6" />
                <?php if (!empty($errors['password'])): ?>
                    <div class="error"><?= e($errors['password']) ?></div>
                <?php endif; ?>
                
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" />
                <?php if (!empty($errors['confirm_password'])): ?>
                    <div class="error"><?= e($errors['confirm_password']) ?></div>
                <?php endif; ?>
                
                <button type="submit">Register</button>
                <p>Already registered? <a href="login.php">Login here</a>.</p>
            </form>            
        </main>

        <footer>
            &copy; 2025 CityLink Initiatives.
            &nbsp;<a href="privacy.php">Privacy Policy</a>
        </footer>

        <script type="text/javascript" src="./js/script.js" defer></script>
    </body>
</html>