<?php
//  This admin page script manages user session and authorization, handles adding, searching, 
//  updating, and deleting announcements with form data, and fetches announcement lists for 
//  display. It includes error handling, success messages, and input sanitization to maintain 
//  proper workflow and security.

declare(strict_types=1);

session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once __DIR__ . '/announcement_functions.php';
require_once 'auth.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

// --- Ensure user is logged in and is an admin ---
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
if ($_SESSION['user']['role'] !== 'admin') {
  header('Location: user_home.php');
  exit;
}
$user = $_SESSION['user'];

// --- Handle logout ---
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit;
}

// --- App boot ---
date_default_timezone_set('Australia/Perth');

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'admin.php');

// ---------------- Page State ----------------
$errors = [];
$successMessage = '';

$announcement_id = $priority = $title = $body = $start = $end = $link_url = $link_text = '';
$searchTerm = '';

// small helper if not provided by your functions.php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}

// ---------------- Handlers ------------------

// Add announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
  $announcement_id = trim($_POST['announcement_id'] ?? '');
  $priority = trim($_POST['priority'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $body = trim($_POST['body'] ?? '');
  $start = trim($_POST['start'] ?? '');
  $end = trim($_POST['end'] ?? '');
  $link_url = trim($_POST['link_url'] ?? '');
  $link_text = trim($_POST['link_text'] ?? '');

  $res = addAnnouncement([
    'id' => $announcement_id,
    'priority' => $priority,
    'title' => $title,
    'body' => $body,
    'start' => $start,
    'end' => $end,
    'link_url' => $link_url ?: null,
    'link_text' => $link_text ?: null,
  ]);

  if ($res === true || (is_array($res) && ($res['ok'] ?? false))) {
    $successMessage = "Announcement '{$announcement_id}' was added.";
    // reset form
    $announcement_id = $priority = $title = $body = $start = $end = $link_url = $link_text = '';
  } else {
    $errors['general'] = is_array($res) && !empty($res['error']) ? $res['error'] : 'Failed to add announcement.';
  }
}

// Search announcements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
  $searchTerm = trim($_POST['search_term'] ?? '');
  if ($searchTerm === '') {
    $errors['search'] = 'Please enter an ID, title, priority, or date (YYYY-MM-DD).';
  }
}

// Update announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_announcement'])) {
  $upd_id = trim($_POST['upd_announcement_id'] ?? '');
  $upd_priority = trim($_POST['upd_priority'] ?? '');
  $upd_title = trim($_POST['upd_title'] ?? '');
  $upd_body = trim($_POST['upd_body'] ?? '');
  $upd_start = trim($_POST['upd_start'] ?? '');
  $upd_end = trim($_POST['upd_end'] ?? '');
  $upd_link_url = trim($_POST['upd_link_url'] ?? '');
  $upd_link_text = trim($_POST['upd_link_text'] ?? '');

  if ($upd_id === '') {
    $errors['general'] = 'Announcement ID is required to update.';
  } else {
    $payload = array_filter([
      'priority' => $upd_priority ?: null,
      'title' => $upd_title ?: null,
      'body' => $upd_body ?: null,
      'start' => $upd_start ?: null,
      'end' => $upd_end ?: null,
      'link_url' => $upd_link_url ?: null,
      'link_text' => $upd_link_text ?: null,
    ], fn($v) => $v !== null && $v !== '');

    if (!$payload) {
      $errors['general'] = 'No fields to update.';
    } else {
      $ok = updateAnnouncement($upd_id, $payload);
      if ($ok) {
        $successMessage = "Announcement '{$upd_id}' was updated.";
      } else {
        $errors['general'] = 'Failed to update the announcement. Check the ID and values.';
      }
    }
  }
}

// Delete announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement'])) {
  $del_id = trim($_POST['del_announcement_id'] ?? '');
  if ($del_id === '') {
    $errors['delete'] = 'Enter an Announcement ID to delete.';
  } else {
    $ok = deleteAnnouncement($del_id);
    if ($ok) {
      $successMessage = "Announcement '{$del_id}' was deleted.";
    } else {
      $errors['delete'] = 'Failed to delete announcement. Check the ID.';
    }
  }
}

// Fetch list (initial or post-action refresh)
$announcements = $searchTerm && empty($errors['search'])
  ? searchAnnouncements($searchTerm)
  : getAnnouncements();
?>

