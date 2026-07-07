# Surethan S — Portfolio

Cinematic dark-first portfolio for **Surethan S** — Product Developer · Frappe/ERPNext · AI Integrations.
Live at **[surethan.zeal.ninja](https://surethan.zeal.ninja)**.

Pure **PHP 8 + vanilla JS** — no frontend framework, no build step at runtime, no third-party
requests (fonts, GSAP, Lenis and three.js are all self-hosted). Ships as a one-command
Docker Compose stack routed by **Traefik**.

## Run it

```bash
cp .env.example .env      # adjust HTTP_PORT if 80 is taken
docker compose up -d --build
# → http://localhost:<HTTP_PORT>
docker compose down       # stop everything
```

Without Docker (any box with PHP 8.1+):

```bash
php -S localhost:8000 -t public
```

### Production TLS (Let's Encrypt at Traefik)

If the box faces the internet directly (ports 80/443 free), use the overlay:

```bash
# .env needs SITE_DOMAIN + ACME_EMAIL
docker compose -f compose.yaml -f compose.prod.yaml up -d --build
```

### Production behind an existing edge proxy (current setup)

TLS terminates at the public Apache edge; it proxies to this stack's Traefik
(`HTTP_PORT`, default 8123). The vhost lives in
[`deploy/apache-edge.conf`](deploy/apache-edge.conf) — copy it to the edge host,
`a2ensite` it, reload Apache. The proxy sets `X-Forwarded-Proto: https`, which makes
PHP emit HSTS and secure session cookies.

## Architecture

```
public/            ← the only web-servable directory (docroot)
  index.php        ← single-page entry: boots security, renders partials
  contact.php      ← hardened form endpoint (JSON for fetch, PRG fallback)
  assets/          ← css, js, vendored libs, self-hosted fonts, images
app/               ← never web-servable (guard constant + .htaccess + docroot)
  config.php       ← site constants, contact transport, rate limits
  security.php     ← CSP nonce, security headers, hardened session, app secret
  helpers.php      ← escaping, CSRF, time-trap, rate limiter, view helpers
  data.php         ← ALL site content (single source of truth)
  partials/        ← head, preloader, nav, hero, about, skills, experience,
                     projects, testimonials, contact, footer
var/               ← runtime state (secret key, rate-limit counters, messages.log)
scripts/           ← Node tooling (photo crops via sharp, asset vendoring) — dev only
deploy/            ← edge Apache vhost
```

## Security measures

| Layer | What's in place |
|---|---|
| Headers | Strict CSP (nonce'd scripts, no third-party origins), `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP, HSTS when HTTPS |
| Output | Every dynamic value passes through `e()` (htmlspecialchars, ENT_QUOTES, UTF-8) |
| Contact form | CSRF token (`hash_equals`), honeypot field (bots get a fake success), HMAC-signed time-trap (min 3 s), per-IP rate limit (5/hour, IPs stored only as HMAC hashes), strict validation, CRLF-stripped mail headers |
| Session | `HttpOnly`, `SameSite=Lax`, `Secure` behind HTTPS, strict mode, cookie only used for CSRF/flash |
| Filesystem | Docroot = `public/` only; `app/` guarded three ways; `var/` outside docroot; dotfile/backup patterns denied |
| PHP runtime | `expose_php=0`, errors to log only, 1 MB post cap, 15 s execution cap (see `Dockerfile`) |
| Delivery | `CONTACT_TRANSPORT` in `app/config.php`: `'log'` (default — messages append to `var/messages.log`) or `'mail'` (PHP `mail()`) |

Read messages from the log transport:

```bash
docker exec surethan-portfolio-web-1 cat /var/www/html/var/messages.log
```

## Design system

Generated with the `ui-ux-pro-max` design intelligence — **Modern Dark Cinema**:
near-black `#0a0a0b` (never pure black), monochrome + electric blue `#3b82f6`,
Space Grotesk display / Inter body / JetBrains Mono labels, `cubic-bezier(0.16,1,0.3,1)`
motion grammar. Light theme included; dark is the default showpiece.

Motion: Lenis smooth scroll, GSAP ScrollTrigger reveals (server-side split word masks —
zero CLS), three.js particle hero (lazy-loaded, paused off-screen, skipped on
`prefers-reduced-motion` and data-saver). Everything degrades gracefully with JS off.

Testing hooks: `/?noanim=1` renders the page static; `/?theme=light` forces light mode.

## Content

All copy, numbers and project data live in [`app/data.php`](app/data.php).
Remaining placeholders to fill in: GitHub profile URL, exact SNA start month,
KEC years attended, 1–2 concrete n8n/MCP examples, real testimonials.

## Asset tooling (dev only)

```bash
npm install          # sharp + the vendored libs
npm run photos       # regenerate image crops from surethan.jpg
npm run vendor       # re-vendor gsap/lenis/three + refresh self-hosted fonts
```

---
Designed & built by Surethan S · engineered with Claude Code
