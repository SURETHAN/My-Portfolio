<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<header class="site-nav" id="site-nav">
  <nav class="nav-pill glass" aria-label="Primary">
    <a href="#top" class="nav-logo" data-scroll data-cursor aria-label="<?= e($identity['name']) ?> — back to top">
      S<em>/</em>
    </a>

    <ul class="nav-links" id="nav-links">
      <span class="nav-ind" id="nav-ind" aria-hidden="true"></span>
      <?php foreach ($navLinks as $link): ?>
        <li>
          <a class="nav-link" href="<?= e($link['href']) ?>" data-scroll data-cursor><?= e($link['label']) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="nav-actions">
      <button type="button" class="icon-btn theme-toggle" id="theme-toggle" data-cursor aria-label="Toggle color theme">
        <span class="tt-ic tt-moon"><?= icon('moon') ?></span>
        <span class="tt-ic tt-sun"><?= icon('sun') ?></span>
      </button>
      <button type="button" class="icon-btn menu-btn" id="menu-open" data-cursor aria-label="Open menu" aria-expanded="false" aria-controls="mobile-menu">
        <?= icon('menu', 17) ?>
      </button>
    </div>
  </nav>
</header>

<div class="mobile-menu" id="mobile-menu" role="dialog" aria-modal="true" aria-label="Menu">
  <div class="mm-top">
    <span class="nav-logo" aria-hidden="true">S<em>/</em></span>
    <button type="button" class="mm-close" id="menu-close" aria-label="Close menu"><?= icon('x', 18) ?></button>
  </div>
  <ul class="mm-list">
    <?php foreach ($navLinks as $i => $link): ?>
      <li class="mm-link-wrap">
        <a class="mm-link" href="<?= e($link['href']) ?>" data-scroll>
          <span class="mm-num">0<?= $i + 1 ?></span>
          <?= e($link['label']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <p class="mm-foot mono-label"><?= e($identity['location']) ?></p>
</div>
