<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<section class="sec" id="skills" aria-label="Skills">
  <div class="container-line sec-inner">
    <div class="sec-head">
      <div>
        <p class="mono-label sec-label" data-reveal>
          <span class="accent">002</span><span class="rule" aria-hidden="true"></span>Stack
        </p>
        <h2 class="sec-title"><?= reveal_words('Tools that earn their place.') ?></h2>
      </div>
      <p class="sec-aside" data-reveal>
        Grouped by what they're for — not a wall of logos. The first two groups are where most of the production mileage is.
      </p>
    </div>

    <div class="skills-grid">
      <?php foreach ($skillGroups as $group): ?>
        <div class="tilt" data-tilt data-reveal>
          <article class="skill-card">
            <div class="sc-top">
              <span class="sc-index">GRP<span class="accent">/<?= e($group['index']) ?></span></span>
              <?php if ($group['featured']): ?><span class="sc-badge">Core</span><?php endif; ?>
            </div>
            <h3 class="sc-title"><?= e($group['title']) ?></h3>
            <p class="sc-blurb"><?= e($group['blurb']) ?></p>
            <ul class="chiplist">
              <?php foreach ($group['items'] as $item): ?>
                <li class="chip"><?= e($item) ?></li>
              <?php endforeach; ?>
            </ul>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="tape" aria-label="Technology ticker">
    <div class="marquee marquee--fade marquee--hoverpause">
      <div class="marquee-track">
        <?php for ($seg = 0; $seg < 2; $seg++): ?>
          <div class="marquee-seg"<?= $seg === 1 ? ' aria-hidden="true"' : '' ?>>
            <?php foreach ($techMarquee as $tech): ?>
              <span class="tape-item"><?= e($tech) ?><span class="tape-dot" aria-hidden="true"></span></span>
            <?php endforeach; ?>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>
