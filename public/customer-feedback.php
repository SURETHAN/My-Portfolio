<?php

/**
 * Private customer-feedback form.
 *
 * Deliberately unlinked from the site — the URL is shared 1:1 with a customer.
 * Only email addresses listed in content/feedback-access.json may submit,
 * and each address may submit exactly once. Approved submissions are stored
 * in var/testimonials.json and appear as cards in the Endorsements section.
 *
 * Same defenses as the contact endpoint: same-origin check, CSRF token,
 * honeypot, signed time-trap, per-IP rate limit, strict validation.
 */

declare(strict_types=1);

define('APP_BOOT', true);

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/security.php';
require __DIR__ . '/../app/helpers.php';

boot_session();
send_security_headers();
header('X-Robots-Tag: noindex, nofollow');

const FEEDBACK_STORE = VAR_DIR . '/testimonials.json';

/** Lowercased allowlist from content/feedback-access.json. */
function feedback_allowlist(): array
{
    $decoded = json_decode((string) @file_get_contents(CONTENT_DIR . '/feedback-access.json'), true);
    if (!is_array($decoded)) {
        error_log('[feedback] feedback-access.json missing or invalid');
        return [];
    }
    return array_values(array_filter(array_map(
        static fn($e) => is_string($e) ? strtolower(trim($e)) : null,
        $decoded
    )));
}

/** Stored submissions (array of assoc rows). */
function feedback_submissions(): array
{
    if (!is_file(FEEDBACK_STORE)) {
        return [];
    }
    $decoded = json_decode((string) file_get_contents(FEEDBACK_STORE), true);
    return is_array($decoded) ? $decoded : [];
}

function feedback_already_submitted(string $email): bool
{
    foreach (feedback_submissions() as $row) {
        if (strtolower((string) ($row['email'] ?? '')) === strtolower($email)) {
            return true;
        }
    }
    return false;
}

/** Append one submission under an exclusive lock. Returns success. */
function feedback_store(array $row): bool
{
    if (!is_dir(VAR_DIR)) {
        mkdir(VAR_DIR, 0750, true);
    }
    $fh = fopen(FEEDBACK_STORE, 'c+');
    if ($fh === false) {
        return false;
    }
    $ok = false;
    if (flock($fh, LOCK_EX)) {
        $raw  = stream_get_contents($fh);
        $list = json_decode((string) $raw, true);
        if (!is_array($list)) {
            $list = [];
        }
        $list[] = $row;
        $json = json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json !== false) {
            ftruncate($fh, 0);
            rewind($fh);
            $ok = fwrite($fh, $json . "\n") !== false;
            fflush($fh);
        }
        flock($fh, LOCK_UN);
    }
    fclose($fh);
    return $ok;
}

