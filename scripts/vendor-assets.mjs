/**
 * Copies pinned JS libraries out of node_modules and downloads the three
 * Google-font families as self-hosted woff2 files (CSP: no third-party hosts).
 * Run: node scripts/vendor-assets.mjs
 */
import { mkdir, copyFile, writeFile, access } from "node:fs/promises";
import { createWriteStream } from "node:fs";
import { Readable } from "node:stream";
import { pipeline } from "node:stream/promises";

const VENDOR = "public/assets/vendor";
const FONTS = "public/assets/fonts";
await mkdir(VENDOR, { recursive: true });
await mkdir(FONTS, { recursive: true });

// ---- JS libs from node_modules ----
const libs = [
  ["node_modules/gsap/dist/gsap.min.js", `${VENDOR}/gsap.min.js`],
  ["node_modules/gsap/dist/ScrollTrigger.min.js", `${VENDOR}/ScrollTrigger.min.js`],
  ["node_modules/lenis/dist/lenis.min.js", `${VENDOR}/lenis.min.js`],
  ["node_modules/three/build/three.module.min.js", `${VENDOR}/three.module.min.js`],
  // three's module build imports ./three.core.min.js at runtime — must ship together
  ["node_modules/three/build/three.core.min.js", `${VENDOR}/three.core.min.js`],
];
for (const [src, dst] of libs) {
  await access(src);
  await copyFile(src, dst);
  console.log("vendored", dst);
}

// ---- Fonts: fetch css2, download unique woff2 (latin), emit fonts.css ----
const UA =
  "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36";
const families = [
  "family=Space+Grotesk:wght@300..700",
  "family=Inter:opsz,wght@14..32,100..900",
  "family=JetBrains+Mono:ital,wght@0,100..800;1,100..800",
].join("&");
const cssUrl = `https://fonts.googleapis.com/css2?${families}&display=swap`;

const css = await (await fetch(cssUrl, { headers: { "User-Agent": UA } })).text();

// Keep only latin blocks (each @font-face is preceded by a `/* latin */`-style comment)
const blocks = css.split("/* ").filter((b) => b.startsWith("latin */"));
let out = "";
let n = 0;
const seen = new Map();
for (const block of blocks) {
  const face = "/* " + block;
  const urlMatch = face.match(/url\((https:[^)]+\.woff2)\)/);
  if (!urlMatch) continue;
  const remote = urlMatch[1];
  let local = seen.get(remote);
  if (!local) {
    const famMatch = face.match(/font-family:\s*'([^']+)'/);
    const slug = (famMatch?.[1] || "font").toLowerCase().replace(/\s+/g, "-");
    local = `${slug}-${n++}.woff2`;
    seen.set(remote, local);
    const res = await fetch(remote, { headers: { "User-Agent": UA } });
    await pipeline(Readable.fromWeb(res.body), createWriteStream(`${FONTS}/${local}`));
    console.log("font", local);
  }
  out += face.replace(urlMatch[1], `../fonts/${local}`) + "\n";
}
await writeFile("public/assets/css/fonts.css", out || "/* no fonts downloaded */");
console.log(`fonts.css written (${seen.size} files)`);
