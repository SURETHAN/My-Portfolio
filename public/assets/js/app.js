/* ==================================================================
   Surethan S — portfolio engine (vanilla JS, strict-CSP friendly)
   gsap + ScrollTrigger + Lenis are loaded as classic scripts before
   this module; three.js is imported lazily by hero-scene.js.
   ================================================================== */

window.__appReady = true; // head boot-script fallback: content unhides if this never runs

// ?noanim testing hook: full-page screenshots never scroll, so force lazy images in
if (window.__noanim === true) {
  document.querySelectorAll('img[loading="lazy"]').forEach((img) => { img.loading = "eager"; });
}

const doc = document.documentElement;
const REDUCED = window.matchMedia("(prefers-reduced-motion: reduce)").matches || window.__noanim === true;
const FINE = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
const EASE = "expo.out";

const hasGsap = typeof window.gsap !== "undefined";
if (hasGsap && typeof window.ScrollTrigger !== "undefined") {
  gsap.registerPlugin(ScrollTrigger);
  gsap.defaults({ ease: EASE, duration: 0.8 });
}

/* ------------------------------------------------------------------ */
/* Smooth scrolling (Lenis) driven by the gsap ticker                  */
/* ------------------------------------------------------------------ */

let lenis = null;
if (!REDUCED && hasGsap && typeof window.Lenis !== "undefined") {
  lenis = new Lenis({ autoRaf: false, lerp: 0.105, wheelMultiplier: 1, touchMultiplier: 1.4 });
  gsap.ticker.add((time) => lenis.raf(time * 1000));
  gsap.ticker.lagSmoothing(0);
  lenis.on("scroll", ScrollTrigger.update);
}

function scrollToTarget(target) {
  const dest = target === "#top" ? 0 : target;
  if (lenis) {
    lenis.scrollTo(dest, { duration: 1.35, easing: (t) => 1 - Math.pow(1 - t, 4) });
  } else {
    if (dest === 0) window.scrollTo({ top: 0, behavior: "smooth" });
    else document.querySelector(dest)?.scrollIntoView({ behavior: "smooth" });
  }
}

document.querySelectorAll("[data-scroll]").forEach((el) => {
  el.addEventListener("click", (e) => {
    const href = el.getAttribute("href") || "";
    if (!href.startsWith("#")) return;
    e.preventDefault();
    closeMenu();
    scrollToTarget(href);
    history.replaceState(null, "", href === "#top" ? " " : href);
  });
});
document.querySelectorAll("[data-scroll-to]").forEach((el) => {
  el.addEventListener("click", () => scrollToTarget(el.getAttribute("data-scroll-to")));
});

/* ------------------------------------------------------------------ */
/* Theme toggle (boot script already applied saved theme)              */
/* ------------------------------------------------------------------ */

const themeToggle = document.getElementById("theme-toggle");
const themeListeners = [];
themeToggle?.addEventListener("click", () => {
  const dark = doc.classList.toggle("dark");
  try { localStorage.setItem("theme", dark ? "dark" : "light"); } catch {}
  themeListeners.forEach((fn) => fn(dark));
});

/* ------------------------------------------------------------------ */
/* Preloader → intro gate                                              */
/* ------------------------------------------------------------------ */

const introListeners = [];
let introDone = false;
function finishIntro() {
  if (introDone) return;
  introDone = true;
  introListeners.forEach((fn) => fn());
}
function onIntro(fn) {
  if (introDone) fn();
  else introListeners.push(fn);
}

