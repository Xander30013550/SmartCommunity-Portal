<?php
declare(strict_types=1);
session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

// --- Ensure user is logged in and is a general user ---
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

if ($_SESSION['user']['role'] === 'admin') {
  header('Location: admin.php'); // Redirect admins to the admin dashboard
  exit;
}

if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit;
}

$user = $_SESSION['user'];

// --- App boot ---
date_default_timezone_set('Australia/Perth');

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'user_home.php');

// --- Load XML ---
$xmlPath = __DIR__ . '/config/announcement.xml';
$xml = @simplexml_load_file($xmlPath);

if ($xml === false) {
  $items = [];
  $xmlErrorMsg = "Could not load XML: " . htmlspecialchars($xmlPath);
} else {
  function weightOf(string $p): int
  {
    return match (strtolower($p)) {
      'high' => 3,
      'normal' => 2,
      'low' => 1,
      default => 1
    };
  }

  $today = new DateTime();
  $items = [];

  foreach ($xml->announcement as $a) {
    $start = isset($a->start) && trim((string) $a->start) !== '' ? new DateTime((string) $a->start) : null;
    $end = isset($a->end) && trim((string) $a->end) !== '' ? new DateTime((string) $a->end) : null;

    $okStart = !$start || $today >= $start;
    $okEnd = !$end || $today < $end;

    if (!($okStart && $okEnd)) {
      continue;
    }

    $items[] = [
      'id' => (string) ($a['id'] ?? ''),
      'priority' => strtolower((string) ($a['priority'] ?? 'normal')),
      'title' => trim((string) $a->title),
      'body' => trim((string) $a->body),
      'start' => $start,
      'end' => $end,
      'category' => trim((string) $a->category) ?: 'General',
      'link' => (isset($a->link) && isset($a->link['url']))
        ? ['url' => (string) $a->link['url'], 'text' => (string) ($a->link['text'] ?? 'Learn more')]
        : null,
    ];
  }

  usort($items, function ($a, $b) {
    $w = weightOf($b['priority']) <=> weightOf($a['priority']);
    if ($w !== 0)
      return $w;
    return $a['end'] <=> $b['end'];
  });
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include './shared/header.php'; ?>

<body class="sb-expanded">
  <?= $nav->render($current) ?>

  <main>
    <h1 class="page-title">Welcome to the User Dashboard</h1>

    <div class="user-info" style="margin-bottom: 1em;">
      👤 Logged in as <strong><?= htmlspecialchars($user['name']) ?></strong>
      (<?= htmlspecialchars($user['role']) ?>)
      &nbsp;|&nbsp;
      <a href="?logout=true">Logout</a>
    </div>

    <?php if (!empty($xmlErrorMsg)): ?>
      <div class="alert error"><?= $xmlErrorMsg ?></div>
    <?php elseif (empty($items)): ?>
      <div class="alert info">No current announcements.</div>
    <?php else: ?>
      <section class="slider" aria-roledescription="carousel" aria-label="Announcements" data-autoplay="true"
        data-interval="4000">
        <div class="slider-track" id="slider-track">
          <?php foreach ($items as $i => $a): ?>
            <figure class="slide priority-<?= htmlspecialchars($a['priority']) ?>" aria-roledescription="slide"
              aria-label="Announcement <?= $i + 1 ?> of <?= count($items) ?>">
              <article class="ann-card">
                <div class="ann-chip"><?= htmlspecialchars($a['category']) ?></div>
                <h2 class="ann-title"><?= htmlspecialchars($a['title']) ?></h2>
                <p class="ann-body"><?= nl2br(htmlspecialchars($a['body'])) ?></p>
                <p class="ann-when">
                  <?php
                  $parts = [];
                  if ($a['start'])
                    $parts[] = 'From ' . $a['start']->format('M j, Y');
                  if ($a['end'])
                    $parts[] = 'until ' . $a['end']->format('M j, Y');
                  echo htmlspecialchars(implode(' ', $parts));
                  ?>
                </p>
                <?php if (!empty($a['link']['url'])): ?>
                  <a class="ann-link" href="<?= htmlspecialchars($a['link']['url']) ?>" target="_blank"
                    rel="noopener noreferrer">
                    <?= htmlspecialchars($a['link']['text'] ?: 'Learn more') ?>
                  </a>
                <?php endif; ?>
              </article>
            </figure>
          <?php endforeach; ?>
        </div>

        <button class="nav prev" aria-label="Previous announcement" data-dir="-1">❮</button>
        <button class="nav next" aria-label="Next announcement" data-dir="1">❯</button>

        <div class="dots" id="slider-dots" aria-label="Slide navigation"></div>
        <p class="sr-only" aria-live="polite" id="sr-status"></p>
      </section>
    <?php endif; ?>
  </main>

  <footer>
    &copy; 2025 CityLink Initiatives.
    <a href="privacy.php">Privacy Policy</a>
  </footer>

</body>

</html>