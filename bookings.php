<?php
declare(strict_types=1);

session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

$user = $_SESSION['user'] ?? [];

/* ===================== Event DB Functions ===================== */

/** @return array<int, array<string,string>> */
function getEventItems(): array
{
    $pdo = db();
    $sql = "SELECT id, title, description, date_info AS date, location, cta_label
            FROM events
            ORDER BY date_info ASC, title ASC";
    return _fetchAllAssoc($pdo, $sql);
}

/** @return array<string,string>|null */
function getEventById(string $id): ?array
{
    $id = trim($id);
    if ($id === '')
        return null;

    $pdo = _db();
    $stmt = $pdo->prepare("SELECT id, title, description, date_info AS date, location, cta_label
                           FROM events WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/* ===================== Load Events ===================== */

$eventsItems = getEventItems();

$selectedEventId = $_GET['event'] ?? null;
$selectedEvent = $selectedEventId ? getEventById($selectedEventId) : null;

/* ===================== Render Event HTML ===================== */

function renderEventItem(array $e): void
{
    $when = $e['date'] !== '' ? htmlspecialchars($e['date']) : 'TBA';
    $loc = $e['location'] !== '' ? ' · ' . htmlspecialchars($e['location']) : '';
    $ctaUrl = '?event=' . rawurlencode($e['id']);
    $ctaLabel = $e['cta_label'] ?? 'Select Event';
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

/* ===================== Navigation ===================== */

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');


?>
<!DOCTYPE html>
<html lang="en">
<?php include './shared/header.php'; ?>

<body class="sb-expanded">
    <?= $nav->render($current) ?>

    <main>
        <img src="./images/CityLinkLogo.png" alt="CityLink Initiatives" class="logo" /><br>

        <div class="upcoming-events">
            <h2>Upcoming Events</h2>
            <?php foreach ($eventsItems as $ev)
                renderEventItem($ev); ?>
        </div>

        <div class="selected-event">
            <h2>Selected Event:</h2>
            <?php if ($selectedEvent): ?>
                <p><strong><?= htmlspecialchars($selectedEvent['title']) ?></strong><br>
                    <?= htmlspecialchars($selectedEvent['date']) ?> | <?= htmlspecialchars($selectedEvent['location']) ?>
                </p>
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
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="amount">Amount of people:</label>
                        <input type="number" id="amount" name="amount" required>
                    </div>

                    <input type="hidden" name="eventName"
                        value="<?= htmlspecialchars($selectedEvent['title'] ?? '') ?>">
                    <input type="hidden" name="eventTime" value="<?= htmlspecialchars($selectedEvent['date'] ?? '') ?>">
                    <input type="hidden" name="eventLocation"
                        value="<?= htmlspecialchars($selectedEvent['location'] ?? '') ?>">

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

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);

                fetch('reserve.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        feedback.textContent = data.message;
                        feedback.style.color = data.success ? 'green' : 'red';
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

    <script src="./js/script.js"></script>
</body>

</html>