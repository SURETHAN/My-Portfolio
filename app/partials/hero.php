<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<section class="hero ambient" id="top" aria-label="Introduction">
  <div class="hero-gridlines" aria-hidden="true"></div>

  <div class="container-line hero-main" id="hero-main">
    <p class="hero-status glass fx-fade" data-hero="chip">
      <span class="live-dot" aria-hidden="true"></span>
      Shipping at <?= e($identity['company']) ?> — <?= e($identity['location']) ?>
    </p>

    <h1 class="hero-name" id="hero-name">
      <span class="visually-hidden"><?= e($identity['name']) ?></span>
      <span class="hn-mask" aria-hidden="true"><?= reveal_chars($identity['nameMain']) ?><span class="hn-ch hn-tail text-outline"><?= e($identity['nameTail']) ?></span></span>
    </h1>

    <p class="hero-role fx-fade" data-hero="role">
      <span class="visually-hidden">Roles: <?= e(implode(', ', $identity['roles'])) ?></span>
      <span class="role-mask" aria-hidden="true"><span class="role-word" id="role-word"><?= e($identity['roles'][0]) ?></span></span>
    </p>

    <p class="hero-sub fx-fade" data-hero="sub">
      <strong><?= e($identity['tagline']) ?></strong>
      <?= e($identity['subline']) ?>
    </p>

    <div class="hero-ctas fx-fade" data-hero="ctas">
      <a class="btn btn--primary" href="#work" data-scroll data-magnetic data-cursor>
        View work
        <span class="btn-ic btn-ic--down"><?= icon('arrow-down', 15) ?></span>
      </a>
      <a class="btn btn--ghost" href="#contact" data-scroll data-magnetic data-cursor>
        Contact
        <span class="btn-ic btn-ic--diag"><?= icon('diag', 15) ?></span>
      </a>
    </div>
  </div>

  <div class="container-line hero-rail fx-fade" data-hero="rail">
    <button type="button" class="scroll-cue" data-scroll-to="#about" data-cursor aria-label="Scroll to about section">
      <span class="scroll-cue-line" aria-hidden="true"><span class="scroll-cue-dot"></span></span>
      <span class="mono-label">Scroll</span>
    </button>
    <p class="mono-label hero-coords">
      <?= e($identity['coords']) ?><span class="sep" aria-hidden="true">·</span>BLR
    </p>
  </div>
</section>
