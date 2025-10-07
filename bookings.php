<?php
declare(strict_types=1);
session_start();
libxml_use_internal_errors(true);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;



$user = $_SESSION['user']; // $_SESSION['user']['name'], ['email'], ['id']

// -------------------- FUNCTIONS --------------------

// Load events from XML
function getEventItems(string $eventsPath): array {
    if (!file_exists($eventsPath)) return [];

    $xml = simplexml_load_file($eventsPath);
    if (!$xml) return [];

    $list = $xml->eventlist[0] ?? null;
    if (!$list) return [];

    $events = [];
    foreach ($list->event as $node) {
        $events[] = [
            'id' => (string)($node['id'] ?? ''),
            'title' => trim((string)($node->title ?? 'Untitled Event')),
            'description' => trim((string)($node->description ?? '')),
            'date' => trim((string)($node->date ?? '')),
            'location' => trim((string)($node->location ?? '')),
        ];
    }
    return $events;
}

function renderEventItem(array $e): void {
    $when = $e['date'] !== '' ? htmlspecialchars($e['date']) : 'TBA';
    $loc = $e['location'] !== '' ? ' · ' . htmlspecialchars($e['location']) : '';
    $ctaUrl = '?event=' . rawurlencode($e['id']);
    $ctaLabel = 'Select Event';
    ?>
    <div class="event-item">
        <div>
            <strong><?= htmlspecialchars($e['title']) ?></strong><br>
            <small><?= $when . $loc ?></small><br>
            <?php if ($e['description'] !== ''): ?>
                Description: <?= htmlspecialchars($e['description']) ?>
            <?php endif; ?>
        </div>
        <a class="uniform-button" href="<?= htmlspecialchars($ctaUrl) ?>"><?= htmlspecialchars($ctaLabel) ?></a>
    </div>
    <?php
}

// -------------------- LOAD EVENTS --------------------
$eventsPath = __DIR__ . '/config/events.xml';
$eventsItems = getEventItems($eventsPath);

if (empty($eventsItems)) {
    $eventsItems = [
        ['id'=>'Event1','title'=>'Event1','description'=>'words','date'=>'never','location'=>'nowhere']
    ];
}

// Selected Event
$selectedEventId = $_GET['event'] ?? null;
$selectedEvent = null;
if ($selectedEventId) {
    foreach ($eventsItems as $ev) {
        if ($ev['id'] === $selectedEventId) {
            $selectedEvent = $ev;
            break;
        }
    }
}

// -------------------- NAVIGATION --------------------
$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: 'index.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Smart Community Portal - Bookings</title>
<link rel="stylesheet" href="./styles/styles.css">
<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body class="sb-expanded">
    <?= $nav->render($current) ?>

    <!--    Page Content    -->
        
    <main>
        <img src="./images/CityLinkLogo.png" alt="CityLink Initiatives" class="logo" /><br>

        <div class="upcoming-events">
            <h2>Upcoming Events</h2>
            <?php foreach ($eventsItems as $ev) {
                renderEventItem($ev);
            } ?>
        </div>

    <div class="selected-event">
        <h2>Selected Event:</h2>
        <?php if ($selectedEvent): ?>
            <p><strong><?= htmlspecialchars($selectedEvent['title']) ?></strong><br>
            <?= htmlspecialchars($selectedEvent['date']) ?> | <?= htmlspecialchars($selectedEvent['location']) ?></p>
        <?php else: ?>
            <p>Please select an event to see details here.</p>
        <?php endif; ?>
    </div>

    <div class="selected-event">
        <h2>Make a Reservation</h2>
        <div class="form-inputs">
            <form id="reservationForm" method="POST">
                <div class="field">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="amount">Amount of people:</label>
                    <input type="number" id="amount" name="amount" required>
                </div>

                <input type="hidden" name="eventName" value="<?= htmlspecialchars($selectedEvent['title'] ?? '') ?>">
                <input type="hidden" name="eventTime" value="<?= htmlspecialchars($selectedEvent['date'] ?? '') ?>">
                <input type="hidden" name="eventLocation" value="<?= htmlspecialchars($selectedEvent['location'] ?? '') ?>">

                <div class="field">
                    <button type="submit">Submit Reservation</button>
                </div>

                <div id="reservationFeedback"></div>
            </form>
        </div>
    </div>
</main>

<footer>
&copy; 2025 CityLink Initiatives. &nbsp;<a href="privacy.php">Privacy Policy</a>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservationForm');
    const feedback = document.getElementById('reservationFeedback');

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // prevent normal form submission

        const formData = new FormData(form);

        fetch('reserve.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            feedback.textContent = data.message;  
            feedback.style.color = data.success ? 'green' : 'red';
            console.log(data); // <-- logs PHP/DB result
            if (data.success) form.reset();
        })
        .catch(err => {
            feedback.textContent = "Error submitting reservation.";
            feedback.style.color = 'red';
            console.error(err);
        });
    });
});

</script>

</body>
</html>
