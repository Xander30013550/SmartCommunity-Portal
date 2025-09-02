<?php
declare(strict_types=1);
<<<<<<< HEAD

require __DIR__ . '/vendor/autoload.php';
use App\Menu\MenuRepository;
use App\Menu\NavRenderer;
$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav      = new NavRenderer($menuRepo);
$current = $_SERVER['REQUEST_URI'] ?? '/index.php';
=======
session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';

$menusPath = __DIR__ . '/config/menus.xml';
$menuItems = getPrimaryMenuItems($menusPath);
if (empty($menuItems)) {
    $menuItems = [
        ['id' => 'home', 'label' => 'Home', 'url' => '/index.php', 'icon' => 'bx bx-home-circle', 'weight' => 10],
        ['id' => 'login', 'label' => 'Login', 'url' => '/login.php', 'icon' => 'bx bx-user', 'weight' => 20],
        ['id' => 'register', 'label' => 'Register', 'url' => '/register.php', 'icon' => 'bx bx-user-plus', 'weight' => 25],
        ['id' => 'feedback', 'label' => 'Feedback', 'url' => '/feedback.php', 'icon' => 'bx bx-chat', 'weight' => 30],
        ['id' => 'bookings', 'label' => 'Bookings', 'url' => '/bookings.php', 'icon' => 'bx bx-book-open', 'weight' => 40],
        ['id' => 'about', 'label' => 'About', 'url' => '/about.php', 'icon' => 'bx bx-info-square', 'weight' => 50],
    ];
}

$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'] ?? null;
>>>>>>> Michael-Dev
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

<<<<<<< HEAD
    <!--    Main Section    -->
    <body class="sb-expanded">        
        <!--    Navigation Section      -->
        <?= $nav->render($current) ?>
=======
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

>>>>>>> Michael-Dev
        <!--    Page Content    -->
        <main>
            <h1>Privacy Policy</h1>
            <p><strong>Last Updated: August 18, 2025</strong></p><br>

            <div class="section">
                <h2>1. Information We Collect</h2>
                <p>We collect personal information in the following ways:</p>

                <h3>a. Information You Provide Directly</h3>
                <ul>
                    <li><strong>Account Information:</strong> When you sign up for the Smart Community Portal, we collect your name, email address, phone number, and any other details necessary for creating and managing your account.</li>
                    <li><strong>Service Booking:</strong> Information related to bookings, such as service requests and the number of people involved in the service, may be collected.</li>
                    <li><strong>Feedback:</strong> We collect feedback you provide through our feedback forms, surveys, or community engagement initiatives.</li>
                </ul><br>

                <h3>b. Information We Collect Automatically</h3>
                <ul>
                    <li><strong>Log Data:</strong> When you visit our website or use our portal, we automatically collect certain data, such as your IP address, browser type, operating system, and the pages you visit.</li>
                    <li><strong>Cookies:</strong> We use cookies to enhance user experience, analyze usage, and provide personalized content. You can control cookie settings through your browser.</li>
                </ul>
            </div>

            <div class="section">
                <h2>2. How We Use Your Information</h2>
                <p>We use your personal information for the following purposes:</p>
                <ul>
                    <li><strong>Provide Services:</strong> To create and manage your account, process service bookings, and provide access to community programs and events.</li>
                    <li><strong>Improve User Experience:</strong> To personalize your experience, enhance the functionality of the portal, and offer relevant content or recommendations.</li>
                    <li><strong>Communication:</strong> To send updates about your account, services, events, and changes to our policies. We may also use your information to respond to customer service requests or queries.</li>
                    <li><strong>Legal Compliance:</strong> To comply with legal obligations and protect the rights, safety, and security of our users and the community.</li>
                </ul>
            </div>

            <div class="section">
                <h2>3. How We Protect Your Information</h2>
                <p>We are committed to ensuring that your information is secure. We implement a variety of security measures to safeguard your personal data, including:</p>
                <ul>
                    <li><strong>Encryption:</strong> We use SSL encryption to protect sensitive data, such as personal information and payment details.</li>
                    <li><strong>Access Control:</strong> We restrict access to personal information to authorized personnel only, based on need.</li>
                    <li><strong>Data Retention:</strong> We retain your personal information only for as long as necessary to fulfill the purposes outlined in this policy or as required by law.</li>
                </ul>
            </div>

            <div class="section">
                <h2>4. Sharing Your Information</h2>
                <p>We do not sell, trade, or rent your personal information to third parties. However, we may share your information in the following situations:</p>
                <ul>
                    <li><strong>Service Providers:</strong> We may share your information with third-party service providers who assist us in operating the portal, processing payments, or offering services on our behalf. These providers are required to keep your information confidential and secure.</li>
                    <li><strong>Legal Compliance:</strong> We may disclose your information if required by law, in response to a legal request, or to protect our rights and the safety of others.</li>
                    <li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your personal data may be transferred as part of the transaction.</li>
                </ul>
            </div>

            <div class="section">
                <h2>5. Your Rights and Choices</h2>
                <p>You have the following rights regarding your personal information:</p>
                <ul>
                    <li><strong>Access and Correction:</strong> You may request access to the personal information we hold about you and request corrections if the information is inaccurate.</li>
                    <li><strong>Deletion:</strong> You may request the deletion of your account and personal information, subject to any legal or contractual obligations.</li>
                    <li><strong>Opt-Out of Communications:</strong> You can opt-out of receiving marketing communications from us at any time by following the unsubscribe instructions in our emails or by contacting us directly.</li>
                    <li><strong>Cookies:</strong> You can manage your cookie preferences through your browser settings. Please note that disabling certain cookies may affect your ability to use some features of the portal.</li>
                </ul>
            </div>

            <div class="section">
                <h2>6. Data Retention</h2>
                <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this policy, or as required by law, including any legal obligations, dispute resolution, and enforcement of agreements.</p>
            </div>

            <div class="section">
                <h2>7. Third-Party Links</h2>
                <p>Our website and portal may contain links to third-party websites, services, or social media platforms. We are not responsible for the privacy practices of these third parties, and we encourage you to review their privacy policies when interacting with their services.</p>
            </div>

            <div class="section">
                <h2>8. Children's Privacy</h2>
                <p>Our services are not intended for children under the age of 13. We do not knowingly collect personal information from children. If we become aware that a child under 13 has provided personal information, we will take steps to remove that information from our systems.</p>
            </div>

            <div class="section">
                <h2>9. Changes to This Privacy Policy</h2>
                <p>We may update this Privacy Policy from time to time. If we make significant changes, we will notify you via email or through a notice on the portal. We encourage you to review this policy periodically to stay informed about how we are protecting your information.</p>
            </div>

            <div class="section">
                <h2>10. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy or the way we handle your personal data, please contact us:</p>
                <div class="contact-info">
                    <p><strong>Email:</strong> contact@citylink.gov.au</p>
                    <p><strong>Phone:</strong> +61 8 9221 2400</p>
                    <p><strong>Office Address:</strong> [Office Location]</p>
                </div>
            </div>
        </main> <!--    End page content    -->

<<<<<<< HEAD
        <!--    Footer section      -->
        <Footer>
            &copy; 2025 CityLink Initiatives. &nbsp;
            <a href="privacy.php"> Privacy Policy </a>
        </Footer>
=======
        <footer>
            &copy; 2025 CityLink Initiatives.
            &nbsp;<a href="privacy.php">Privacy Policy</a>
        </footer>
>>>>>>> Michael-Dev

        <script type="text/javascript" src="./js/script.js" defer></script>
    </body>
</html>