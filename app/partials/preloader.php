<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<div class="preloader" id="preloader" role="status" aria-label="Loading portfolio" hidden>
  <p class="pl-name" aria-hidden="true"><?= reveal_chars($identity['name'], 'pl-ch') ?></p>
  <div class="pl-meta">
    <div class="pl-meta-row">
      <span class="mono-label">Loading environment</span>
      <span class="pl-count" id="pl-count">000</span>
    </div>
    <div class="pl-bar"><div class="pl-bar-fill" id="pl-bar"></div></div>
  </div>
</div>