/* ------------------------------------------------------------------ */
/*  POST — handle a submission (PRG: always redirect back with flash)  */
/* ------------------------------------------------------------------ */

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $fail = static function (string $message): never {
        flash_set('error', $message);
        header('Location: /customer-feedback', true, 303);
        exit;
    };
    $succeed = static function (string $message): never {
        flash_set('success', $message);
        header('Location: /customer-feedback', true, 303);
        exit;
    };

    /* Same-origin check */
    $host   = $_SERVER['HTTP_HOST'] ?? '';
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    if ($origin !== '' && $host !== '') {
        $originHost = parse_url($origin, PHP_URL_HOST);
        if ($originHost !== null
            && !hash_equals(strtolower(explode(':', $host)[0]), strtolower((string) $originHost))) {
            $fail('Request rejected.');
        }
    }

    if (!csrf_validate($_POST['csrf'] ?? null)) {
        $fail('Your session expired — reload the page and try again.');
    }

    /* Honeypot: pretend success so bots learn nothing */
    if (($_POST['website'] ?? '') !== '') {
        $succeed('Thank you — your feedback is in.');
    }

    if (!form_timestamp_valid($_POST['fts'] ?? null)) {
        $fail('That was a bit too quick — please try again.');
    }

    if (rate_limit_exceeded('feedback')) {
        $fail('Too many attempts from your network — please try again later.');
    }

    /* ---- Validation ---- */
    $name  = sanitize_line((string) ($_POST['name'] ?? ''), 80);
    $email = sanitize_line((string) ($_POST['email'] ?? ''), 120);
    $role  = sanitize_line((string) ($_POST['role'] ?? ''), 100);
    $quote = sanitize_block((string) ($_POST['quote'] ?? ''), 800);

    if (text_len($name) < 2) {
        $fail('Please tell me your name (2+ characters).');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fail("That email address doesn't look valid.");
    }
    if (text_len($quote) < 20) {
        $fail('Please write a few words more (20+ characters).');
    }

    /* ---- Authorization: allowlist + one submission per address ---- */
    if (!in_array(strtolower($email), feedback_allowlist(), true)) {
        $fail('This email address is not authorized to submit feedback. Please use the address the form was sent to.');
    }
    if (feedback_already_submitted($email)) {
        $fail('Feedback from this email address has already been received — thank you!');
    }

    $stored = feedback_store([
        'quote' => $quote,
        'name'  => $name,
        'role'  => $role,
        'email' => strtolower($email),
        'ts'    => gmdate('c'),
    ]);
    if (!$stored) {
        $fail('Could not save your feedback right now — please try again in a moment.');
    }

    /* Best-effort notification to the site owner */
    if (CONTACT_TRANSPORT === 'mail') {
        $body = "New testimonial submitted.\n\nName: {$name}\nEmail: {$email}\nRole: {$role}\nTime: "
            . gmdate('c') . "\n\n{$quote}\n";
        @mail(
            CONTACT_TO,
            '[Portfolio] New testimonial from ' . $name,
            $body,
            implode("\r\n", [
                'From: ' . SITE_NAME . ' <' . CONTACT_FROM . '>',
                'Reply-To: ' . $email,
                'X-Mailer: portfolio-feedback',
                'Content-Type: text/plain; charset=UTF-8',
            ])
        );
    }

    $succeed('Thank you — your feedback is now live on the site.');
}

/* ------------------------------------------------------------------ */
/*  GET — render the form                                              */
/* ------------------------------------------------------------------ */

$flash = flash_take();
?>
<!doctype html>
<html lang="en" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Share your feedback — <?= e(SITE_NAME) ?></title>
<meta name="theme-color" content="#0a0a0b">
<link rel="icon" href="<?= e(asset('assets/images/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('assets/css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
</head>
<body>
<main class="fb-page">
  <div class="fb-card">
    <p class="mono-label">Customer feedback · <?= e(SITE_NAME) ?></p>
    <h1 class="fb-title">A few words about working with me.</h1>
    <p class="fb-sub">
      This form was shared with you directly. Your words appear as a card in the
      Endorsements section of <?= e(SITE_NAME) ?>'s portfolio — exactly as you write them.
    </p>

    <?php if ($flash !== null): ?>
      <p class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
    <?php endif; ?>

    <form class="c-form" method="post" action="/customer-feedback">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="fts" value="<?= e(form_timestamp_field()) ?>">
      <div class="hp-field" aria-hidden="true">
        <label for="hp-website">Leave this field empty</label>
        <input type="text" id="hp-website" name="website" tabindex="-1" autocomplete="off">
      </div>

      <div class="field">
        <label for="fb-name">Your name <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="fb-name" name="name" required minlength="2" maxlength="80" autocomplete="name" placeholder="How should you be credited?">
      </div>
      <div class="field">
        <label for="fb-email">Your email <span class="req" aria-hidden="true">*</span></label>
        <input type="email" id="fb-email" name="email" required maxlength="120" autocomplete="email" placeholder="The address this form was sent to">
      </div>
      <div class="field">
        <label for="fb-role">Role &amp; company</label>
        <input type="text" id="fb-role" name="role" maxlength="100" autocomplete="organization-title" placeholder="e.g. CTO · Acme Corp (optional)">
      </div>
      <div class="field">
        <label for="fb-quote">Your feedback <span class="req" aria-hidden="true">*</span></label>
        <textarea id="fb-quote" name="quote" required minlength="20" maxlength="800" placeholder="Two or three sentences about what we built together and how it went."></textarea>
      </div>
      <div class="form-foot">
        <button type="submit" class="btn btn--primary">Submit feedback</button>
        <p class="form-note">Only the email address this form was sent to can submit — one submission per person.</p>
      </div>
    </form>
  </div>
</main>
</body>
</html>
