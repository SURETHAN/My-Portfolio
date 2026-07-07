<?php

declare(strict_types=1);

if (!defined('APP_BOOT')) {
    http_response_code(403);
    exit('Forbidden');
}

/* ------------------------------------------------------------------ */
/*  Output escaping — every dynamic value goes through e()             */
/* ------------------------------------------------------------------ */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ------------------------------------------------------------------ */
/*  CSRF                                                               */
/* ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_validate(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}

/* ------------------------------------------------------------------ */
/*  Bot time-trap: signed timestamp field                              */
/* ------------------------------------------------------------------ */

function form_timestamp_field(): string
{
    $ts  = (string) time();
    $sig = hash_hmac('sha256', $ts, app_secret());
    return $ts . '.' . $sig;
}

function form_timestamp_valid(?string $value): bool
{
    if (!is_string($value) || !str_contains($value, '.')) {
        return false;
    }
    [$ts, $sig] = explode('.', $value, 2);
    if (!ctype_digit($ts) || !hash_equals(hash_hmac('sha256', $ts, app_secret()), $sig)) {
        return false;
    }
    $age = time() - (int) $ts;
    return $age >= FORM_MIN_SECONDS && $age <= FORM_MAX_SECONDS;
}

/* ------------------------------------------------------------------ */
/*  Rate limiting (file-based, IP hashed — no raw IPs stored)          */
/* ------------------------------------------------------------------ */

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function rate_limit_exceeded(string $bucket): bool
{
    $dir = VAR_DIR . '/ratelimit';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $key  = hash_hmac('sha256', $bucket . '|' . client_ip(), app_secret());
    $file = $dir . '/' . $key . '.json';
    $now  = time();

    $state = ['start' => $now, 'count' => 0];
    if (is_file($file)) {
        $decoded = json_decode((string) file_get_contents($file), true);
        if (is_array($decoded) && isset($decoded['start'], $decoded['count'])) {
            $state = $decoded;
        }
    }
    if ($now - (int) $state['start'] > RATE_LIMIT_WINDOW) {
        $state = ['start' => $now, 'count' => 0];
    }
    $state['count']++;
    file_put_contents($file, json_encode($state), LOCK_EX);

    return $state['count'] > RATE_LIMIT_MAX;
}

/* ------------------------------------------------------------------ */
/*  Flash messages (PRG pattern for the no-JS form fallback)           */
/* ------------------------------------------------------------------ */

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type:string,message:string}|null */
function flash_take(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

/* ------------------------------------------------------------------ */
/*  Mail-safety: kill header injection, bound lengths                  */
/* ------------------------------------------------------------------ */

/** mbstring-safe length (php-mbstring may be absent on minimal hosts). */
function text_len(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function sanitize_line(string $value, int $maxLen): string
{
    $value = str_replace(["\r", "\n", "\0", "%0a", "%0d"], ' ', $value);
    $value = trim($value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLen);
    }
    return substr($value, 0, $maxLen);
}

function sanitize_block(string $value, int $maxLen): string
{
    $value = str_replace(["\0"], '', $value);
    $value = trim($value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLen);
    }
    return substr($value, 0, $maxLen);
}

/* ------------------------------------------------------------------ */
/*  View helpers: server-side split for masked reveals (zero CLS)      */
/* ------------------------------------------------------------------ */

/** Each word inside its own overflow mask — animated by JS, intact without it. */
function reveal_words(string $text): string
{
    $out = [];
    foreach (preg_split('/\s+/u', trim($text)) ?: [] as $word) {
        $out[] = '<span class="rw-m"><span class="rw-w">' . e($word) . '</span></span>';
    }
    return implode(' ', $out);
}

/** Per-character spans for kinetic headlines. */
function reveal_chars(string $text, string $charClass = 'hn-ch'): string
{
    $out = '';
    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    foreach ($chars as $ch) {
        $out .= '<span class="' . e($charClass) . '">' . e($ch) . '</span>';
    }
    return $out;
}

/* ------------------------------------------------------------------ */
/*  Inline SVG icons (Lucide outlines — consistent 1.75 stroke)        */
/* ------------------------------------------------------------------ */

function icon(string $name, int $size = 16, string $class = ''): string
{
    static $paths = [
        'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
        'moon'       => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        'menu'       => '<path d="M4 12h16M4 6h16M4 18h16"/>',
        'x'          => '<path d="M18 6 6 18M6 6l12 12"/>',
        'arrow-down' => '<path d="M12 5v14M19 12l-7 7-7-7"/>',
        'arrow-up'   => '<path d="M12 19V5M5 12l7-7 7 7"/>',
        'diag'       => '<path d="M7 7h10v10M7 17 17 7"/>',
        'copy'       => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'check'      => '<path d="M20 6 9 17l-5-5"/>',
        'mail'       => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'linkedin'   => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
        'github'     => '<path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/>',
        'send'       => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
    ];
    $body = $paths[$name] ?? '';
    $cls  = $class !== '' ? ' class="' . e($class) . '"' : '';
    return '<svg' . $cls . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

/* ------------------------------------------------------------------ */
/*  Asset cache-busting                                                */
/* ------------------------------------------------------------------ */

function asset(string $path): string
{
    $file = dirname(__DIR__) . '/public/' . ltrim($path, '/');
    $v    = is_file($file) ? (string) filemtime($file) : '1';
    return '/' . ltrim($path, '/') . '?v=' . $v;
}
