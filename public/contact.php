<?php

/**
 * Contact form endpoint.
 * Defenses: same-origin check, CSRF token, honeypot, signed time-trap,
 * per-IP rate limit, strict validation, header-injection-proof delivery.
 * Progressive enhancement: JSON for fetch() clients, PRG redirect otherwise.
 */

declare(strict_types=1);

define('APP_BOOT', true);

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/security.php';
require __DIR__ . '/../app/helpers.php';

boot_session();

$wantsJson = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

function respond(bool $ok, string $message, int $status = 200): never
{
    global $wantsJson;
    if ($wantsJson) {
        http_response_code($ok ? $status : max($status, 400));
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        flash_set($ok ? 'success' : 'error', $message);
        header('Location: /#contact', true, 303);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(false, 'Method not allowed.', 405);
}

/* Same-origin check (defense-in-depth next to the CSRF token) */
$host   = $_SERVER['HTTP_HOST'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
if ($origin !== '' && $host !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    if ($originHost !== null && !hash_equals(strtolower($host), strtolower((string) $originHost . (parse_url($origin, PHP_URL_PORT) ? ':' . parse_url($origin, PHP_URL_PORT) : '')))
        && !hash_equals(strtolower(explode(':', $host)[0]), strtolower((string) $originHost))) {
        respond(false, 'Request rejected.', 403);
    }
}

if (!csrf_validate($_POST['csrf'] ?? null)) {
    respond(false, 'Your session expired — reload the page and try again.', 403);
}

/* Honeypot: real users never see or fill this field */
if (($_POST['website'] ?? '') !== '') {
    // Pretend success so bots learn nothing
    respond(true, "Thanks — I'll get back to you soon.");
}

/* Signed time-trap: form must exist ≥ FORM_MIN_SECONDS before submit */
if (!form_timestamp_valid($_POST['fts'] ?? null)) {
    respond(false, 'That was a bit too quick — please try sending again.', 400);
}

if (rate_limit_exceeded('contact')) {
    respond(false, 'Too many messages from your network — please try again later.', 429);
}

/* ---- Validation ---- */
$name    = sanitize_line((string) ($_POST['name'] ?? ''), 80);
$email   = sanitize_line((string) ($_POST['email'] ?? ''), 120);
$message = sanitize_block((string) ($_POST['message'] ?? ''), 3000);

if (text_len($name) < 2) {
    respond(false, 'Please tell me your name (2+ characters).', 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, "That email address doesn't look valid.", 422);
}
if (text_len($message) < 10) {
    respond(false, 'Please write a few words more (10+ characters).', 422);
}

/* ---- Delivery ---- */
$delivered = false;

if (CONTACT_TRANSPORT === 'mail') {
    $subject = '[Portfolio] Message from ' . $name;
    $body    = "Name: {$name}\nEmail: {$email}\nTime: " . gmdate('c') . "\nIP hash: "
        . substr(hash_hmac('sha256', client_ip(), app_secret()), 0, 16)
        . "\n\n" . $message . "\n";
    $headers = [
        'From: ' . SITE_NAME . ' <no-reply@' . (explode(':', $host)[0] ?: 'localhost') . '>',
        'Reply-To: ' . $email, // validated above; sanitize_line stripped CR/LF
        'X-Mailer: portfolio-contact',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    $delivered = @mail(CONTACT_TO, $subject, $body, implode("\r\n", $headers));
} else {
    if (!is_dir(VAR_DIR)) {
        mkdir(VAR_DIR, 0750, true);
    }
    $line = json_encode([
        'ts'      => gmdate('c'),
        'name'    => $name,
        'email'   => $email,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE) . "\n";
    $delivered = (bool) file_put_contents(VAR_DIR . '/messages.log', $line, FILE_APPEND | LOCK_EX);
}

if (!$delivered) {
    respond(false, 'Could not send right now — email me directly instead.', 500);
}

respond(true, "Thanks — your message is in. I'll get back to you soon.");