(function preloader() {
  const el = document.getElementById("preloader");
  if (!el || !hasGsap) { finishIntro(); return; }

  let played = false;
  try { played = sessionStorage.getItem("sn-intro-played") === "1"; } catch {}
  if (played || REDUCED) {
    el.remove();
    finishIntro();
    return;
  }

  el.hidden = false;
  lenis?.stop();
  const letters = el.querySelectorAll(".pl-ch");
  const count = document.getElementById("pl-count");
  const bar = document.getElementById("pl-bar");
  const state = { v: 0 };

  gsap.timeline({
    onComplete() {
      try { sessionStorage.setItem("sn-intro-played", "1"); } catch {}
      lenis?.start();
      el.remove();
      finishIntro();
    },
  })
    .to(letters, { y: 0, opacity: 1, duration: 0.7, stagger: 0.045 }, 0.1)
    .to(state, {
      v: 100,
      duration: 1.15,
      ease: "power2.inOut",
      onUpdate() {
        if (count) count.textContent = String(Math.round(state.v)).padStart(3, "0");
        if (bar) bar.style.transform = `scaleX(${state.v / 100})`;
      },
    }, 0.15)
    .to(letters, { y: "-110%", duration: 0.55, stagger: 0.03, ease: "expo.in" }, "+=0.12")
    .to(el, { clipPath: "inset(0% 0% 100% 0%)", duration: 0.7, ease: "expo.inOut" }, "-=0.25");
})();

/* ------------------------------------------------------------------ */
/* Hero entrance (plays once the intro gate opens)                     */
/* ------------------------------------------------------------------ */

onIntro(() => {
  if (!hasGsap || REDUCED) {
    document.querySelectorAll(".hn-ch, .fx-fade").forEach((n) => {
      n.style.opacity = "1";
      n.style.transform = "none";
    });
    return;
  }
  const chars = document.querySelectorAll("#hero-name .hn-ch");
  const order = ["chip", "role", "sub", "ctas", "rail"];
  const tl = gsap.timeline();
  tl.to(chars, { y: 0, rotationX: 0, opacity: 1, duration: 0.9, stagger: 0.038 }, 0.12);
  order.forEach((key, i) => {
    tl.fromTo(
      `[data-hero="${key}"]`,
      { opacity: 0, y: 22 },
      { opacity: 1, y: 0, duration: 0.8 },
      0.55 + i * 0.11
    );
  });
});

/* ------------------------------------------------------------------ */
/* Scroll reveals                                                      */
/* ------------------------------------------------------------------ */

if (hasGsap && !REDUCED) {
  // Block reveals
  gsap.utils.toArray("[data-reveal]").forEach((el) => {
    gsap.to(el, {
      opacity: 1,
      y: 0,
      duration: 0.85,
      scrollTrigger: { trigger: el, start: "top 88%", once: true },
      clearProps: "transform",
    });
  });

  // Word-mask reveals, grouped by parent element
  const roots = new Set();
  document.querySelectorAll(".rw-m").forEach((m) => roots.add(m.parentElement));
  roots.forEach((root) => {
    const words = root.querySelectorAll(".rw-w");
    gsap.to(words, {
      y: 0,
      rotation: 0,
      duration: 0.75,
      stagger: 0.045,
      scrollTrigger: { trigger: root, start: "top 90%", once: true },
    });
  });

  // Counters
  gsap.utils.toArray("[data-counter]").forEach((el) => {
    const target = parseInt(el.getAttribute("data-counter"), 10) || 0;
    const suffix = el.getAttribute("data-suffix") || "";
    const state = { v: 0 };
    el.textContent = "0" + suffix;
    gsap.to(state, {
      v: target,
      duration: 1.8,
      scrollTrigger: { trigger: el, start: "top 88%", once: true },
      onUpdate() { el.textContent = Math.round(state.v).toLocaleString("en-IN") + suffix; },
    });
  });

  // Experience spine draw
  const fill = document.getElementById("tl-fill");
  if (fill) {
    gsap.fromTo(fill, { scaleY: 0 }, {
      scaleY: 1,
      ease: "none",
      scrollTrigger: { trigger: "#timeline", start: "top 62%", end: "bottom 68%", scrub: 0.5 },
    });
  }

  // Portrait parallax inside its mask
  const portrait = document.getElementById("portrait-parallax");
  if (portrait) {
    gsap.fromTo(portrait, { yPercent: -7 }, {
      yPercent: 7,
      ease: "none",
      scrollTrigger: { trigger: ".portrait", start: "top bottom", end: "bottom top", scrub: 0.7 },
    });
  }

  // Hero content drifts up as you scroll past
  gsap.to("#hero-main", {
    yPercent: -14,
    opacity: 0.15,
    ease: "none",
    scrollTrigger: { trigger: ".hero", start: "top top", end: "88% top", scrub: 0.6 },
  });

  document.fonts?.ready.then(() => ScrollTrigger.refresh());
}

