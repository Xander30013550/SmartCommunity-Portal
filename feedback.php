<?php
declare(strict_types=1);
libxml_use_internal_errors(true);

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Load and parse an XML file safely. Returns SimpleXMLElement|null.
 */
function loadXml(string $path): ?SimpleXMLElement
{
    if (!is_file($path))
        return null;
    $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    return $xml !== false ? $xml : null;
}
function getPrimaryMenuItems(string $menusPath): array
{
    $xml = loadXml($menusPath);
    if (!$xml)
        return [];

    // If you use namespaces later, switch to XPath with registerXPathNamespace(...)
    $menu = $xml->menu; // assumes only one primary menu
    if (!$menu) {
        // Try to find <menu id="primary"> if multiple menus exist
        foreach ($xml->menu as $m) {
            if ((string) ($m['id'] ?? '') === 'primary') {
                $menu = $m;
                break;
            }
        }
    }
    if (!$menu)
        return [];

    // Normalize each <item> into an array
    $items = [];
    foreach ($menu->item as $item) {
        $items[] = [
            'id' => (string) ($item['id'] ?? ''),
            'label' => trim((string) ($item->label ?? 'Untitled')),
            'url' => trim((string) ($item->url ?? '#')),
            'icon' => trim((string) ($item->icon ?? 'bx bx-link')),
            'weight' => (int) ($item['weight'] ?? 0),
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
        ['id' => 'home', 'label' => 'Home', 'url' => '/index.php', 'icon' => 'bx bx-home-circle', 'weight' => 10],
        ['id' => 'login', 'label' => 'Login', 'url' => '/login.php', 'icon' => 'bx bx-user', 'weight' => 20],
        ['id' => 'feedback', 'label' => 'Feedback', 'url' => '/feedback.php', 'icon' => 'bx bx-chat', 'weight' => 30],
        ['id' => 'bookings', 'label' => 'Bookings', 'url' => '/bookings.php', 'icon' => 'bx bx-book-open', 'weight' => 40],
        ['id' => 'about', 'label' => 'About', 'url' => '/about.php', 'icon' => 'bx bx-info-square', 'weight' => 50],
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
    <title> Smart Community Portal </title>
    <link rel="stylesheet" href="./styles/styles.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
</head>

<!--    Main Section    -->

<body class="sb-expanded">

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
    <!--    Page Content    -->
    <main>
        <h1> Welcome to CityLink Initiatives </h1><br>
        <div class="row">
            <div class="column">
                <form id="feedback-form" class="form">
                    <!--    Company logo    -->
                    <img src="./images/CityLinkIcon.png" width="50%" style="margin: auto;" />

                    <h2> Feedback Form </h2>

                    <label for="name"> Name: </label>
                    <input type="text" id="name" name="name" required>

                    <label for="email"> Email: </label>
                    <input type="email" id="email" name="email" required>

                    <label for="subject"> Subject: </label>
                    <select id="subject" name="subject" required>
                        <option value="" disabled selected>Please Select...</option>
                        <option value="eventBookings">Event Bookings</option>
                        <option value="wasteManagement">Waste Management</option>
                        <option value="communityPrograms">Community Programs</option>
                        <option value="ratesEnquiries">Rates Enquiries</option>
                        <option value="feedback">Feedback</option>
                        <option value="publicAnnouncements">Public Announcements</option>
                        <option value="serviceRequests">Service Requests</option>
                        <option value="volunteering">Volunteering Opportunities</option>
                        <option value="other">Other</option>
                    </select>

                    <label for="message"> Message: </label>
                    <textarea id="message" name="message" required></textarea>

                    <button type="submit"> Submit </button>
                </form>

                <!--    Modal Feedback Popup    -->
                <div id="successModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span id="closeModal" class="close">&times;</span>
                    <div class="tick">&#10004;</div>
                    <h2>Your Form Has Been Successfully Submitted</h2>
                    <p>Look forward to hearing from us soon!</p>

                    <!-- Toggle form info button -->
                    <button id="toggleInfoBtn">Show/Hide Form Info</button>
                    <div id="formInfo" style="display:none; margin-top:10px; text-align:left;"></div>
                </div>
                </div>

            </div>

            <div class="column">
                <div id="faq-container" class="faq-container">
                    <h2>Frequently Asked Questions</h2>

                    <details>
                        <summary>How can I book a community event?</summary>
                        <p>To book a community event, simply visit the "Event Bookings" section on the portal, select
                            your desired event, and follow the prompts to complete the booking process. You'll receive a
                            confirmation email with event details.</p>
                    </details>

                    <details>
                        <summary>What should I do if I have a problem with waste collection?</summary>
                        <p>If you encounter any issues with waste collection, head to the "Waste Management" section on
                            the portal and fill out the service request form. Our team will get back to you with a
                            resolution as soon as possible.</p>
                    </details>

                    <details>
                        <summary>How can I submit feedback about CityLink services?</summary>
                        <p>You can submit feedback through the "Feedback" section of the portal. Just fill out the form
                            with your comments or suggestions, and we'll review it to help improve our services.</p>
                    </details>

                    <details>
                        <summary>Can I access my user profile information?</summary>
                        <p>Yes, you can access and update your user profile through the "User Profile" section. Here,
                            you can view your contact details, service bookings, and any past feedback you’ve submitted.
                        </p>
                    </details>

                    <details>
                        <summary>Are there any volunteer opportunities available in my community?</summary>
                        <p>Yes, we frequently post volunteer opportunities in the "Volunteering Opportunities" section.
                            You can browse through different roles and apply directly through the portal.</p>
                    </details>

                    <details>
                        <summary>How do I get information about my rates or local taxes?</summary>
                        <p>To get information about rates or taxes, visit the "Rates Enquiries" section on the portal.
                            You’ll be able to find relevant information about payments, deadlines, and any related
                            inquiries.</p>
                    </details>

                    <details>
                        <summary>How can I stay updated on important community announcements?</summary>
                        <p>Stay updated by checking the "Public Announcements" section regularly. You can also subscribe
                            to email notifications to receive the latest announcements directly in your inbox.</p>
                    </details>

                    <details>
                        <summary>What is the process for submitting a service request?</summary>
                        <p>To submit a service request, navigate to the "Service Requests" section. Select the type of
                            service you need, fill out the form with your details, and our team will handle your request
                            promptly.</p>
                    </details>

                    <details>
                        <summary>Is the CityLink portal mobile-friendly?</summary>
                        <p>Yes, the CityLink portal is fully optimized for mobile use, allowing you to access all
                            services, make bookings, and manage your profile on-the-go.</p>
                    </details>

                    <details>
                        <summary>How do I update my personal contact information?</summary>
                        <p>To update your personal contact information, log in to your user profile and edit the details
                            under "Account Settings." Your changes will be saved immediately.</p>
                    </details>
                </div>
            </div>
        </div>
    </main> <!--    End page content    -->

    <!--    Footer section      -->
    <Footer>
        &copy; 2025 CityLink Initiatives. &nbsp;
        <a href="privacy.html"> Privacy Policy </a>
    </Footer>

    <script type="text/javascript" src="./js/script.js" defer></script>
</body>

</html>