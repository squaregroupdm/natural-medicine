<?php
/* =====================================================================
   bootstrap.php

   Every page on the site starts by including this file. It:
     1. loads the settings
     2. opens the database
     3. starts the session
     4. defines the small helper functions used everywhere

   Nothing in here needs changing day to day.
   ===================================================================== */

declare(strict_types=1);

define('APP_ROOT', __DIR__);
define('WEB_ROOT', dirname(__DIR__) . '/public_html');

/* ---------- settings ---------- */

$configFile = APP_ROOT . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Setup is not finished: private_app/config.php is missing. '
       . 'Copy config.sample.php to config.php and fill in the database details.');
}
$CONFIG = require $configFile;

/* ---------- database ---------- */

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    global $CONFIG;
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
                   $CONFIG['db_host'], $CONFIG['db_name']);
    try {
        $pdo = new PDO($dsn, $CONFIG['db_user'], $CONFIG['db_pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Could not connect to the database. Check the details in private_app/config.php.');
    }
    return $pdo;
}

/** Run a query and get all rows back. */
function q(string $sql, array $args = []): array
{
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

/** Run a query and get the first row, or null. */
function q1(string $sql, array $args = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($args);
    $row = $st->fetch();
    return $row === false ? null : $row;
}

/** Run a statement that changes data. Returns rows affected. */
function run(string $sql, array $args = []): int
{
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->rowCount();
}

/* ---------- session ---------- */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('sqnm');
    session_start();
}

/* ---------- language ---------- */

/**
 * Which language the visitor is reading in.
 * ?lang=bn in the address switches it and remembers the choice.
 */
function lang(): string
{
    static $lang = null;
    if ($lang !== null) return $lang;

    global $CONFIG;
    $want = $_GET['lang'] ?? ($_COOKIE['sqnm_lang'] ?? $CONFIG['default_lang']);
    $lang = ($want === 'bn') ? 'bn' : 'en';

    if (isset($_GET['lang'])) {
        setcookie('sqnm_lang', $lang, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'samesite' => 'Lax',
        ]);
    }
    return $lang;
}

/**
 * Pick the right language out of a row.
 * If the Bangla is empty it falls back to the English, so the page
 * never shows a blank while translation is still being done.
 */
function pick(array $row, string $field): string
{
    $bn = trim((string)($row[$field . '_bn'] ?? ''));
    $en = trim((string)($row[$field . '_en'] ?? ''));
    return (lang() === 'bn' && $bn !== '') ? $bn : $en;
}

/** One line of page copy, by section and key. */
function copy_defaults(): array
{
    static $rows = null;
    if ($rows === null) $rows = require APP_ROOT . '/copy_defaults.php';
    return $rows;
}

/**
 * One piece of page wording, in the visitor's language.
 * Reads the `copy` table (edited in Admin > Page text). If a row is
 * missing there, falls back to private_app/copy_defaults.php.
 * $swap fills placeholders: copyText('catalogue','count',['n'=>3])
 */
function copyText(string $section, string $key, array $swap = []): string
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (copy_defaults() as $d) {
            $all[$d[0] . '.' . $d[1]] = ['val_en' => $d[3], 'val_bn' => $d[4]];
        }
        try {
            foreach (q('SELECT section, k, val_en, val_bn FROM copy') as $r) {
                $all[$r['section'] . '.' . $r['k']] = $r;
            }
        } catch (Throwable $e) {
            // table not there yet: the defaults carry the page
        }
    }
    $row  = $all[$section . '.' . $key] ?? null;
    $text = $row ? pick($row, 'val') : '';
    foreach ($swap as $name => $value) {
        $text = str_replace('{' . $name . '}', (string)$value, $text);
    }
    return $text;
}

/**
 * Add any wording that exists in copy_defaults.php but not yet in the
 * database, and refresh labels and order. Never overwrites edited text.
 * Returns how many rows were added.
 */
function sync_copy(): int
{
    $have = [];
    foreach (q('SELECT section, k FROM copy') as $r) $have[$r['section'] . '.' . $r['k']] = true;
    $added = 0;
    foreach (copy_defaults() as $i => $d) {
        $order = ($i + 1) * 10;
        if (isset($have[$d[0] . '.' . $d[1]])) {
            run('UPDATE copy SET label = ?, sort_order = ? WHERE section = ? AND k = ?',
                [$d[2], $order, $d[0], $d[1]]);
        } else {
            run('INSERT INTO copy (section, k, label, val_en, val_bn, sort_order) VALUES (?,?,?,?,?,?)',
                [$d[0], $d[1], $d[2], $d[3], $d[4], $order]);
            $added++;
        }
    }
    return $added;
}

/** Turn 0-9 into Bangla digits when the visitor is reading Bangla. */
function digits(string $text): string
{
    if (lang() !== 'bn') return $text;
    return strtr($text, ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯']);
}

/* ---------- output safety ---------- */

/** Escape text before putting it on the page. */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Web address of an uploaded file. */
function upload_url(?string $file, string $sub = 'products'): string
{
    global $CONFIG;
    if (!$file) return '';
    return rtrim($CONFIG['base_url'], '/') . '/' . $CONFIG['upload_dir'] . '/' . $sub . '/' . rawurlencode($file);
}

/**
 * Save one uploaded film file (a clip or its still) into uploads/video.
 * $kind is "video" (MP4) or "image" (JPG or WebP).
 * Returns the new file name, or null if nothing was uploaded. Problems
 * are added to $notes in plain words.
 */
function save_film_upload(string $field, string $kind, string $prefix, array &$notes): ?string
{
    global $CONFIG;
    if (empty($_FILES[$field]['name'])) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) { $notes[] = 'The upload did not finish. The file may be too large for this server.'; return null; }

    $dir = WEB_ROOT . '/' . $CONFIG['upload_dir'] . '/video';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
    $max  = (int)($CONFIG['max_upload_mb'] ?? 8);

    if ($kind === 'video') {
        if ($mime !== 'video/mp4') { $notes[] = 'The clip must be an MP4 file.'; return null; }
        if ($_FILES[$field]['size'] > $max * 1024 * 1024) { $notes[] = "That clip is over $max MB. Please compress it first."; return null; }
        $ext = 'mp4';
    } else {
        $ok = ['image/jpeg' => 'jpg', 'image/webp' => 'webp'];
        if (!isset($ok[$mime])) { $notes[] = 'The picture must be a JPG or WebP.'; return null; }
        $ext = $ok[$mime];
    }
    $name = $prefix . '-' . time() . '.' . $ext;
    return move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $name) ? $name : null;
}

/* ---------- a token that proves a form came from our own page ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)$sent)) {
        http_response_code(400);
        exit('This form expired. Go back, reload the page and try again.');
    }
}

/* ---------- who is logged in ---------- */

function current_user(): ?array
{
    static $u = null;
    if ($u !== null) return $u ?: null;
    $id = $_SESSION['uid'] ?? null;
    if (!$id) { $u = false; return null; }
    $u = q1('SELECT * FROM users WHERE id = ? AND is_active = 1', [$id]) ?: false;
    return $u ?: null;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        header('Location: login.php');
        exit;
    }
    return $u;
}

function log_activity(string $what): void
{
    $u = current_user();
    run('INSERT INTO activity (user_id, what) VALUES (?, ?)', [$u['id'] ?? null, $what]);
}

/* ---------- small conveniences ---------- */

function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim($s, '-') ?: 'item';
}

function flash(?string $msg = null): ?string
{
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function money(float $n): string
{
    $num = number_format($n, 0);
    return lang() === 'bn' ? '৳' . digits($num) : 'Tk. ' . $num;
}
