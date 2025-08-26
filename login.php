<?php
require_once 'functions.php';
require_once 'auth.php';

session_start();
libxml_use_internal_errors(true);

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
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

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$errors = [];
$login = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '') {
        $errors['login'] = 'Email or Username is required.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $user = loginUser($login, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'Invalid credentials.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Login - Smart Community Portal</title>
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

            <form method="POST" action="login.php" novalidate>
                <img src="./images/CityLinkIcon.png" width="33%" style="margin: auto;" alt="Company Logo" />

                <h1>Login</h1>

                <div>
                    <label for="login">Email or Username</label><br />
                    <input type="text" id="login" name="login" value="<?= e($login) ?>" required />
                    <?php if (!empty($errors['login'])): ?>
                        <div class="error"><?= e($errors['login']) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password">Password</label><br />
                    <input type="password" id="password" name="password" required />
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error"><?= e($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit">Login</button>

                <p> Don't have an account? <a href="register.php"> Register here</a>.</p>
            </form>            
        </main>

        <footer>
            &copy; 2025 CityLink Initiatives.
            &nbsp;<a href="privacy.php">Privacy Policy</a>
        </footer>

        <script type="text/javascript" src="./js/script.js" defer></script>
    </body>
</html>