<!DOCTYPE html>
<html lang="en">
<?php include './shared/header.php'; ?>
<body class="sb-expanded">
  <?= $nav->render($current) ?>

  <main>
    <section>
      <h1 class="page-title">Admin Portal - Manage Announcements</h1>

      <div class="user-info" style="margin-bottom: 1em; font-size: 1rem;">
        Logged in as <strong><?= e($user['name']) ?></strong><br>
        <a href="admin.php">Return to Dashboard</a>
        &nbsp;|&nbsp; <a href="user_management.php">Edit Users</a>
        &nbsp;|&nbsp; <a href="?logout=true">Logout</a>
      </div>

      <?php if ($successMessage): ?>
        <div class="success"><?= e($successMessage) ?></div>
      <?php elseif (!empty($errors['general'])): ?>
        <div class="error"><?= e($errors['general']) ?></div>
      <?php endif; ?>

      <div style="display:flex; width:100%; gap:12px; align-items:flex-start; flex-wrap:wrap;">

        <!-- Add Announcement -->
        <div style="flex:1 1 360px; padding:10px;">
          <h2>Add a New Announcement</h2>
          <form method="POST" action="" novalidate style="width:99%;">
            <label for="announcement_id">Announcement ID</label>
            <input type="text" id="announcement_id" name="announcement_id" value="<?= e($announcement_id) ?>"
              placeholder="e.g. a-101" required />
            <?php if (!empty($errors['announcement_id'])): ?>
              <div class="error"><?= e($errors['announcement_id']) ?></div><?php endif; ?>

            <label for="priority">Priority</label>
            <select id="priority" name="priority" required>
              <option value="low" <?= ($priority === 'low') ? 'selected' : '' ?>>Low</option>
              <option value="medium" <?= ($priority === 'medium') ? 'selected' : '' ?>>Medium</option>
              <option value="high" <?= ($priority === 'high') ? 'selected' : '' ?>>High</option>
            </select>
            <?php if (!empty($errors['priority'])): ?>
              <div class="error"><?= e($errors['priority']) ?></div><?php endif; ?>

            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?= e($title) ?>" required />

            <label for="body">Message</label>
            <textarea id="body" name="body" rows="4" required><?= e($body) ?></textarea>

            <label for="start">Start Date</label>
            <input type="date" id="start" name="start" value="<?= e($start) ?>" required />

            <label for="end">End Date</label>
            <input type="date" id="end" name="end" value="<?= e($end) ?>" required />

            <fieldset>
              <legend>Link Information</legend>
              <label for="link_url">Link URL</label>
              <input type="url" id="link_url" name="link_url" value="<?= e($link_url) ?>"
                placeholder="https://example.com/details" />
              <label for="link_text">Link Text</label>
              <input type="text" id="link_text" name="link_text" value="<?= e($link_text) ?>" placeholder="More info" />
            </fieldset>

            <button type="submit" name="add_announcement">Add Announcement</button>
          </form>
        </div>

        <!-- View / Search -->
        <div style="flex:1 1 360px; padding:10px;">
          <h2>View Announcements</h2>
          <form method="post" action="" style="width:99%; display:flex; gap:.5rem; align-items:center;">
            <label for="search_term" style="flex:0 0 auto;">Search:</label>
            <input type="text" id="search_term" name="search_term" value="<?= e($searchTerm) ?>"
              placeholder="ID, title, priority, or YYYY-MM-DD" style="flex:1 1 auto;" />
            <button type="submit" name="search">Search</button>
          </form>
          <br>
          <?php if (!empty($errors['search'])): ?>
            <div class="error"><?= e($errors['search']) ?></div><?php endif; ?>

          <?php if (!empty($announcements)): ?>
            <ul>
              <?php foreach ($announcements as $a): ?>
                <li style="margin-bottom:.75rem; padding:.5rem; border:1px solid #ddd; border-radius:8px;">
                  <strong>ID:</strong> <?= e($a['id']) ?> &nbsp; | &nbsp;
                  <strong>Priority:</strong> <?= e($a['priority']) ?><br>
                  <strong>Title:</strong> <?= e($a['title']) ?><br>
                  <strong>Body:</strong> <?= e($a['body']) ?><br>
                  <strong>Start:</strong> <?= e($a['start']) ?> &nbsp; <strong>End:</strong> <?= e($a['end']) ?><br>
                  <?php if (!empty($a['link_url'])): ?>
                    <strong>Link:</strong> <a href="<?= e($a['link_url']) ?>" target="_blank"
                      rel="noopener"><?= e($a['link_text'] ?: $a['link_url']) ?></a>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>No announcements found.</p>
          <?php endif; ?>
        </div>

        <!-- Update -->
        <div style="flex:1 1 360px; padding:10px;">
          <h2>Update Announcement</h2>
          <form method="POST" action="" style="width:99%;">
            <label for="upd_announcement_id">Announcement ID</label>
            <input type="text" id="upd_announcement_id" name="upd_announcement_id" required />

            <label for="upd_priority">Priority (optional)</label>
            <select id="upd_priority" name="upd_priority">
              <option value="">(no change)</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>

            <label for="upd_title">Title (optional)</label>
            <input type="text" id="upd_title" name="upd_title" />

            <label for="upd_body">Message (optional)</label>
            <textarea id="upd_body" name="upd_body" rows="3"></textarea>

            <label for="upd_start">Start Date (optional)</label>
            <input type="date" id="upd_start" name="upd_start" />

            <label for="upd_end">End Date (optional)</label>
            <input type="date" id="upd_end" name="upd_end" />

            <fieldset>
              <legend>Link (optional)</legend>
              <label for="upd_link_url">Link URL</label>
              <input type="url" id="upd_link_url" name="upd_link_url" />
              <label for="upd_link_text">Link Text</label>
              <input type="text" id="upd_link_text" name="upd_link_text" />
            </fieldset>

            <button type="submit" name="update_announcement">Update Announcement</button>
          </form>
        </div>

        <!-- Delete -->
        <div style="flex:1 1 360px; padding:10px;">
          <h2>Delete Announcement</h2>
          <form method="POST" action="" style="width:99%;">
            <label for="del_announcement_id">Announcement ID</label>
            <input type="text" id="del_announcement_id" name="del_announcement_id" required />
            <button type="submit" name="delete_announcement">Delete</button>
          </form>
          <?php if (!empty($errors['delete'])): ?>
            <div class="error"><?= e($errors['delete']) ?></div><?php endif; ?>
        </div>

      </div>
    </section>
  </main>

  <footer>
    &copy; 2025 CityLink Initiatives.
    <a href="/privacy.php">Privacy Policy</a>
  </footer>
  <script src="./js/script.js"></script>
</body>

</html>