<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php'; // expects DB_* constants
date_default_timezone_set('Australia/Perth');

/* =========================== Public API =========================== */

/** @return array<int, array<string, string>> */
function getAnnouncements(): array
{
    $pdo = _db();
    $sql = "SELECT id, priority, title, body, `start`, `end`, link_url, link_text, created_at, updated_at
            FROM announcements
            ORDER BY `start` DESC, FIELD(priority, 'high','medium','low'), updated_at DESC";
    return _fetchAllAssoc($pdo, $sql);
}

/** @return array<int, array<string, string>> */
//  This function searches announcements by a term, using full-text search if available and falling 
//  back to LIKE queries otherwise, returning matched records ordered by start date, priority, and 
//  update time. It supports exact matches on ID, priority, and dates, enhancing flexible search 
//  capabilities.
function searchAnnouncements(string $term): array
{
    $term = trim($term);
    if ($term === '')
        return getAnnouncements();

    $pdo = _db();

    // If you enable FULLTEXT (recommended), this branch will be used.
    if (_hasFulltext($pdo)) {
        // BOOLEAN MODE: +term to require; fallback LIKE for edge cases below.
        $sql = "SELECT id, priority, title, body, `start`, `end`, link_url, link_text, created_at, updated_at
                FROM announcements
                WHERE MATCH(title, body) AGAINST (:q IN BOOLEAN MODE)
                   OR id = :eq
                   OR priority = :prio
                   OR `start` = :date_eq_start
                   OR `end`   = :date_eq_end
                ORDER BY `start` DESC, FIELD(priority, 'high','medium','low'), updated_at DESC";
        $stmt = $pdo->prepare($sql);
        $q = _toBooleanQuery($term);
        $dateEq = _maybeYmd($term);
        $stmt->execute([
            ':q' => $q,
            ':eq' => $term,
            ':prio' => strtolower($term),
            ':date_eq_start' => $dateEq,
            ':date_eq_end' => $dateEq,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows)
            return $rows;
        // Fallback to LIKE if fulltext yields nothing.
    }

    // Portable LIKE fallback (searches across key columns).
    $like = '%' . $term . '%';
    $sql = "SELECT id, priority, title, body, `start`, `end`, link_url, link_text, created_at, updated_at
        FROM announcements
        WHERE id LIKE :like_id
           OR title LIKE :like_title
           OR body LIKE :like_body
           OR priority LIKE :like_priority
           OR `start` = :date_eq_start
           OR `end`   = :date_eq_end
        ORDER BY `start` DESC, FIELD(priority, 'high','medium','low'), updated_at DESC";
    $stmt = $pdo->prepare($sql);
    $dateEq = _maybeYmd($term);
    $stmt->execute([
        ':like_id' => $like,
        ':like_title' => $like,
        ':like_body' => $like,
        ':like_priority' => $like,
        ':date_eq_start' => $dateEq,
        ':date_eq_end' => $dateEq,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


//  This function validates announcement data, checks if an announcement ID is unique, 
//  and inserts a new announcement into the database with timestamps, returning true on 
//  success or an error message if it fails.
function addAnnouncement(array $data)
{
    $v = _validateAnnouncementData($data, false);
    if (!$v['ok'])
        return ['error' => implode(' ', $v['errors'])];

    $pdo = _db();

    // Unique ID check
    $exists = _fetchValue($pdo, "SELECT 1 FROM announcements WHERE id = ?", [$data['id']]);
    if ($exists)
        return ['error' => "Announcement with id '{$data['id']}' already exists."];

    $now = _now();
    $sql = "INSERT INTO announcements
            (id, priority, title, body, `start`, `end`, link_url, link_text, created_at, updated_at)
            VALUES (:id, :priority, :title, :body, :start, :end, :link_url, :link_text, :created_at, :updated_at)";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ':id' => $data['id'],
        ':priority' => strtolower($data['priority']),
        ':title' => $data['title'],
        ':body' => $data['body'],
        ':start' => $data['start'], // YYYY-MM-DD
        ':end' => $data['end'],   // YYYY-MM-DD
        ':link_url' => $data['link_url'] ?? null,
        ':link_text' => $data['link_text'] ?? null,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    if ($ok)
        return true;
    return ['error' => 'There was an error adding the announcement.'];
}

/** Only provided keys are updated. */
//  This function validates and updates specified fields of an announcement by ID, 
//  dynamically building the SQL SET clause and updating the `updated_at` timestamp, 
//  returning true on success or false on failure.
function updateAnnouncement(string $id, array $changes): bool
{
    $id = trim($id);
    if ($id === '')
        return false;

    $v = _validateAnnouncementData($changes, true);
    if (!$v['ok'])
        return false;

    $pdo = _db();

    // Build dynamic SET
    $fields = [];
    $params = [':id' => $id];
    $map = [
        'priority' => 'priority',
        'title' => 'title',
        'body' => 'body',
        'start' => '`start`',
        'end' => '`end`',
        'link_url' => 'link_url',
        'link_text' => 'link_text',
    ];
    foreach ($map as $k => $col) {
        if (array_key_exists($k, $changes) && $changes[$k] !== '' && $changes[$k] !== null) {
            $fields[] = "$col = :$k";
            $params[":$k"] = ($k === 'priority') ? strtolower((string) $changes[$k]) : (string) $changes[$k];
        }
    }
    if (!$fields)
        return true; // nothing to change

    $fields[] = "updated_at = :updated_at";
    $params[':updated_at'] = _now();

    $sql = "UPDATE announcements SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

//  This function deletes an announcement by its trimmed ID and returns 
// true if successful or false if the ID is empty or the deletion fails.
function deleteAnnouncement(string $id): bool
{
    $id = trim($id);
    if ($id === '')
        return false;

    $pdo = _db();
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/* =========================== Validation =========================== */
//  This function validates announcement data, checking required fields for new entries, 
//  ensuring priority values are correct, dates are properly formatted and ordered, URLs 
//  are valid, and link text is provided if a URL exists, returning an array with validation 
//  status and error messages.
function _validateAnnouncementData(array $data, bool $isUpdate): array
{
    $errors = [];

    if (!$isUpdate) {
        foreach (['id', 'priority', 'title', 'body', 'start', 'end'] as $req) {
            if (empty($data[$req]))
                $errors[] = "Field '{$req}' is required.";
        }
    }

    if (
        isset($data['priority']) && $data['priority'] !== '' &&
        !in_array(strtolower((string) $data['priority']), ['low', 'medium', 'high'], true)
    ) {
        $errors[] = "Priority must be low, medium, or high.";
    }

    foreach (['start', 'end'] as $d) {
        if (isset($data[$d]) && $data[$d] !== '' && !_validDateYmd((string) $data[$d])) {
            $errors[] = ucfirst($d) . " must be in YYYY-MM-DD format.";
        }
    }

    if (
        !empty($data['start']) && !empty($data['end'])
        && _validDateYmd((string) $data['start']) && _validDateYmd((string) $data['end'])
        && (string) $data['end'] < (string) $data['start']
    ) {
        $errors[] = 'End date must be on or after the start date.';
    }

    if (
        isset($data['link_url']) && $data['link_url'] !== '' &&
        !filter_var((string) $data['link_url'], FILTER_VALIDATE_URL)
    ) {
        $errors[] = 'Link URL must be a valid URL.';
    }
    if (
        isset($data['link_url']) && $data['link_url'] !== '' &&
        isset($data['link_text']) && $data['link_text'] === ''
    ) {
        $errors[] = 'Provide link text when a link URL is set.';
    }

    return ['ok' => empty($errors), 'errors' => $errors];
}

//  This function checks if a string is a valid date in YYYY-MM-DD format by 
//  parsing it with DateTime and verifying the exact format.
function _validDateYmd(string $d): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}

/* ============================ DB Utils ============================ */
//  This function returns a singleton PDO connection to a MySQL database using defined 
//  constants for host, database, user, password, and charset, with error handling and 
//  default fetch mode set.
function _db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO)
        return $pdo;

    $host = DB_HOST;
    $db = DB_NAME;
    $user = DB_USER;
    $pass = defined('DB_PASS') ? DB_PASS : '';
    $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $opt);
    return $pdo;
}


//  This function executes a prepared PDO query with optional parameters and returns 
//  the first column of the first row or null if no results are found.
function _fetchValue(PDO $pdo, string $sql, array $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $val = $stmt->fetchColumn();
    return $val === false ? null : $val;
}

//  This function returns the current date and time as a string formatted like 
//  "YYYY-MM-DD HH:MM:SS".
function _now(): string
{
    return (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
}

/* ========================== Search Helpers ========================= */

/** Detect if FULLTEXT index exists (simple cache). */
//  This function checks if the `announcements` table has a FULLTEXT index named 
//  `ft_title_body` and caches the result to avoid repeated queries.
function _hasFulltext(PDO $pdo): bool
{
    static $cache = null;
    if ($cache !== null)
        return $cache;

    // Look for a FULLTEXT index named ft_title_body (matches DDL below).
    $sql = "SHOW INDEX FROM announcements WHERE Index_type = 'FULLTEXT' AND Key_name = 'ft_title_body'";
    $cache = (bool) _fetchValue($pdo, $sql);
    return $cache;
}

/** Convert raw term into a BOOLEAN MODE query string. */
//  This function converts a search term into a MySQL full-text boolean query by prefixing 
//  each word with a `+` to require its presence, preserving any existing `+` or `-` prefixes.
function _toBooleanQuery(string $term): string
{
    $parts = preg_split('/\s+/', trim($term));
    $parts = array_filter($parts, fn($p) => $p !== '');
    if (!$parts)
        return $term;
    // Require each term, prefix with '+' (e.g., "+power +outage")
    return implode(' ', array_map(fn($p) => (str_starts_with($p, '+') || str_starts_with($p, '-')) ? $p : ('+' . $p), $parts));
}

/** Return Y-m-d if $s looks like a date; otherwise null. */
//  This function trims a string and returns it if it's a valid YYYY-MM-DD date; 
//  otherwise, it returns null.
function _maybeYmd(string $s): ?string
{
    $s = trim($s);
    return _validDateYmd($s) ? $s : null;
}