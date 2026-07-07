<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<section class="sec" id="about" aria-label="About">
  <div class="container-line sec-inner">
    <div class="sec-head">
      <div>
        <p class="mono-label sec-label" data-reveal>
          <span class="accent">001</span><span class="rule" aria-hidden="true"></span>Story
        </p>
        <h2 class="sec-title"><?= reveal_words($about['heading']) ?></h2>
      </div>
      <p class="sec-aside" data-reveal><?= e($about['aside']) ?></p>
    </div>

    <div class="about-grid">
      <div class="portrait-wrap" data-reveal>
        <figure class="portrait" data-cursor>
          <div class="portrait-inner" id="portrait-parallax">
            <img
              class="portrait-img"
              src="<?= e(asset('assets/images/portrait.webp')) ?>"
              alt="Surethan S standing in a misty field at sunrise, the sun rising directly behind him"
              width="880" height="1100" loading="lazy" decoding="async">
          </div>
          <div class="portrait-grade" aria-hidden="true"></div>
          <div class="portrait-tint" aria-hidden="true"></div>
          <figcaption class="portrait-cap">
            <span class="c1"><?= e($identity['name']) ?> — early hours</span>
            <span class="c2">EXIF · 06:1X AM</span>
          </figcaption>
        </figure>
        <div class="fact-chip glass" data-reveal>
          <p class="fc-k">Currently</p>
          <p class="fc-v">Product Dev <span class="accent">@</span> Selfmade Ninja</p>
        </div>
      </div>

      <div class="about-copy">
        <div>
          <?php foreach ($about['paragraphs'] as $i => $para): ?>
            <p class="para<?= $i === 0 ? ' para--lead' : '' ?>" data-reveal><?= e($para) ?></p>
          <?php endforeach; ?>
        </div>

        <dl class="stats">
          <?php foreach ($about['stats'] as $stat): ?>
            <div class="stat" data-reveal>
              <dd class="stat-num">
                <span data-counter="<?= e((string) $stat['value']) ?>" data-suffix="<?= e($stat['suffix']) ?>"><?= e((string) $stat['value'] . $stat['suffix']) ?></span>
              </dd>
              <dt class="stat-label"><?= e($stat['label']) ?></dt>
            </div>
          <?php endforeach; ?>
        </dl>
      </div>
    </div>
  </div>
</section>
