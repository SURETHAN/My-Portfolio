<?php

declare(strict_types=1);

define('APP_BOOT', true);

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/security.php';
require __DIR__ . '/../app/helpers.php';

boot_session();
send_security_headers();

$DATA  = require __DIR__ . '/../app/data.php';
$nonce = csp_nonce();
$flash = flash_take();

$identity     = $DATA['identity'];
$about        = $DATA['about'];
$skillGroups  = $DATA['skillGroups'];
$techMarquee  = $DATA['techMarquee'];
$timeline     = $DATA['timeline'];
$projects     = $DATA['projects'];
$testimonials = $DATA['testimonials'];
$navLinks     = $DATA['navLinks'];

$partials = __DIR__ . '/../app/partials';

require $partials . '/head.php';
require $partials . '/preloader.php';
require $partials . '/nav.php';
?>
<main id="main">
<?php
require $partials . '/hero.php';
require $partials . '/about.php';
require $partials . '/skills.php';
require $partials . '/experience.php';
require $partials . '/projects.php';
require $partials . '/testimonials.php';
require $partials . '/contact.php';
?>
</main>
<?php
require $partials . '/footer.php';