/* ------------------------------------------------------------------ */
/* Navbar: active section, hide on scroll down, mobile menu            */
/* ------------------------------------------------------------------ */

const nav = document.getElementById("site-nav");
const navLinksWrap = document.getElementById("nav-links");
const navInd = document.getElementById("nav-ind");
const navLinks = [...document.querySelectorAll(".nav-link")];

function setActiveLink(href) {
  let active = null;
  navLinks.forEach((a) => {
    const is = a.getAttribute("href") === href;
    a.classList.toggle("is-active", is);
    if (is) { a.setAttribute("aria-current", "true"); active = a; }
    else a.removeAttribute("aria-current");
  });
  if (!navInd) return;
  if (active && navLinksWrap) {
    const wrapBox = navLinksWrap.getBoundingClientRect();
    const box = active.getBoundingClientRect();
    navInd.style.width = `${box.width}px`;
    navInd.style.transform = `translateX(${box.left - wrapBox.left}px)`;
    navInd.style.opacity = "1";
  } else {
    navInd.style.opacity = "0";
  }
}

const sectionIds = navLinks.map((a) => a.getAttribute("href"));
const sections = sectionIds.map((id) => document.querySelector(id)).filter(Boolean);
const sectionIO = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => { if (e.isIntersecting) setActiveLink(`#${e.target.id}`); });
  },
  { rootMargin: "-42% 0px -52% 0px" }
);
sections.forEach((s) => sectionIO.observe(s));
window.addEventListener("resize", () => {
  const current = navLinks.find((a) => a.classList.contains("is-active"));
  if (current) setActiveLink(current.getAttribute("href"));
});

let lastY = 0;
function onScrollDirection(y) {
  const menuOpen = document.getElementById("mobile-menu")?.classList.contains("is-open");
  nav?.classList.toggle("is-hidden", y > lastY && y > 160 && !menuOpen);
  lastY = y;
}
if (lenis) lenis.on("scroll", ({ scroll }) => onScrollDirection(scroll));
else window.addEventListener("scroll", () => onScrollDirection(window.scrollY), { passive: true });

const menu = document.getElementById("mobile-menu");
const menuOpenBtn = document.getElementById("menu-open");
const menuCloseBtn = document.getElementById("menu-close");
function openMenu() {
  menu?.classList.add("is-open");
  menuOpenBtn?.setAttribute("aria-expanded", "true");
  lenis?.stop();
  menuCloseBtn?.focus();
}
function closeMenu() {
  if (!menu?.classList.contains("is-open")) return;
  menu.classList.remove("is-open");
  menuOpenBtn?.setAttribute("aria-expanded", "false");
  lenis?.start();
}
menuOpenBtn?.addEventListener("click", openMenu);
menuCloseBtn?.addEventListener("click", closeMenu);
window.addEventListener("keydown", (e) => { if (e.key === "Escape") closeMenu(); });

/* ------------------------------------------------------------------ */
/* Rotating role line                                                  */
/* ------------------------------------------------------------------ */

