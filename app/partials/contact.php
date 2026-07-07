<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<section class="sec contact-sec ambient" id="contact" aria-label="Contact">
  <div class="container-line sec-inner">
    <div class="sec-head">
      <div>
        <p class="mono-label sec-label" data-reveal>
          <span class="accent">006</span><span class="rule" aria-hidden="true"></span>Contact
        </p>
      </div>
    </div>

    <h2 class="c-statement"><?= reveal_words("Let's build something") ?> <span class="accent-word"><?= reveal_words('real.') ?></span></h2>

    <div class="c-grid">
      <div class="c-email-block" data-reveal>
        <p class="mono-label">Direct line</p>
        <button type="button" class="copy-email" id="copy-email" data-email="<?= e($identity['email']) ?>" data-cursor>
          <?= e($identity['email']) ?>
          <span class="ce-ic ic-copy"><?= icon('copy', 20) ?></span>
          <span class="ce-ic ic-check"><?= icon('check', 20) ?></span>
        </button>
        <p class="copy-state" id="copy-state" aria-live="polite">Copied to clipboard</p>

        <div class="c-socials">
          <a class="social-btn" href="<?= e($identity['linkedin']) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn profile" data-magnetic data-cursor>
            <?= icon('linkedin', 19) ?>
          </a>
          <?php if ($identity['github'] !== ''): ?>
            <a class="social-btn" href="<?= e($identity['github']) ?>" target="_blank" rel="noopener noreferrer" aria-label="GitHub profile" data-magnetic data-cursor>
              <?= icon('github', 19) ?>
            </a>
          <?php else: ?>
            <span class="social-btn" role="img" aria-label="GitHub — link coming soon" title="[ADD GITHUB PROFILE URL]">
              <?= icon('github', 19) ?>
            </span>
          <?php endif; ?>
          <a class="social-btn" href="mailto:<?= e($identity['email']) ?>" aria-label="Send an email" data-magnetic data-cursor>
            <?= icon('mail', 19) ?>
          </a>
        </div>

        <div class="c-meta">
          <p class="mono-label">Based in <?= e($identity['location']) ?></p>
          <p class="mono-label">Open to product engineering &amp; AI integration work</p>
        </div>
      </div>

      <form class="c-form" method="post" action="/contact.php" id="contact-form" data-reveal novalidate>
        <?php if ($flash !== null): ?>
          <p class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
        <?php endif; ?>
        <p class="flash" id="form-flash" role="status" hidden></p>

        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="fts" value="<?= e(form_timestamp_field()) ?>">
        <div class="hp-field" aria-hidden="true">
          <label for="hp-website">Leave this field empty</label>
          <input type="text" id="hp-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="field">
          <label for="cf-name">Name <span class="req" aria-hidden="true">*</span></label>
          <input type="text" id="cf-name" name="name" required minlength="2" maxlength="80" autocomplete="name" placeholder="What should I call you?">
        </div>
        <div class="field">
          <label for="cf-email">Email <span class="req" aria-hidden="true">*</span></label>
          <input type="email" id="cf-email" name="email" required maxlength="120" autocomplete="email" placeholder="you@company.com">
        </div>
        <div class="field">
          <label for="cf-message">Message <span class="req" aria-hidden="true">*</span></label>
          <textarea id="cf-message" name="message" required minlength="10" maxlength="3000" placeholder="A project, a role, an idea — what are we building?"></textarea>
        </div>
        <div class="form-foot">
          <button type="submit" class="btn btn--primary" data-magnetic data-cursor>
            Send message
            <span class="btn-ic btn-ic--diag"><?= icon('send', 15) ?></span>
          </button>
          <p class="form-note">Protected against spam — no trackers, nothing stored beyond your message.</p>
        </div>
      </form>
    </div>
  </div>
</section>
