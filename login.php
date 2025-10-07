<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
use App\Menu\MenuRepository;
use App\Menu\NavRenderer;
$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav      = new NavRenderer($menuRepo);
$current = $_SERVER['REQUEST_URI'] ?? '/index.php';
require_once 'functions.php';
require_once 'auth.php';

session_start();
libxml_use_internal_errors(true);



if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: user_home.php');
    }
    exit;
}

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

            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: user_home.php');
            }

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
  <title>Smart Community Portal</title>
  <link rel="stylesheet" href="./styles/styles.css" />
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
</head>
    <body class="sb-expanded">
        <?= $nav->render($current) ?>
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
    </main> <!--    End Page Content      -->

    <!--    Footer Section      -->
    <footer>
        &copy; 2025 CityLink Initiatives.
        &nbsp;<a href="privacy.php">Privacy Policy</a>
    </footer>

    <script type="text/javascript" src="./js/script.js" defer></script>
</body>

</html>