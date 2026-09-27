<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; }

$flagships = array_values(array_filter($projects, fn ($p) => $p['flagship']));
$others    = array_values(array_filter($projects, fn ($p) => !$p['flagship']));
?>
<section class="sec" id="work" aria-label="Projects">
  <div class="container-line sec-inner">
    <div class="sec-head">
      <div>
        <p class="mono-label sec-label" data-reveal>
          Selected work<span class="rule" aria-hidden="true"></span>
        </p>
        <h2 class="sec-title"><?= reveal_words('Proof, running in production.') ?></h2>
      </div>
      <p class="sec-aside" data-reveal>
        Everything here is deployed and in daily use — at Selfmade Ninja Academy and Sponge Collaborative. The work repos are private, so each case stands on its architecture and outcomes.
      </p>
    </div>

    <div class="flagships">
      <?php foreach ($flagships as $project): ?>
        <div data-tilt data-tilt-max="1.2" data-reveal>
          <article class="fs-panel" aria-labelledby="fs-<?= e($project['id']) ?>">
            <span class="fs-bignum" aria-hidden="true"><?= e(ltrim($project['index'], '0')) ?></span>
            <div class="fs-grid">
              <div>
                <p class="fs-meta">
                  <span class="fs-index">Case study</span>
                  <?php if ($project['liveLabel'] !== ''): ?>
                    <span class="fs-live"><span class="live-dot" aria-hidden="true"></span><?= e($project['liveLabel']) ?></span>
                  <?php endif; ?>
                </p>
                <h3 class="fs-name" id="fs-<?= e($project['id']) ?>"><?= e($project['name']) ?></h3>
                <p class="fs-kicker"><?= e($project['kicker']) ?></p>
                <p class="fs-sum"><?= e($project['summary']) ?></p>
                <p class="fs-role"><?= e($project['role']) ?></p>
                <ul class="fs-impact">
                  <?php foreach ($project['impact'] as $point): ?>
                    <li><?= e($point) ?></li>
                  <?php endforeach; ?>
                </ul>
                <ul class="fs-tech" aria-label="Technology used">
                  <?php foreach ($project['tech'] as $tech): ?>
                    <li class="chip"><?= e($tech) ?></li>
                  <?php endforeach; ?>
                </ul>
                <?php if ($project['liveUrl'] !== ''): ?>
                  <div class="fs-linkrow">
                    <a class="btn btn--ghost" href="<?= e($project['liveUrl']) ?>" target="_blank" rel="noopener noreferrer" data-magnetic data-cursor>
                      Visit live
                      <span class="btn-ic btn-ic--diag"><?= icon('diag', 15) ?></span>
                    </a>
                  </div>
                <?php endif; ?>
              </div>

              <?php if ($project['architecture'] !== []): ?>
                <div class="fs-arch">
                  <p class="mono-label arch-title">Architecture — signal path</p>
                  <?php foreach ($project['architecture'] as $step): ?>
                    <div class="arch-step" data-reveal>
                      <p class="arch-k"><?= e($step['label']) ?></p>
                      <p class="arch-d"><?= e($step['detail']) ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="bento">
      <?php foreach ($others as $project): ?>
        <div class="b-cell <?= $project['span'] === 'wide' ? 'b-cell--wide' : 'b-cell--std' ?>" data-reveal>
          <div class="tilt" data-tilt>
            <article class="b-card" aria-labelledby="b-<?= e($project['id']) ?>">
              <div class="b-top">
                <span class="b-index"><?= e($project['liveLabel'] !== '' ? 'In production' : 'Case study') ?></span>
                <?php if ($project['liveUrl'] !== ''): ?>
                  <a class="b-arrow" href="<?= e($project['liveUrl']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Open <?= e($project['name']) ?> live site" data-cursor><?= icon('diag') ?></a>
                <?php else: ?>
                  <span class="b-arrow" aria-hidden="true"><?= icon('diag') ?></span>
                <?php endif; ?>
              </div>
              <h3 class="b-name" id="b-<?= e($project['id']) ?>"><?= e($project['name']) ?></h3>
              <p class="b-kicker"><?= e($project['kicker']) ?></p>
              <p class="b-sum"><?= e($project['summary']) ?></p>
              <?php if ($project['liveLabel'] !== ''): ?>
                <p class="b-live fs-live"><span class="live-dot" aria-hidden="true"></span><?= e($project['liveLabel']) ?></p>
              <?php endif; ?>
              <ul class="b-tech" aria-label="Technology used">
                <?php foreach (array_slice($project['tech'], 0, 5) as $tech): ?>
                  <li class="chip"><?= e($tech) ?></li>
                <?php endforeach; ?>
              </ul>
            </article>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
