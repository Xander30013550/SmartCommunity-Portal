<?php
declare(strict_types=1);

session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';

// --- Access control: admin only ---
$user    = $_SESSION['user'] ?? null;
$isAdmin = isset($user) && (($user['role'] ?? '') === 'admin');
if (!$isAdmin) {
  header('Location: login.php');
  exit;
}

// --- Storage path ---
$xmlPath = __DIR__ . '/config/announcement.xml';

// --- Ensure XML file exists with root ---
if (!file_exists($xmlPath)) {
  @mkdir(dirname($xmlPath), 0777, true);
  $seed = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<announcements></announcements>
XML;
  file_put_contents($xmlPath, $seed);
}

// --- Helpers ---
function loadAnnouncements(string $path): SimpleXMLElement {
  $xml = @simplexml_load_file($path);
  if ($xml === false) {
    $xml = new SimpleXMLElement('<announcements/>');
  }
  return $xml;
}

function saveAnnouncements(SimpleXMLElement $xml, string $path): bool {
  $xml->asXML($path);
  return true;
}

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// --- CSRF (simple) ---
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$errors = [];
$notice = '';

// --- Handle POST actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $errors[] = 'Security token invalid. Please try again.';
  } else {
    $action = $_POST['action'] ?? '';
    $xml    = loadAnnouncements($xmlPath);

    if ($action === 'create') {
      $title    = trim($_POST['title'] ?? '');
      $body     = trim($_POST['body'] ?? '');
      $priority = trim($_POST['priority'] ?? 'medium');
      $start    = trim($_POST['start'] ?? '');
      $end      = trim($_POST['end'] ?? '');
      $link     = trim($_POST['link'] ?? '');

      if ($title === '')   $errors[] = 'Title is required.';
      if ($body === '')    $errors[] = 'Body is required.';
      if ($start === '')   $errors[] = 'Start date is required.';
      if (!in_array($priority, ['high', 'medium', 'low'], true)) $priority = 'medium';

      if (!$errors) {
        $node = $xml->addChild('announcement');
        $node->addAttribute('id', uniqid('ann_', true));
        $node->addChild('title', $title);
        $node->addChild('body', $body);
        $node->addChild('priority', $priority);
        $node->addChild('start', $start);
        $node->addChild('end', $end);
        $node->addChild('link', $link);
        saveAnnouncements($xml, $xmlPath);
        $notice = 'Announcement created.';
      }
    }

    if ($action === 'update') {
      $id = $_POST['id'] ?? '';
      if ($id === '') $errors[] = 'Invalid announcement ID.';
      if (!$errors) {
        foreach ($xml->announcement as $a) {
          if ((string)$a['id'] === $id) {
            $a->title    = trim($_POST['title'] ?? (string)$a->title);
            $a->body     = trim($_POST['body'] ?? (string)$a->body);
            $prio        = trim($_POST['priority'] ?? (string)$a->priority);
            $a->priority = in_array($prio, ['high','medium','low'], true) ? $prio : 'medium';
            $a->start    = trim($_POST['start'] ?? (string)$a->start);
            $a->end      = trim($_POST['end'] ?? (string)$a->end);
            $a->link     = trim($_POST['link'] ?? (string)$a->link);
            saveAnnouncements($xml, $xmlPath);
            $notice = 'Announcement updated.';
            break;
          }
        }
      }
    }

    if ($action === 'delete') {
      $id = $_POST['id'] ?? '';
      if ($id === '') $errors[] = 'Invalid announcement ID.';
      if (!$errors) {
        $i = 0; $deleted = false;
        foreach ($xml->announcement as $a) {
          if ((string)$a['id'] === $id) {
            unset($xml->announcement[$i]);
            $deleted = true;
            break;
          }
          $i++;
        }
        if ($deleted) {
          saveAnnouncements($xml, $xmlPath);
          $notice = 'Announcement deleted.';
        } else {
          $errors[] = 'Announcement not found.';
        }
      }
    }
  }
}

// --- Load announcements for display ---
$xml = loadAnnouncements($xmlPath);