(function rotateRoles() {
  const word = document.getElementById("role-word");
  if (!word) return;
  let roles = [];
  try {
    const sr = word.closest(".hero-role")?.querySelector(".visually-hidden")?.textContent || "";
    roles = sr.replace(/^Roles:\s*/, "").split(",").map((s) => s.trim()).filter(Boolean);
  } catch {}
  if (roles.length < 2) return;
  let i = 0;
  setInterval(() => {
    i = (i + 1) % roles.length;
    if (!hasGsap || REDUCED) { word.textContent = roles[i]; return; }
    gsap.timeline()
      .to(word, { y: "-105%", duration: 0.45, ease: "expo.in" })
      .add(() => { word.textContent = roles[i]; })
      .set(word, { y: "105%" })
      .to(word, { y: "0%", duration: 0.55 });
  }, 2800);
})();

/* ------------------------------------------------------------------ */
/* Magnetic elements                                                   */
/* ------------------------------------------------------------------ */

if (FINE && !REDUCED && hasGsap) {
  document.querySelectorAll("[data-magnetic]").forEach((el) => {
    const qx = gsap.quickTo(el, "x", { duration: 0.4, ease: EASE });
    const qy = gsap.quickTo(el, "y", { duration: 0.4, ease: EASE });
    const clamp = gsap.utils.clamp(-10, 10);
    el.addEventListener("pointermove", (e) => {
      const r = el.getBoundingClientRect();
      qx(clamp((e.clientX - (r.left + r.width / 2)) * 0.28));
      qy(clamp((e.clientY - (r.top + r.height / 2)) * 0.28));
    });
    el.addEventListener("pointerleave", () => { qx(0); qy(0); });
  });
}

/* ------------------------------------------------------------------ */
/* Tilt + spotlight cards                                              */
/* ------------------------------------------------------------------ */

if (FINE && !REDUCED && hasGsap) {
  document.querySelectorAll("[data-tilt]").forEach((el) => {
    const max = parseFloat(el.getAttribute("data-tilt-max")) || 5;
    const qrx = gsap.quickTo(el, "rotationX", { duration: 0.5, ease: EASE });
    const qry = gsap.quickTo(el, "rotationY", { duration: 0.5, ease: EASE });
    el.style.transformStyle = "preserve-3d";
    el.addEventListener("pointermove", (e) => {
      const r = el.getBoundingClientRect();
      const px = (e.clientX - r.left) / r.width;
      const py = (e.clientY - r.top) / r.height;
      qrx((0.5 - py) * max * 2);
      qry((px - 0.5) * max * 2);
      el.style.setProperty("--mx", `${px * 100}%`);
      el.style.setProperty("--my", `${py * 100}%`);
    });
    el.addEventListener("pointerleave", () => { qrx(0); qry(0); });
  });
}

/* ------------------------------------------------------------------ */
/* Custom cursor (dot + trailing ring)                                 */
/* ------------------------------------------------------------------ */

if (FINE) {
  const dot = document.getElementById("cursor-dot");
  const ring = document.getElementById("cursor-ring");
  if (dot && ring) {
    doc.classList.add("has-custom-cursor");
    const INTERACTIVE = 'a, button, [role="button"], [data-cursor], input, textarea, select, summary, label';
    let x = innerWidth / 2, y = innerHeight / 2, rx = x, ry = y;
    let scale = 1, targetScale = 1, shown = false;

    window.addEventListener("pointermove", (e) => {
      x = e.clientX; y = e.clientY;
      if (!shown) {
        shown = true; rx = x; ry = y;
        dot.style.opacity = "1"; ring.style.opacity = "1";
      }
    }, { passive: true });
    doc.addEventListener("pointerleave", () => {
      shown = false;
      dot.style.opacity = "0"; ring.style.opacity = "0";
    });
    document.addEventListener("pointerover", (e) => {
      const hit = e.target.closest?.(INTERACTIVE);
      targetScale = hit ? 2.1 : 1;
      ring.classList.toggle("is-hover", !!hit);
    }, { passive: true });
    document.addEventListener("pointerdown", () => { targetScale = 0.85; }, { passive: true });
    document.addEventListener("pointerup", () => { targetScale = 1; }, { passive: true });

    (function tick() {
      const k = REDUCED ? 1 : 0.16;
      rx += (x - rx) * k; ry += (y - ry) * k;
      scale += (targetScale - scale) * 0.18;
      dot.style.transform = `translate3d(${x}px,${y}px,0) translate(-50%,-50%)`;
      ring.style.transform = `translate3d(${rx}px,${ry}px,0) translate(-50%,-50%) scale(${scale})`;
      requestAnimationFrame(tick);
    })();
  }
}

