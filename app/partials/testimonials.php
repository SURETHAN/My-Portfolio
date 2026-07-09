<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<?php if (empty($testimonials)) { return; } // hidden until real feedback arrives ?>
<section class="sec" id="signals" aria-label="Testimonials">
  <div class="container-line sec-inner">
    <div class="sec-head">
      <div>
        <p class="mono-label sec-label" data-reveal>
          Endorsements<span class="rule" aria-hidden="true"></span>
        </p>
        <h2 class="sec-title"><?= reveal_words('Word from the people I ship with.') ?></h2>
      </div>
      <p class="sec-aside" data-reveal>
        Collected directly from customers and colleagues — published exactly as written.
      </p>
    </div>

    <div class="quotes-grid" aria-label="Testimonial cards">
      <?php foreach ($testimonials as $t): ?>
        <blockquote class="q-card" data-reveal>
          <span class="q-mark" aria-hidden="true">&ldquo;</span>
          <p class="q-text"><?= e($t['quote']) ?></p>
          <footer class="q-who">
            <cite class="q-name"><?= e($t['name']) ?></cite>
            <?php if (($t['role'] ?? '') !== ''): ?>
              <span class="q-role"><?= e($t['role']) ?></span>
            <?php endif; ?>
          </footer>
        </blockquote>
      <?php endforeach; ?>
    </div>
  </div>
</section>