// If editing one: ?edit=ID
$editId = $_GET['edit'] ?? '';
$editItem = null;
if ($editId) {
  foreach ($xml->announcement as $a) {
    if ((string)$a['id'] === $editId) { $editItem = $a; break; }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <?php include './shared/header.php'; ?>
<!--<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Announcement Management — Smart Community Portal</title>
  <link rel="stylesheet" href="./styles/styles.css" />
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
  
</head>-->
<style>
    main { max-width: 1100px; margin: 0 auto; padding: 1.25rem; }
    .page-title { display: flex; align-items: center; gap: .5rem; }
    .grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    @media (min-width: 900px) { .grid { grid-template-columns: 1fr 1fr; } }
    form.box { background: #fff; border: 1px solid #ddd; border-radius: 12px; padding: 1rem; }
    .row { display: grid; gap: .5rem; margin-bottom: .75rem; }
    label { font-weight: 600; }
    input[type="text"], input[type="date"], select, textarea {
      width: 100%; padding: .6rem .7rem; border: 1px solid #ccc; border-radius: 8px;
    }
    textarea { min-height: 120px; }
    .actions { display: flex; gap: .5rem; align-items: center; }
    .btn { display:inline-flex; align-items:center; gap:.35rem; padding:.55rem .9rem; border-radius:10px;
           border:1px solid #6b4ba6; background:#7b58bd10; text-decoration:none; font-weight:600; cursor:pointer; }
    .btn:hover { background:#7b58bd22; }
    .btn.danger { border-color:#b24a4a; background:#b24a4a10; }
    .btn.danger:hover { background:#b24a4a22; }
    table { width:100%; border-collapse: collapse; background:#fff; border-radius:12px; overflow:hidden; }
    th, td { padding:.7rem .8rem; border-bottom:1px solid #eee; vertical-align: top; }
    th { text-align:left; background:#faf7ff; }
    .flash { margin:.8rem 0; padding:.7rem .9rem; border-radius:10px; }
    .flash.ok { border:1px solid #9ad29a; background:#e9f7e9; }
    .flash.err { border:1px solid #e39c9c; background:#fdeeee; }
    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
  </style>

<body class="sb-expanded">
  <main>
    <div class="topbar">
      <h1 class="page-title"><i class='bx bx-bulb'></i> Announcement Management</h1>
      <div>
        <a class="btn" href="index.php"><i class='bx bx-home-alt'></i> Back to portal</a>
        <a class="btn" href="?logout=true"><i class='bx bx-log-out-circle'></i> Logout</a>
      </div>
    </div>

    <?php if ($notice): ?><div class="flash ok"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($errors): ?>
      <div class="flash err">
        <?php foreach ($errors as $err): ?>
          <div><?= e($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="grid">
      <!-- Create / Edit form -->
      <form class="box" method="post" action="">
        <h2><?= $editItem ? 'Edit Announcement' : 'Create Announcement' ?></h2>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <?php if ($editItem): ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= e((string)$editItem['id']) ?>">
        <?php else: ?>
          <input type="hidden" name="action" value="create">
        <?php endif; ?>

        <div class="row">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" required
                 value="<?= e($editItem ? (string)$editItem->title : '') ?>">
        </div>

        <div class="row">
          <label for="body">Body</label>
          <textarea id="body" name="body" required><?= e($editItem ? (string)$editItem->body : '') ?></textarea>
        </div>

        <div class="row" style="grid-template-columns: 1fr 1fr;">
          <div>
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
              <?php $p = $editItem ? (string)$editItem->priority : 'medium'; ?>
              <option value="high"   <?= $p==='high'?'selected':'' ?>>high</option>
              <option value="medium" <?= $p==='medium'?'selected':'' ?>>medium</option>
              <option value="low"    <?= $p==='low'?'selected':'' ?>>low</option>
            </select>
          </div>
          <div>
            <label for="link">Optional Link</label>
            <input type="text" id="link" name="link" placeholder="https://example.com"
                   value="<?= e($editItem ? (string)$editItem->link : '') ?>">
          </div>
        </div>

        <div class="row" style="grid-template-columns: 1fr 1fr;">
          <div>
            <label for="start">Start date</label>
            <input type="date" id="start" name="start" required
                   value="<?= e($editItem ? (string)$editItem->start : '') ?>">
          </div>
          <div>
            <label for="end">End date</label>
            <input type="date" id="end" name="end"
                   value="<?= e($editItem ? (string)$editItem->end : '') ?>">
          </div>
        </div>

        <div class="actions">
          <button class="btn" type="submit">
            <i class='bx bx-save'></i> <?= $editItem ? 'Save changes' : 'Create' ?>
          </button>
          <?php if ($editItem): ?>
            <a class="btn" href="announcement_management.php">
              <i class='bx bx-undo'></i> Cancel edit
            </a>
          <?php endif; ?>
        </div>
      </form>

      <!-- List -->
      <div class="box">
        <h2>All Announcements</h2>
        <table>
          <thead>
            <tr>
              <th style="width:16ch;">Start → End</th>
              <th>Title / Body</th>
              <th style="width:9ch;">Priority</th>
              <th style="width:14ch;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($xml->announcement) === 0): ?>
              <tr><td colspan="4">No announcements yet. Add one on the left.</td></tr>
            <?php else: ?>
              <?php foreach ($xml->announcement as $a): ?>
                <?php
                  $id   = (string)$a['id'];
                  $tit  = (string)$a->title;
                  $bod  = (string)$a->body;
                  $pri  = (string)$a->priority;
                  $sta  = (string)$a->start;
                  $en   = (string)$a->end;
                ?>
                <tr>
                  <td><strong><?= e($sta ?: '-') ?></strong><br>→ <?= e($en ?: '—') ?></td>
                  <td>
                    <div style="font-weight:700;"><?= e($tit) ?></div>
                    <div style="opacity:.85;"><?= nl2br(e($bod)) ?></div>
                  </td>
                  <td><?= e($pri ?: 'medium') ?></td>
                  <td class="actions">
                    <a class="btn" href="?edit=<?= e($id) ?>"><i class='bx bx-edit'></i> Edit</a>
                    <form method="post" action="" onsubmit="return confirm('Delete this announcement?');" style="display:inline;">
                      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= e($id) ?>">
                      <button class="btn danger" type="submit"><i class='bx bx-trash'></i> Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
