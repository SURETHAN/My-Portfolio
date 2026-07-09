<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<section class="sec" id="experience" aria-label="Experience and education">
  <div class="container-line sec-inner">
    <div class="sec-head">
      <div>
        <p class="mono-label sec-label" data-reveal>
          Journey<span class="rule" aria-hidden="true"></span>
        </p>
        <h2 class="sec-title"><?= reveal_words('Student to production owner, fast.') ?></h2>
      </div>
      <p class="sec-aside" data-reveal>
        No previous jobs to pad this out — just the shortest honest path from classroom to shipping code with real users.
      </p>
    </div>

    <div class="tl" id="timeline">
      <div class="tl-spine" aria-hidden="true"></div>
      <div class="tl-spine-fill" id="tl-fill" aria-hidden="true"></div>
      <ol class="tl-list">
        <?php foreach ($timeline as $entry): ?>
          <?php
            $isCurrent  = $entry['tag'] === 'CURRENT';
            $isInflect  = $entry['tag'] === 'INFLECTION';
            $nodeClass  = $isCurrent ? ' is-current' : ($isInflect ? ' is-inflect' : '');
          ?>
          <li class="tl-item">
            <span class="tl-node<?= $nodeClass ?>" aria-hidden="true"><i></i></span>
            <div data-reveal>
              <p class="tl-meta">
                <span class="tl-period"><?= e($entry['period']) ?></span>
                <?php if ($entry['tag'] !== ''): ?>
                  <span class="tl-tag <?= $isCurrent ? 'tl-tag--current' : 'tl-tag--inflect' ?>"><?= e($entry['tag']) ?></span>
                <?php endif; ?>
              </p>
              <h3 class="tl-title"><?= e($entry['title']) ?></h3>
              <p class="tl-org">
                <?= e($entry['org']) ?><?php if ($entry['place'] !== ''): ?><span class="place"> · <?= e($entry['place']) ?></span><?php endif; ?>
              </p>
              <p class="tl-body"><?= e($entry['body']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>
