<?php
declare(strict_types=1);
libxml_use_internal_errors(true);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

// Saves Event on Selected Event button click
$eventsPath  = __DIR__ . '/config/events.xml';
$eventsItems = getEventItems($eventsPath);

// If xml is empty, fills default values
if (empty($eventsItems)) {
    $eventsItems = [
        [
            'id' => 'Event1',
            'title' => 'Event1',
            'description' => 'words',
            'date' => 'never',
            'location' => 'nowhere'
        ],
    ];
}

// Error Catch in case selected event was null
$selectedEventId = $_GET['event'] ?? null;
$selectedEvent   = null;

if ($selectedEventId !== null) {
    foreach ($eventsItems as $ev) {
        if ((string)($ev['id'] ?? '') === (string)$selectedEventId) {
            $selectedEvent = $ev;
            break;
        }
    }
}


$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav      = new NavRenderer($menuRepo);

$current = $_SERVER['REQUEST_URI'] ?? '/index.php';


function getEventItems(string $eventsPath): array
{
    $xml = loadXml($eventsPath);
    if (!$xml)
        return [];

    // Pick the first <eventlist> or the one with id="primary"
    $list = $xml->eventlist ?? null;
    if (!$list && isset($xml->eventlist)) {
        foreach ($xml->eventlist as $candidate) {
            if ((string) ($candidate['id'] ?? '') === 'primary') {
                $list = $candidate;
                break;
            }
        }
        // If still null, default to the first one
        if (!$list && isset($xml->eventlist[0]))
            $list = $xml->eventlist[0];
    }

    if (!$list)
        return [];

    $events = [];
    foreach ($list->event as $node) {
        $events[] = [
            'id' => (string) ($node['id'] ?? ''),
            'title' => trim((string) ($node->title ?? 'Untitled Event')),
            'description' => trim((string) ($node->description ?? '')),
            'date' => trim((string) ($node->date ?? '')),
            'location' => trim((string) ($node->location ?? '')),
        ];
    }

    return $events;
}
function renderEventItem(array $e): void
{
    $when = $e['date'] !== '' ? e($e['date']) : 'TBA';
    $loc = $e['location'] !== '' ? ' · ' . e($e['location']) : '';
    $ctaUrl = '?event=' . rawurlencode($e['id']);
    $ctaLabel = 'Select Event';
    ?>
    <div class="event-item">
        <div>
            <strong><?= e($e['title']) ?></strong><br>
            <small><?= $when . $loc ?></small><br>
            <?php if ($e['description'] !== ''): ?>
                Description: <?= e($e['description']) ?>
            <?php endif; ?>
        </div>
        <a class="uniform-button" href="<?= e($ctaUrl) ?>"><?= e($ctaLabel) ?></a>
    </div>
    <?php
}
$eventsPath = __DIR__ . '/config/events.xml';
$eventsItems = getEventItems($eventsPath);
if (empty($eventsItems)) {
    $eventsItems = [
        ['id' => 'Event1', 'title' => 'Event1', 'description' => 'words', 'date' => 'never', 'location' => 'nowhere'],
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

        <div class="upcoming-events">
            <h2>Upcoming Events</h2>
            <?php foreach ($eventsItems as $ev) {
                renderEventItem($ev);
            } ?>
        </div>

        <!-- Selected Event Field Box -->
        <div class="selected-event">
            <div id="userSelectedEvent">
                <h2>Selected Event:</h2>
                <?php if ($selectedEvent): ?>
                <div class="event-details">
                    <span class="event-title">
                        <span class="label">Event:</span> <?= e($selectedEvent['title']) ?>
                    </span>
                    <span class="event-info">
                        <span class="label">Date:</span> <?= e($selectedEvent['date']) ?>
                        <span class="separator">|</span>
                        <span class="label">Location:</span> <?= e($selectedEvent['location']) ?>
                    </span>
                </div>
                <?php else: ?>
                    <p>Please select an event to see details here.</p>
                <?php endif; ?>
            </div>
        </div>




        <div class="selected-event">
            <div class="form-inputs">
                <label for="name">Name: (Autofill if login)</label>
                <input type="text" id="name" name="name">

                <label for="email">Email: (Autofill if login)</label>
                <input type="email" id="email" name="email">

                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone">

                <label for="amount">Amount of people:</label>
                <input type="number" id="amount" name="amount">

                <button class="submit-button">Submit</button>
            </div>
        </div>
    </main>
    <!--    End page content    -->

    <!--    Footer section      -->
    <Footer>
        &copy; 2025 CityLink Initiatives. &nbsp;
        <a href="privacy.php"> Privacy Policy </a>
    </Footer>

    <script type="text/javascript" src="./js/script.js" defer></script>
</body>

</html>