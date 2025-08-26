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
        <title> Smart Community Portal </title>
        <link rel="stylesheet" href="./styles/styles.css" />
        <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    </head>

    <!--    Main Section    -->
    <body class="sb-expanded">
        <?= $nav->render($current) ?>
        <!--    Page Content    -->
        <main>
            <section>
                <!--    Login Form  -->
                <form id="login-form">
                    <!--    Company logo    -->
                    <img src="./images/CityLinkIcon.png" width="33%" style="margin: auto;" />
                    
                    <!--    Section header  -->
                    <h2> Sign in </h2>

                    <!--    Username section    -->
                    <label for="username"> Username or email </label>
                    <input type="text" id="username" name="username" required>

                    <!--    Password section    -->
                    <label for="password"> Password </label>
                    <input type="password" id="password" name="password" required>

                    <!--    Submission Button   -->
                    <button type="submit"> Submit </button>

                    <a href="#"> Forgot Password? </a>

                    <p> Don't have an account? </p>
                    <p>Register <a href="#"> Here</a>. </p>
                </form>
            </section>
        </main> <!--    End page content    -->

        <!--    Footer section      -->
        <Footer>
            &copy; 2025 CityLink Initiatives. &nbsp;
            <a href="privacy.php"> Privacy Policy </a>
        </Footer>

        <script type="text/javascript" src="./js/script.js" defer></script>
    </body>
</html>