/* ------------------------------------------------------------------ */
/* Site-wide ambient light layer (lazy module, paused when tab hidden) */
/* ------------------------------------------------------------------ */

(function sceneLayer() {
  const holder = document.getElementById("scene-layer");
  if (!holder || REDUCED) return;
  if (navigator.connection?.saveData) return;

  let scene = null;
  onIntro(async () => {
    try {
      const mod = await import(`./hero-scene.js?v=${holder.dataset.v || "1"}`);
      scene = mod.mount(holder, { dark: doc.classList.contains("dark") });
      themeListeners.push((dark) => scene?.setTheme(dark));
      document.addEventListener("visibilitychange", () => {
        scene?.[document.hidden ? "pause" : "resume"]();
      });
    } catch { /* WebGL unavailable — static backdrop stays */ }
  });
})();

/* ------------------------------------------------------------------ */
/* Copy e-mail                                                         */
/* ------------------------------------------------------------------ */

(function copyEmail() {
  const btn = document.getElementById("copy-email");
  const state = document.getElementById("copy-state");
  if (!btn) return;
  let timer = null;
  btn.addEventListener("click", async () => {
    const email = btn.getAttribute("data-email") || "";
    let ok = false;
    try { await navigator.clipboard.writeText(email); ok = true; }
    catch {
      const ta = document.createElement("textarea");
      ta.value = email;
      ta.style.position = "fixed"; ta.style.opacity = "0";
      document.body.appendChild(ta);
      ta.select();
      try { ok = document.execCommand("copy"); } catch {}
      ta.remove();
    }
    if (!ok) { window.location.href = `mailto:${email}`; return; }
    btn.classList.add("is-copied");
    state?.classList.add("is-visible");
    clearTimeout(timer);
    timer = setTimeout(() => {
      btn.classList.remove("is-copied");
      state?.classList.remove("is-visible");
    }, 2200);
  });
})();

/* ------------------------------------------------------------------ */
/* Footer clock (IST)                                                  */
/* ------------------------------------------------------------------ */

(function clock() {
  const el = document.getElementById("f-time");
  if (!el) return;
  const fmt = new Intl.DateTimeFormat("en-GB", {
    timeZone: el.getAttribute("data-tz") || "Asia/Kolkata",
    hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: false,
  });
  const update = () => { el.textContent = fmt.format(new Date()); };
  update();
  setInterval(update, 1000);
})();

/* ------------------------------------------------------------------ */
/* Contact form: fetch submit with graceful non-JS fallback            */
/* ------------------------------------------------------------------ */

(function contactForm() {
  const form = document.getElementById("contact-form");
  const flash = document.getElementById("form-flash");
  if (!form || !flash) return;

  form.addEventListener("submit", async (e) => {
    if (!form.checkValidity()) return; // native validation UI
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const label = btn?.firstChild;
    const original = label?.textContent;
    if (btn) { btn.disabled = true; if (label) label.textContent = "Sending… "; }

    try {
      const res = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: { "X-Requested-With": "fetch" },
      });
      const data = await res.json();
      flash.hidden = false;
      flash.className = `flash flash--${data.ok ? "success" : "error"}`;
      flash.textContent = data.message;
      if (data.ok) form.reset();
    } catch {
      flash.hidden = false;
      flash.className = "flash flash--error";
      flash.textContent = "Network hiccup — try again, or email me directly.";
    } finally {
      if (btn) { btn.disabled = false; if (label && original) label.textContent = original; }
      flash.scrollIntoView({ block: "nearest", behavior: REDUCED ? "auto" : "smooth" });
    }
  });
})();
