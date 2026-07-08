<?php

declare(strict_types=1);

if (!defined('APP_BOOT')) {
    http_response_code(403);
    exit('Forbidden');
}

/* ------------------------------------------------------------------ */
/*  Site configuration                                                 */
/* ------------------------------------------------------------------ */

const SITE_NAME   = 'Surethan S';
const SITE_TITLE  = 'Surethan S — Product Developer · AI Integrations';
const SITE_DESC   = 'Product developer at Selfmade Ninja Academy, Bengaluru. Ships production AI end-to-end: an autonomous voice agent that qualifies every sales lead, and a 5-agent LLM pipeline turning university syllabi into learning platforms.';
const SITE_URL    = 'https://surethan.zeal.ninja';
const CONTACT_TO  = 'surethan37@gmail.com';

/* Envelope/header sender — must match the Gmail account msmtp authenticates as */
const CONTACT_FROM = 'surethan37@gmail.com';

/**
 * How the contact form delivers messages:
 *  - 'mail' : PHP mail() → msmtp → Gmail SMTP (see Dockerfile; needs $SMTP_PASS)
 *  - 'log'  : append to var/messages.log only (safe default everywhere)
 * Note: every message is ALWAYS written to the log as a durable backup,
 * regardless of this setting — see public/contact.php.
 */
const CONTACT_TRANSPORT = 'mail';

/* Rate limit: max submissions per IP per window (seconds) */
const RATE_LIMIT_MAX    = 5;
const RATE_LIMIT_WINDOW = 3600;

/* Form must take at least this many seconds to fill (bot time-trap) */
const FORM_MIN_SECONDS = 3;
const FORM_MAX_SECONDS = 7200;

/* Writable state directory (rate-limit counters, secret key, message log) */
define('VAR_DIR', dirname(__DIR__) . '/var');
