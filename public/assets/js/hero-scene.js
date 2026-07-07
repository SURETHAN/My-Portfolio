/* One tasteful WebGL moment: a drifting particle field with a slow
   sine swell and cursor parallax. Scenery, not a tech demo. */

import * as THREE from "three";

const COUNT = 2400;

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
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));
  renderer.setSize(holder.clientWidth, holder.clientHeight);
  renderer.domElement.setAttribute("aria-hidden", "true");
  holder.prepend(renderer.domElement);

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(55, holder.clientWidth / holder.clientHeight, 0.1, 60);
  camera.position.set(0, 0.4, 7);

  const positions = new Float32Array(COUNT * 3);
  const seeds = new Float32Array(COUNT);
  const rng = mulberry32(42);
  for (let i = 0; i < COUNT; i++) {
    const r = Math.sqrt(rng()) * 11;
    const theta = rng() * Math.PI * 2;
    positions[i * 3] = Math.cos(theta) * r * 1.6;
    positions[i * 3 + 1] = (rng() - 0.42) * 5.2;
    positions[i * 3 + 2] = Math.sin(theta) * r - 4;
    seeds[i] = rng() * Math.PI * 2;
  }
  const geometry = new THREE.BufferGeometry();
  geometry.setAttribute("position", new THREE.BufferAttribute(positions, 3));

  const material = new THREE.PointsMaterial({
    size: 0.028,
    sizeAttenuation: true,
    transparent: true,
    opacity: 0,
    depthWrite: false,
    blending: THREE.AdditiveBlending,
    color: new THREE.Color(dark ? "#6ba3ff" : "#2563eb"),
  });
  const points = new THREE.Points(geometry, material);
  points.frustumCulled = false;
  scene.add(points);

  let isDark = dark;
  let paused = false;
  let raf = 0;
  const pointer = { x: 0, y: 0 };
  const clock = new THREE.Clock();
  let elapsed = 0;

  const onPointer = (e) => {
    pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
    pointer.y = -((e.clientY / window.innerHeight) * 2 - 1);
  };
  window.addEventListener("pointermove", onPointer, { passive: true });

  const onResize = () => {
    const w = holder.clientWidth, h = holder.clientHeight;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
  };
  window.addEventListener("resize", onResize);

  const attr = geometry.getAttribute("position");
  function frame() {
    raf = requestAnimationFrame(frame);
    if (paused) return;
    elapsed += clock.getDelta();
    const t = elapsed;

    // Animate a strided subset per frame — full-field look, third of the cost
    const stride = 3;
    const offset = Math.floor(t * 60) % stride;
    for (let i = offset; i < COUNT; i += stride) {
      const x = attr.array[i * 3];
      const z = attr.array[i * 3 + 2];
      attr.array[i * 3 + 1] += Math.sin(t * 0.5 + seeds[i] + x * 0.25 + z * 0.18) * 0.0016;
    }
    attr.needsUpdate = true;

    points.rotation.y += (pointer.x * 0.06 + t * 0.008 - points.rotation.y) * 0.03;
    points.rotation.x += (-pointer.y * 0.035 - points.rotation.x) * 0.03;
    material.opacity += ((isDark ? 0.52 : 0.34) - material.opacity) * 0.04;

    renderer.render(scene, camera);
  }
  frame();

  return {
    setTheme(nextDark) {
      isDark = nextDark;
      material.color.set(nextDark ? "#6ba3ff" : "#2563eb");
    },
    pause() { paused = true; clock.stop(); },
    resume() { paused = false; clock.start(); },
    dispose() {
      cancelAnimationFrame(raf);
      window.removeEventListener("pointermove", onPointer);
      window.removeEventListener("resize", onResize);
      geometry.dispose();
      material.dispose();
      renderer.dispose();
      renderer.domElement.remove();
    },
  };
}
