<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

declare(strict_types=1);
libxml_use_internal_errors(true);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';
//require_once __DIR__ . '/feedback/feedbackFunctions.php';


use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);


$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

/*$feedbackSuccess = false;
$feedbackErrors = [];
$formData = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $formData = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'subject' => trim($_POST['subject'] ?? ''),
        'message' => trim($_POST['message'] ?? ''),
    ];

    try {
        $result = addFeedbackToTable(
            $formData['name'],
            $formData['email'],
            $formData['subject'],
            $formData['message']
        );
    } catch (Throwable $e) {
        die("addFeedbackToTable() error: " . $e->getMessage());
    }

    if (isset($result['id'])) {
        $feedbackSuccess = true;
    } else {
        $feedbackErrors = $result;
    }
}*/

function getFaqItems(string $faqPath): array {
    $xml = loadXml($faqPath);
    if (!$xml)
        return [];

    // Pick the first FAQList (or the one with id="primary")
    $list = $xml->FAQList ?? null;
    if (!$list && isset($xml->FAQList)) {
        foreach ($xml->FAQList as $candidate) {
            if ((string) ($candidate['id'] ?? '') === 'primary') {
                $list = $candidate;
                break;
            }
        }
        if (!$list && isset($xml->FAQList[0])) {
            $list = $xml->FAQList[0];
        }
    }
    if (!$list)
        return [];

    $faqs = [];
    foreach ($list->FAQ as $node) {
        $faqs[] = [
            'id' => (string) ($node['id'] ?? ''),
            'summary' => trim((string) ($node->summary ?? 'Untitled question')),
            'description' => trim((string) ($node->description ?? '')),
        ];
    }

    // Keep XML order, or sort by summary if you prefer:
    // usort($faqs, fn($a,$b) => strcmp($a['summary'], $b['summary']));

    return $faqs;
}

$faqPath = __DIR__ . '/config/faqs.xml';
$faqItems = getFaqItems($faqPath);
if (empty($faqItems)) {
    $faqItems = [
        [
            'id' => 'placeholder',
            'summary' => 'No FAQs available yet',
            'description' => 'Check back soon or contact support if you need help.',
        ]
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
    <?= $nav->render($current) ?>

    <!--    Page Content    -->

    <main>
        <h1> Welcome to CityLink Initiatives </h1><br>
        <div class="row">
            <div class="column">
                <form id="feedback-form" class="form" method="POST" action="">
                    <!--    Company logo    -->
                    <img src="./images/CityLinkIcon.png" width="50%" style="margin: auto;" />

                    <h2> Feedback Form </h2>

                    <label for="name"> Name: </label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($formData['name']) ?>">

                    <label for="email"> Email: </label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($formData['email']) ?>">

                    <label for="subject"> Subject: </label>
                    <select id="subject" name="subject" required>
                        <option value="" disabled <?= $formData['subject'] === '' ? 'selected' : '' ?>>Please Select...</option>
                        <option value="eventBookings" <?= $formData['subject'] === 'eventBookings' ? 'selected' : '' ?>>Event Bookings</option>
                        <option value="wasteManagement" <?= $formData['subject'] == 'wasteManagement' ? 'selected' : ''?>>Waste Management</option>
                        <option value="communityPrograms" <?= $formData['subject'] == 'communityPrograms' ? 'selected' : ''?>>Community Programs</option>
                        <option value="ratesEnquiries" <?= $formData['subject'] == 'ratesEnquiries' ? 'selected' : ''?>>Rates Enquiries</option>
                        <option value="feedback" <?= $formData['subject'] == 'feedback' ? 'selected' : ''?>>Feedback</option>
                        <option value="publicAnnouncements" <?= $formData['subject'] == 'publicAnnouncements' ? 'selected' : ''?>>Public Announcements</option>
                        <option value="serviceRequests" <?= $formData['subject'] == 'serviceRequests' ? 'selected' : ''?>>Service Requests</option>
                        <option value="volunteering" <?= $formData['subject'] == 'volunteering' ? 'selected' : ''?>>Volunteering Opportunities</option>
                        <option value="other" <?= $formData['subject'] == 'other' ? 'selected' : ''?>>Other</option>
                    </select>

                    <label for="message"> Message: </label>
                    
                    <textarea id="message" name="message" required><?= htmlspecialchars($formData['message']) ?></textarea>

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
                    <?php foreach ($faqItems as $faq): ?>
                        <details <?= $faq['id'] ? 'id="' . e($faq['id']) . '"' : '' ?>>
                            <summary><?= e($faq['summary']) ?></summary>
                            <p><?= nl2br(e($faq['description'])) ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main> <!--    End page content    -->

    <!--    Footer section      -->
    <Footer>
        &copy; 2025 CityLink Initiatives. &nbsp;
        <a href="privacy.php"> Privacy Policy </a>
    </Footer>

    <?php if ($feedbackSuccess): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                document.getElementById('successModal').style.display = 'block';
            });
        </script>
    <?php endif; ?>

    <script type="text/javascript" src="./js/script.js" defer></script>
</body>

</html>