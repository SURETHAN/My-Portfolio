<?php

declare(strict_types=1);

if (!defined('APP_BOOT')) {
    http_response_code(403);
    exit('Forbidden');
}

/* ------------------------------------------------------------------ */
/*  Security bootstrap: headers, session, CSP nonce, secrets           */
/* ------------------------------------------------------------------ */

/** Per-request CSP nonce. */
function csp_nonce(): string
{
    static $nonce = null;
    if ($nonce === null) {
        $nonce = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
    }
    return $nonce;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

/** Hardened session (cookie only used for CSRF + flash messages). */
function boot_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('snsess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax', // Lax (not Strict) so the CSRF cookie survives arrival via external links
    ]);
    session_start();
}

/** Full security-header set. Call before any output. */
function send_security_headers(): void
{
    $nonce = csp_nonce();

    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'nonce-{$nonce}'",
        "style-src 'self'",
        "img-src 'self' data:",
        "font-src 'self'",
        "connect-src 'self'",
        "object-src 'none'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ]);

    header('Content-Type: text/html; charset=UTF-8');
    header("Content-Security-Policy: {$csp}");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header_remove('X-Powered-By');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/** Per-install secret for HMACs (auto-generated, stored outside docroot). */
function app_secret(): string
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }
    $file = VAR_DIR . '/secret.key';
    if (!is_dir(VAR_DIR)) {
        mkdir(VAR_DIR, 0750, true);
    }
    if (is_file($file)) {
        $secret = (string) file_get_contents($file);
        if ($secret !== '') {
            return $secret;
        }
    }
    $secret = bin2hex(random_bytes(32));
    file_put_contents($file, $secret, LOCK_EX);
    @chmod($file, 0600);
    return $secret;
}
