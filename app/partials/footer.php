<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<footer class="site-footer">
  <div class="container-line f-inner">
    <p class="f-note">&copy; <?= e(date('Y')) ?> <?= e($identity['name']) ?> — designed &amp; built by <span class="heart"><?= e($identity['name']) ?></span></p>
    <p class="f-clock" id="f-clock">
      <span class="tz">BLR</span>
      <span id="f-time" data-tz="Asia/Kolkata">--:--:--</span>
      <span>IST</span>
    </p>
    <button type="button" class="f-top" data-scroll-to="#top" data-cursor>
      Back to top <?= icon('arrow-up', 14) ?>
    </button>
  </div>
</footer>

<div class="cursor-layer" aria-hidden="true">
  <div class="cursor-dot" id="cursor-dot"></div>
  <div class="cursor-ring" id="cursor-ring"></div>
</div>

<script src="<?= e(asset('assets/vendor/gsap.min.js')) ?>" defer></script>
<script src="<?= e(asset('assets/vendor/ScrollTrigger.min.js')) ?>" defer></script>
<script src="<?= e(asset('assets/vendor/lenis.min.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/app.js')) ?>" type="module"></script>
</body>
</html>
