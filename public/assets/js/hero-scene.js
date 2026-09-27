/* Site-wide ambient backdrop: silky green light ribbons that follow the
   cursor (spring physics + GPU feedback buffer with curl advection, so
   strokes keep swirling and slowly dissolve), over a faint particle field.
   Background-only by construction: the canvas is fixed at z-index -1 with
   pointer-events none — it can never cover or block content. */

import * as THREE from "three";

const PARTICLES = 1600;
const SIM_MAX_W = 960; // trail buffer cap — half-res keeps it cheap

function mulberry32(a) {
  return () => {
    a |= 0;
    a = (a + 0x6d2b79f5) | 0;
    let t = Math.imul(a ^ (a >>> 15), 1 | a);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}

export function mount(holder, { dark }) {
  const renderer = new THREE.WebGLRenderer({ antialias: false, alpha: true, powerPreference: "high-performance" });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
  renderer.setSize(holder.clientWidth, holder.clientHeight);
  renderer.autoClear = false;
  renderer.domElement.setAttribute("aria-hidden", "true");
  holder.prepend(renderer.domElement);

  /* ---------------- trail feedback system ---------------- */

  let simW = 0, simH = 0;
  const rtOpts = {
    minFilter: THREE.LinearFilter,
    magFilter: THREE.LinearFilter,
    depthBuffer: false,
    stencilBuffer: false,
    type: THREE.HalfFloatType,
  };
  let rtA = new THREE.WebGLRenderTarget(4, 4, rtOpts);
  let rtB = new THREE.WebGLRenderTarget(4, 4, rtOpts);

  const simCam = new THREE.OrthographicCamera(0, 1, 1, 0, -1, 1);
  const screenCam = new THREE.OrthographicCamera(-1, 1, 1, -1, -1, 1);

  // Pass 1: previous frame — faded, gently advected by a curl-ish flow
  // field so ribbons keep swirling after they are drawn (the silky part)
  const fadeMat = new THREE.ShaderMaterial({
    uniforms: { uPrev: { value: null }, uDecay: { value: 0.988 }, uT: { value: 0 } },
    vertexShader: "varying vec2 vUv; void main(){ vUv = uv; gl_Position = vec4(position.xy, 0.0, 1.0); }",
    fragmentShader: `
      uniform sampler2D uPrev; uniform float uDecay; uniform float uT; varying vec2 vUv;
      void main(){
        vec2 flow = vec2(
          sin(vUv.y * 6.3 + uT * 0.33) + 0.6 * sin(vUv.y * 14.1 - uT * 0.21) ,
          sin(vUv.x * 5.7 - uT * 0.27) + 0.6 * sin(vUv.x * 12.4 + uT * 0.18)
        ) * 0.00085;
        flow.y -= 0.00035; // slow upward smoke drift
        vec3 c = texture2D(uPrev, vUv + flow).rgb * uDecay;
        gl_FragColor = vec4(min(c, 1.6), 1.0);
      }`,
    depthTest: false, depthWrite: false,
  });
  const fadeScene = new THREE.Scene();
  fadeScene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), fadeMat));

  // Brush: small soft stamp — three parallel filaments approximate a ribbon
  const brushMat = new THREE.ShaderMaterial({
    uniforms: { uColor: { value: new THREE.Color() }, uIntensity: { value: 1 } },
    vertexShader: "varying vec2 vUv; void main(){ vUv = uv; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }",
    fragmentShader: `
      uniform vec3 uColor; uniform float uIntensity; varying vec2 vUv;
      void main(){
        float d = length(vUv - 0.5) * 2.0;
        float a = pow(max(1.0 - d, 0.0), 1.7) * uIntensity;
        gl_FragColor = vec4(uColor * a, 1.0);
      }`,
    blending: THREE.AdditiveBlending, transparent: true, depthTest: false, depthWrite: false,
  });
  const brush = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), brushMat);
  const brushScene = new THREE.Scene();
  brushScene.add(brush);

  // Deep-space nebula (procedural, Zeta-Ophiuchi-inspired greens for dark,
  // mint wisps for light). Rendered at quarter-res every 3rd frame — cheap.
  const nebulaRT = new THREE.WebGLRenderTarget(4, 4, {
    minFilter: THREE.LinearFilter, magFilter: THREE.LinearFilter,
    depthBuffer: false, stencilBuffer: false,
  });
  const nebulaMat = new THREE.ShaderMaterial({
    uniforms: { uT: { value: 0 }, uDark: { value: dark ? 1 : 0 }, uAspect: { value: 1.6 } },
    vertexShader: "varying vec2 vUv; void main(){ vUv = uv; gl_Position = vec4(position.xy, 0.0, 1.0); }",
    fragmentShader: `
      uniform float uT; uniform float uDark; uniform float uAspect; varying vec2 vUv;
      float hash(vec2 p){ p = fract(p * vec2(234.34, 435.345)); p += dot(p, p + 34.23); return fract(p.x * p.y); }
      float noise(vec2 p){
        vec2 i = floor(p), f = fract(p); f = f * f * (3.0 - 2.0 * f);
        return mix(mix(hash(i), hash(i + vec2(1, 0)), f.x), mix(hash(i + vec2(0, 1)), hash(i + vec2(1, 1)), f.x), f.y);
      }
      float fbm(vec2 p){ float v = 0.0, a = 0.5; for (int i = 0; i < 5; i++){ v += a * noise(p); p *= 2.03; a *= 0.5; } return v; }
      void main(){
        vec2 uv = vec2(vUv.x * uAspect, vUv.y);
        vec2 warp = vec2(fbm(uv * 1.3 + uT * 0.006), fbm(uv * 1.3 + 7.3 - uT * 0.005));
        float n = fbm(uv * 2.0 + warp * 1.7);
        float cloud = smoothstep(0.45, 0.9, n);
        float wisp = smoothstep(0.55, 0.95, fbm(uv * 3.7 - warp * 1.2 + uT * 0.004));
        vec3 col; float alpha;
        if (uDark > 0.5) {
          col = mix(vec3(0.02, 0.10, 0.06), vec3(0.06, 0.35, 0.18), cloud);
          col = mix(col, vec3(0.05, 0.28, 0.24), wisp * 0.6);
          alpha = cloud * 0.34 + wisp * 0.10;
          // faint warm galactic-core dust along the same band as the stars
          float band = exp(-pow(vUv.y - (0.58 - 0.28 * vUv.x), 2.0) / 0.09);
          col = mix(col, vec3(0.30, 0.27, 0.16), band * wisp * 0.5);
          alpha += band * wisp * 0.12;
          // sparse twinkling starfield
          vec2 sg = uv * 70.0; vec2 id = floor(sg); float h = hash(id);
          if (h > 0.994) {
            float s = 1.0 - smoothstep(0.0, 0.35, length(fract(sg) - 0.5));
            float tw = 0.6 + 0.4 * sin(uT * (1.0 + h * 3.0) + h * 40.0);
            float bright = pow(s, 3.0) * tw;
            col += vec3(0.7, 1.0, 0.85) * bright;
            alpha += bright * 0.8;
          }
        } else {
          col = mix(vec3(0.82, 0.97, 0.89), vec3(0.55, 0.90, 0.75), cloud);
          alpha = cloud * 0.30 + wisp * 0.08;
        }
        gl_FragColor = vec4(col, clamp(alpha, 0.0, 1.0));
      }`,
    depthTest: false, depthWrite: false,
  });
  const nebulaScene = new THREE.Scene();
  nebulaScene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), nebulaMat));

  const nebulaCompMat = new THREE.MeshBasicMaterial({
    map: nebulaRT.texture, transparent: true, depthTest: false, depthWrite: false,
  });
  const nebulaComp = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), nebulaCompMat);
  const nebulaCompScene = new THREE.Scene();
  nebulaCompScene.add(nebulaComp);

  // Composite: tone-map the trail buffer onto the page
  const compMat = new THREE.ShaderMaterial({
    uniforms: {
      uTrail: { value: null },
      uStrength: { value: dark ? 0.85 : 0.55 },
      uT: { value: 0 },
      uDark: { value: dark ? 1 : 0 },
      uAspect: { value: 1.6 },
    },
    vertexShader: "varying vec2 vUv; void main(){ vUv = uv; gl_Position = vec4(position.xy, 0.0, 1.0); }",
    fragmentShader: `
      uniform sampler2D uTrail; uniform float uStrength; uniform float uT;
      uniform float uDark; uniform float uAspect; varying vec2 vUv;
      float hash(vec2 p){ p = fract(p * vec2(234.34, 435.345)); p += dot(p, p + 34.23); return fract(p.x * p.y); }
      // One crisp star layer: random position inside sparse grid cells
      float stars(vec2 uv, float grid, float thresh, float size, float twAmp){
        vec2 sg = uv * grid;
        vec2 id = floor(sg);
        float h = hash(id);
        if (h < thresh) return 0.0;
        vec2 off = (vec2(hash(id + 1.3), hash(id + 2.7)) - 0.5) * 0.6;
        float d = length(fract(sg) - 0.5 - off);
        float core = 1.0 - smoothstep(0.0, size * (0.6 + hash(id + 3.1)), d);
        float tw = 1.0 - twAmp + twAmp * (0.5 + 0.5 * sin(uT * (0.4 + h * 1.8) + h * 50.0));
        return pow(core, 3.0) * (0.2 + 0.8 * fract(h * 13.7)) * tw;
      }
      void main(){
        vec3 t = texture2D(uTrail, vUv).rgb * uStrength;
        vec2 e = smoothstep(vec2(0.0), vec2(0.06), vUv) * smoothstep(vec2(0.0), vec2(0.06), 1.0 - vUv);
        t *= e.x * e.y;
        if (uDark > 0.5) {
          // Aurora glow + Milky-Way starfield (dense dust + twinkling mids,
          // denser along a soft diagonal band like the reference sky)
          t = min(t, vec3(0.9)); // contrast guard: glow stays dimmer than body text
          float alpha = max(t.r, max(t.g, t.b)) * 1.4;
          vec2 uvA = vec2(vUv.x * uAspect, vUv.y);
          float band = exp(-pow(vUv.y - (0.58 - 0.28 * vUv.x), 2.0) / 0.09);
          float dust = stars(uvA, 150.0, 0.70, 0.26, 0.25) * (0.34 + band * 0.22);
          float mids = stars(uvA + 11.7, 58.0, 0.90, 0.24, 0.55) * (0.65 + band * 0.25);
          t += vec3(0.85, 1.0, 0.92) * (dust + mids);
          alpha += (dust + mids) * 0.95;
          gl_FragColor = vec4(t, clamp(alpha, 0.0, 1.0));
        } else {
          // Ink-in-water silk: white base; the canvas multiplies the page
          // (CSS mix-blend-mode), so strokes tint it toward the trail hue.
          float k = max(t.r, max(t.g, t.b));
          vec3 hue = k > 0.001 ? clamp(t / k, 0.0, 1.0) : vec3(1.0);
          float ink = clamp(k * 1.25, 0.0, 1.0) * 0.55; // contrast-safe cap
          gl_FragColor = vec4(mix(vec3(1.0), hue, ink), 1.0);
        }
      }`,
    blending: THREE.AdditiveBlending, transparent: true, depthTest: false, depthWrite: false,
  });
  const compScene = new THREE.Scene();
  compScene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), compMat));

  /* ---------------- faint particle depth layer ---------------- */

  const perspCam = new THREE.PerspectiveCamera(55, 1, 0.1, 60);
  perspCam.position.set(0, 0.4, 7);
  const positions = new Float32Array(PARTICLES * 3);
  const seeds = new Float32Array(PARTICLES);
  const rng = mulberry32(42);
  for (let i = 0; i < PARTICLES; i++) {
    const r = Math.sqrt(rng()) * 11;
    const theta = rng() * Math.PI * 2;
    positions[i * 3] = Math.cos(theta) * r * 1.6;
    positions[i * 3 + 1] = (rng() - 0.42) * 5.2;
    positions[i * 3 + 2] = Math.sin(theta) * r - 4;
    seeds[i] = rng() * Math.PI * 2;
  }
  const pGeo = new THREE.BufferGeometry();
  pGeo.setAttribute("position", new THREE.BufferAttribute(positions, 3));
  // Soft round sprite so points glow instead of rendering as hard squares
  const spriteCanvas = document.createElement("canvas");
  spriteCanvas.width = spriteCanvas.height = 64;
  const ctx = spriteCanvas.getContext("2d");
  const grad = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
  grad.addColorStop(0, "rgba(255,255,255,1)");
  grad.addColorStop(0.4, "rgba(255,255,255,0.5)");
  grad.addColorStop(1, "rgba(255,255,255,0)");
  ctx.fillStyle = grad;
  ctx.fillRect(0, 0, 64, 64);
  const sprite = new THREE.CanvasTexture(spriteCanvas);
  const pMat = new THREE.PointsMaterial({
    size: 0.05, sizeAttenuation: true, transparent: true, opacity: 0,
    map: sprite, depthWrite: false, blending: THREE.AdditiveBlending,
    color: new THREE.Color(dark ? "#6bffa8" : "#16a34a"),
  });
  const points = new THREE.Points(pGeo, pMat);
  points.frustumCulled = false;
  const pScene = new THREE.Scene();
  pScene.add(points);

  /* ---------------- state ---------------- */

  // Ribbon hues: deep green → brand green → teal → mint
  const HUES = [new THREE.Color("#16a34a"), new THREE.Color("#22c55e"), new THREE.Color("#5eead4"), new THREE.Color("#a7f3d0")];
  const tmpColor = new THREE.Color();
  const sideColorA = new THREE.Color();
  const sideColorB = new THREE.Color();
  const DEEP = new THREE.Color("#15803d");
  const MINT = new THREE.Color("#a7f3d0");

  let isDark = dark;
  let paused = false;
  let raf = 0;
  let elapsed = 0;
  let lastPointerAt = -10;
  const clock = new THREE.Clock();
  const target = { x: 0.5, y: 0.42 };            // normalized viewport coords
  const pen = { x: 0.5, y: 0.42 };
  const vel = { x: 0, y: 0 };
  const prevPen = { x: 0.5, y: 0.42 };
  const pointer3 = { x: 0, y: 0 };

  function setSize() {
    const w = holder.clientWidth, h = holder.clientHeight;
    renderer.setSize(w, h);
    perspCam.aspect = w / h;
    perspCam.updateProjectionMatrix();
    simW = Math.min(SIM_MAX_W, Math.max(2, Math.round(w / 2)));
    simH = Math.max(2, Math.round(simW * (h / w)));
    rtA.setSize(simW, simH);
    rtB.setSize(simW, simH);
    nebulaRT.setSize(Math.max(2, Math.round(simW / 2)), Math.max(2, Math.round(simH / 2)));
    nebulaMat.uniforms.uAspect.value = w / h;
    compMat.uniforms.uAspect.value = w / h;
    simCam.right = simW; simCam.top = simH;
    simCam.updateProjectionMatrix();
  }
  setSize();

  const onPointer = (e) => {
    target.x = e.clientX / Math.max(window.innerWidth, 1);
    target.y = e.clientY / Math.max(window.innerHeight, 1);
    pointer3.x = target.x * 2 - 1;
    pointer3.y = -(target.y * 2 - 1);
    lastPointerAt = elapsed;
  };
  window.addEventListener("pointermove", onPointer, { passive: true });
  window.addEventListener("resize", setSize);

  function stamp(x, y, color, intensity, radius) {
    brushMat.uniforms.uColor.value.copy(color);
    brushMat.uniforms.uIntensity.value = intensity;
    brush.scale.set(radius, radius, 1);
    brush.position.set(x, simH - y, 0);
    renderer.render(brushScene, simCam);
  }

  function paint() {
    // Idle / touch: a slow autonomous drift keeps the light alive
    if (elapsed - lastPointerAt > 2.6) {
      target.x = 0.5 + 0.34 * Math.sin(elapsed * 0.13) + 0.06 * Math.sin(elapsed * 0.51);
      target.y = 0.42 + 0.22 * Math.sin(elapsed * 0.097 + 1.7);
    }
    prevPen.x = pen.x; prevPen.y = pen.y;

    // Spring follow: unhurried, glides in graceful arcs behind the cursor
    vel.x += (target.x - pen.x) * 0.034;
    vel.y += (target.y - pen.y) * 0.034;
    vel.x *= 0.9; vel.y *= 0.9;
    pen.x += vel.x; pen.y += vel.y;

    const dx = (pen.x - prevPen.x) * simW;
    const dy = (pen.y - prevPen.y) * simH;
    const dist = Math.hypot(dx, dy);
    if (dist < 0.35) return; // resting cursor must not accumulate a hot dot

    const speed = Math.min(dist / 30, 1);
    const nx = -dy / dist, ny = dx / dist; // stroke normal, for filaments

    // Even brightness along the stroke; hue cycles slowly
    const t = (elapsed * 0.09) % HUES.length;
    const i0 = Math.floor(t);
    tmpColor.copy(HUES[i0]).lerp(HUES[(i0 + 1) % HUES.length], t - i0);
    sideColorA.copy(tmpColor).lerp(MINT, 0.55);
    sideColorB.copy(tmpColor).lerp(DEEP, 0.55);

    const radius = simW * 0.014 * (1 + speed * 0.9); // thin ribbon core
    const gap = radius * 1.15;                        // filament separation
    const intensity = 0.4; // slower pen overlaps more — keep energy equal

    const steps = Math.min(Math.max(Math.ceil(dist / (radius * 0.3)), 1), 48);
    renderer.setRenderTarget(rtB);
    for (let s = 0; s < steps; s++) {
      const f = steps === 1 ? 1 : s / (steps - 1);
      const x = (prevPen.x + (pen.x - prevPen.x) * f) * simW;
      const y = (prevPen.y + (pen.y - prevPen.y) * f) * simH;
      // slight breathing of the filament offsets makes strands weave
      const weave = Math.sin(elapsed * 2.1 + f * 3.0) * 0.35 + 1.0;
      stamp(x, y, tmpColor, intensity, radius);
      stamp(x + nx * gap * weave, y + ny * gap * weave, sideColorA, intensity * 0.5, radius * 0.8);
      stamp(x - nx * gap * weave, y - ny * gap * weave, sideColorB, intensity * 0.5, radius * 0.8);
    }
    renderer.setRenderTarget(null);
  }

  /* Theme plumbing: dark = additive aurora over transparent canvas;
     light = opaque white canvas multiplied onto the page (ink-in-water). */
  function applyTheme(nextDark) {
    isDark = nextDark;
    pMat.color.set(nextDark ? "#6bffa8" : "#16a34a");
    compMat.uniforms.uStrength.value = nextDark ? 0.85 : 0.8;
    compMat.uniforms.uDark.value = nextDark ? 1 : 0;
    nebulaMat.uniforms.uDark.value = nextDark ? 1 : 0;
    if (nextDark) {
      renderer.setClearColor(0x000000, 0);
      renderer.domElement.style.mixBlendMode = "";
      compMat.blending = THREE.AdditiveBlending;
      compMat.transparent = true;
    } else {
      renderer.setClearColor(0xffffff, 1);
      renderer.domElement.style.mixBlendMode = "multiply";
      compMat.blending = THREE.NoBlending;
      compMat.transparent = false;
    }
  }
  applyTheme(dark);

  let frameN = 0;
  function frame() {
    raf = requestAnimationFrame(frame);
    if (paused) return;
    const dt = clock.getDelta();
    elapsed += dt;
    frameN++;

    // 0) nebula evolves slowly — refresh its buffer every 3rd frame
    if (frameN % 3 === 1) {
      nebulaMat.uniforms.uT.value = elapsed;
      renderer.setRenderTarget(nebulaRT);
      renderer.clear();
      renderer.render(nebulaScene, screenCam);
      renderer.setRenderTarget(null);
    }

    // 1) fade + advect previous trail into the other buffer
    fadeMat.uniforms.uPrev.value = rtA.texture;
    fadeMat.uniforms.uT.value = elapsed;
    renderer.setRenderTarget(rtB);
    renderer.clear();
    renderer.render(fadeScene, screenCam);

    // 2) paint this frame's stroke segment (into rtB)
    paint();

    // swap
    const tmp = rtA; rtA = rtB; rtB = tmp;

    // 3) particles drift (strided subset per frame)
    const attr = pGeo.getAttribute("position");
    const stride = 3;
    const offset = Math.floor(elapsed * 60) % stride;
    for (let i = offset; i < PARTICLES; i += stride) {
      const x = attr.array[i * 3];
      const z = attr.array[i * 3 + 2];
      attr.array[i * 3 + 1] += Math.sin(elapsed * 0.5 + seeds[i] + x * 0.25 + z * 0.18) * 0.0016;
    }
    attr.needsUpdate = true;
    points.rotation.y += (pointer3.x * 0.05 + elapsed * 0.008 - points.rotation.y) * 0.03;
    points.rotation.x += (-pointer3.y * 0.03 - points.rotation.x) * 0.03;
    pMat.opacity += ((isDark ? 0.3 : 0.18) - pMat.opacity) * 0.04;

    // 4) composite to screen
    renderer.setRenderTarget(null);
    renderer.clear();
    compMat.uniforms.uTrail.value = rtA.texture;
    compMat.uniforms.uT.value = elapsed;
    if (isDark) {
      // nebula → particles → glowing ribbons
      renderer.render(nebulaCompScene, screenCam);
      renderer.render(pScene, perspCam);
      renderer.render(compScene, screenCam);
    } else {
      // opaque silk base → mint wisps (page sees it through multiply)
      renderer.render(compScene, screenCam);
      renderer.render(nebulaCompScene, screenCam);
    }
  }
  frame();

  return {
    setTheme(nextDark) {
      applyTheme(nextDark);
    },
    pause() { paused = true; clock.stop(); },
    resume() { paused = false; clock.start(); },
    dispose() {
      cancelAnimationFrame(raf);
      window.removeEventListener("pointermove", onPointer);
      window.removeEventListener("resize", setSize);
      rtA.dispose(); rtB.dispose(); nebulaRT.dispose();
      pGeo.dispose(); pMat.dispose(); sprite.dispose();
      fadeMat.dispose(); brushMat.dispose(); compMat.dispose();
      nebulaMat.dispose(); nebulaCompMat.dispose();
      renderer.dispose();
      renderer.domElement.remove();
    },
  };
}
