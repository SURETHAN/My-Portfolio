<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<section class="sec" id="signals" aria-label="Testimonials">
  <div class="container-line sec-inner">
    <div class="sec-head">
      <div>
        <p class="mono-label sec-label" data-reveal>
          <span class="accent">005</span><span class="rule" aria-hidden="true"></span>Signals
        </p>
        <h2 class="sec-title"><?= reveal_words('Word from the people I ship with.') ?></h2>
      </div>
      <p class="sec-aside" data-reveal>
        Real quotes land here soon — the cards below are marked placeholders, not fabricated praise.
      </p>
    </div>
  </div>

  <div class="quotes-strip" aria-label="Testimonial cards">
    <div class="marquee marquee--fade marquee--hoverpause">
      <div class="marquee-track">
        <?php for ($seg = 0; $seg < 2; $seg++): ?>
          <div class="marquee-seg"<?= $seg === 1 ? ' aria-hidden="true"' : '' ?>>
            <?php foreach ($testimonials as $t): ?>
              <blockquote class="q-card">
                <span class="q-mark" aria-hidden="true">&ldquo;</span>
                <p class="q-text"><?= e($t['quote']) ?></p>
                <footer class="q-who">
                  <cite class="q-name"><?= e($t['name']) ?></cite>
                  <span class="q-role"><?= e($t['role']) ?></span>
                </footer>
              </blockquote>
            <?php endforeach; ?>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>
