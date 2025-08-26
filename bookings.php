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

    // Optional: sort by date if it’s ISO-like
    usort($events, function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    return $events;
}
function renderEventItem(array $e): void
{
    $when = $e['date'] !== '' ? e($e['date']) : 'TBA';
    $loc = $e['location'] !== '' ? ' · ' . e($e['location']) : '';
    // You don't have a CTA in XML yet; we can derive a default route using id
    $ctaUrl = $e['id'] !== '' ? '/events/view.php?id=' . rawurlencode($e['id']) : '#';
    $ctaLabel = 'Learn More';
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
    <!--    Navigation Section      -->
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

        <div class="upcoming-events">
            <h2>Upcoming Events</h2>
            <?php foreach ($eventsItems as $ev) {
                renderEventItem($ev);
            } ?>
        </div>



        <div class="selected-event">
            <h2>Selected Event</h2>

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
    </main> <!--    End page content    -->

    <!--    Footer section      -->
    <Footer>
        &copy; 2025 CityLink Initiatives. &nbsp;
        <a href="privacy.html"> Privacy Policy </a>
    </Footer>

    <script type="text/javascript" src="./js/script.js" defer></script>
</body>

</html>