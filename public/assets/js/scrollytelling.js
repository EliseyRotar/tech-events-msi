/* ============================================================
   TECH DRAGONS EVENTS — Scrollytelling Story Section
   Three.js particle morph driven by GSAP ScrollTrigger.
   Particles transition through 5 shapes:
     0  Primordial cloud      (Act 1 — the spark)
     1  Globe                 (Act 2 — the network)
     2  Arena (concentric)    (Act 3 — scale)
     3  Tournament bracket    (Act 4 transition)
     4  TD emblem (logo)      (Act 4 climax)
   The pointer rotates the field; the scene also breathes by itself.
   ============================================================ */

(function () {
  'use strict';

  /* ── Guards ──────────────────────────────────────────── */
  const section = document.querySelector('.scrollytell');
  const canvas  = document.getElementById('story-canvas');
  if (!section || !canvas) return;

  function ready() {
    if (typeof THREE === 'undefined' ||
        typeof gsap  === 'undefined' ||
        typeof ScrollTrigger === 'undefined') {
      // Libs loaded with `defer` — wait for window load
      return false;
    }
    return true;
  }

  function start() {
    if (!ready()) return;
    gsap.registerPlugin(ScrollTrigger);

    const PARTICLE_COUNT = 4000;
    const ACT_COUNT      = 4;

    /* ── Renderer / Scene / Camera ─────────────────────── */
    const renderer = new THREE.WebGLRenderer({
      canvas,
      alpha: true,
      antialias: window.devicePixelRatio < 2,
      powerPreference: 'high-performance',
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);

    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(58, 1, 0.1, 100);
    camera.position.set(0, 0, 8.5);

    function resize() {
      const rect = canvas.getBoundingClientRect();
      const w = Math.max(1, rect.width);
      const h = Math.max(1, rect.height);
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    /* ── Shape generators (each returns Float32Array N*3) ── */
    function makeCloud(n) {
      const a = new Float32Array(n * 3);
      for (let i = 0; i < n; i++) {
        // Soft spherical fog
        const r = 1.6 + Math.pow(Math.random(), 0.55) * 2.6;
        const theta = Math.random() * Math.PI * 2;
        const phi   = Math.acos(2 * Math.random() - 1);
        a[i*3  ] = r * Math.sin(phi) * Math.cos(theta);
        a[i*3+1] = r * Math.sin(phi) * Math.sin(theta);
        a[i*3+2] = r * Math.cos(phi) * 0.6;
      }
      return a;
    }

    function makeGlobe(n) {
      const a = new Float32Array(n * 3);
      const R = 3.1;
      for (let i = 0; i < n; i++) {
        // Fibonacci sphere for even distribution
        const phi   = Math.acos(1 - 2 * (i + 0.5) / n);
        const theta = Math.PI * (1 + Math.sqrt(5)) * (i + 0.5);
        // sparse latitudinal rings (continents-ish)
        const ringMask = 0.5 + 0.5 * Math.cos(phi * 14.0 + Math.sin(theta * 3));
        const r = R + (ringMask - 0.5) * 0.18 + (Math.random() - 0.5) * 0.04;
        a[i*3  ] = r * Math.sin(phi) * Math.cos(theta);
        a[i*3+1] = r * Math.sin(phi) * Math.sin(theta);
        a[i*3+2] = r * Math.cos(phi);
      }
      return a;
    }

    function makeArena(n) {
      // Tiered amphitheater: small central platform, 11 raked rings around
      const a = new Float32Array(n * 3);
      for (let i = 0; i < n; i++) {
        const t = i / n;
        // weight outer rings (more seats further out, like a real arena)
        const ringT = Math.pow(Math.random(), 0.7);
        const ring  = Math.floor(ringT * 11);
        const radius = 0.6 + ring * 0.32;
        const angle  = Math.random() * Math.PI * 2;
        const tier   = ring === 0 ? 0 : 0.08 + ring * 0.14;
        const y      = -1.6 + tier;
        const jitter = (Math.random() - 0.5) * 0.06;
        a[i*3  ] = radius * Math.cos(angle);
        a[i*3+1] = y + jitter;
        a[i*3+2] = radius * Math.sin(angle) * 0.85;
      }
      return a;
    }

    function makeBracket(n) {
      // 5-round bracket: 32 → 16 → 8 → 4 → 2 → 1
      const a = new Float32Array(n * 3);
      const rounds = [32, 16, 8, 4, 2, 1];
      const nodes  = [];
      const ySpan  = 5.0;
      rounds.forEach((count, ri) => {
        const x = -3.6 + (ri / (rounds.length - 1)) * 7.2;
        for (let m = 0; m < count; m++) {
          const y = count === 1 ? 0 : -ySpan/2 + (m / (count - 1)) * ySpan;
          nodes.push([x, y, 0]);
        }
      });
      for (let i = 0; i < n; i++) {
        const node = nodes[i % nodes.length];
        const r = Math.random() * 0.16;
        const ang = Math.random() * Math.PI * 2;
        a[i*3  ] = node[0] + r * Math.cos(ang);
        a[i*3+1] = node[1] + r * Math.sin(ang);
        a[i*3+2] = node[2] + (Math.random() - 0.5) * 0.35;
      }
      return a;
    }

    function makeEmblem(n) {
      // Stylized "TD" — half the particles spell T, half spell D
      const a = new Float32Array(n * 3);
      const half = Math.floor(n / 2);
      for (let i = 0; i < n; i++) {
        let x, y;
        const isT = i < half;
        if (isT) {
          // T: top horizontal bar + vertical stem
          if (Math.random() < 0.42) {
            // crossbar
            x = -2.1 + Math.random() * 2.0;
            y = 1.35 + (Math.random() - 0.5) * 0.35;
          } else {
            // stem
            x = -1.18 + (Math.random() - 0.5) * 0.32;
            y = -1.7 + Math.random() * 3.1;
          }
        } else {
          // D: left vertical + right semicircle arc
          const t = Math.random();
          if (t < 0.28) {
            // left vertical
            x = 0.35 + (Math.random() - 0.5) * 0.32;
            y = -1.55 + Math.random() * 3.0;
          } else {
            // semicircle right side
            const ang = (Math.random() - 0.5) * Math.PI; // -π/2..π/2
            const rr  = 1.55 + (Math.random() - 0.5) * 0.18;
            x = 0.45 + Math.cos(ang) * rr * 0.9;
            y = Math.sin(ang) * rr * 1.05;
          }
        }
        const z = (Math.random() - 0.5) * 0.45;
        a[i*3  ] = x;
        a[i*3+1] = y;
        a[i*3+2] = z;
      }
      return a;
    }

    const shapes = [
      makeCloud(PARTICLE_COUNT),
      makeGlobe(PARTICLE_COUNT),
      makeArena(PARTICLE_COUNT),
      makeBracket(PARTICLE_COUNT),
      makeEmblem(PARTICLE_COUNT),
    ];

    /* ── Particle geometry / material ──────────────────── */
    const geometry  = new THREE.BufferGeometry();
    const positions = new Float32Array(PARTICLE_COUNT * 3);
    positions.set(shapes[0]);
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    // Per-particle colors (blue → white gradient based on radius)
    const colors = new Float32Array(PARTICLE_COUNT * 3);
    for (let i = 0; i < PARTICLE_COUNT; i++) {
      const tone = Math.random();
      if (tone < 0.78) {
        // Cyan-blue majority
        colors[i*3  ] = 0.0;
        colors[i*3+1] = 0.83 + tone * 0.12;
        colors[i*3+2] = 1.0;
      } else if (tone < 0.94) {
        // Soft white
        colors[i*3  ] = 0.85;
        colors[i*3+1] = 0.95;
        colors[i*3+2] = 1.0;
      } else {
        // Hot magenta accents (rare sparks)
        colors[i*3  ] = 0.95;
        colors[i*3+1] = 0.25;
        colors[i*3+2] = 0.7;
      }
    }
    geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    // Circular sprite texture for soft particles
    const spriteTex = (function () {
      const size = 64;
      const cnv  = document.createElement('canvas');
      cnv.width = cnv.height = size;
      const ctx  = cnv.getContext('2d');
      const g    = ctx.createRadialGradient(size/2, size/2, 0, size/2, size/2, size/2);
      g.addColorStop(0.0,  'rgba(255,255,255,1)');
      g.addColorStop(0.35, 'rgba(255,255,255,0.55)');
      g.addColorStop(0.7,  'rgba(255,255,255,0.08)');
      g.addColorStop(1.0,  'rgba(255,255,255,0)');
      ctx.fillStyle = g;
      ctx.fillRect(0, 0, size, size);
      const tex = new THREE.CanvasTexture(cnv);
      tex.needsUpdate = true;
      return tex;
    })();

    const material = new THREE.PointsMaterial({
      size: 0.085,
      map: spriteTex,
      sizeAttenuation: true,
      transparent: true,
      opacity: 0.92,
      vertexColors: true,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
    });

    const points = new THREE.Points(geometry, material);
    scene.add(points);

    /* ── Soft glow halo behind particles (subtle disc) ── */
    const haloGeo = new THREE.SphereGeometry(2.6, 32, 32);
    const haloMat = new THREE.MeshBasicMaterial({
      color: 0x00d4ff,
      transparent: true,
      opacity: 0.04,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
      side: THREE.BackSide,
    });
    const halo = new THREE.Mesh(haloGeo, haloMat);
    scene.add(halo);

    /* ── State ─────────────────────────────────────────── */
    const state = {
      progress: 0,
      mouseTargetX: 0,
      mouseTargetY: 0,
      mouseSmoothX: 0,
      mouseSmoothY: 0,
      time: 0,
    };

    function easeInOut(t) {
      return t < 0.5 ? 2*t*t : 1 - Math.pow(-2*t + 2, 2) / 2;
    }

    function updateMorph(globalP) {
      const segments = shapes.length - 1; // 4 transitions
      const seg  = Math.min(Math.floor(globalP * segments), segments - 1);
      const segP = (globalP * segments) - seg;
      const t    = easeInOut(Math.min(1, Math.max(0, segP)));

      const A = shapes[seg];
      const B = shapes[seg + 1];
      const pos = geometry.attributes.position.array;

      // Add a tiny per-particle "shimmer" wave
      const tt = state.time * 0.6;
      for (let i = 0; i < PARTICLE_COUNT; i++) {
        const i3 = i * 3;
        const wob = Math.sin(tt + i * 0.013) * 0.02;
        pos[i3  ] = A[i3  ] + (B[i3  ] - A[i3  ]) * t + wob;
        pos[i3+1] = A[i3+1] + (B[i3+1] - A[i3+1]) * t + Math.cos(tt + i * 0.017) * 0.02;
        pos[i3+2] = A[i3+2] + (B[i3+2] - A[i3+2]) * t;
      }
      geometry.attributes.position.needsUpdate = true;

      // Halo grows during globe/arena, shrinks during emblem
      const haloTarget = 0.04 + Math.sin(globalP * Math.PI) * 0.06;
      haloMat.opacity += (haloTarget - haloMat.opacity) * 0.08;
    }

    /* ── Pointer parallax ──────────────────────────────── */
    section.addEventListener('mousemove', (e) => {
      const rect = section.getBoundingClientRect();
      const x = ((e.clientX - rect.left) / rect.width)  * 2 - 1;
      // Use viewport-relative Y so pinned canvas always tracks cursor
      const y = ((e.clientY) / window.innerHeight) * 2 - 1;
      state.mouseTargetX = x * 0.45;
      state.mouseTargetY = y * 0.30;
    }, { passive: true });

    // Touch parallax — use first touch
    section.addEventListener('touchmove', (e) => {
      if (!e.touches || !e.touches.length) return;
      const t = e.touches[0];
      const rect = section.getBoundingClientRect();
      const x = ((t.clientX - rect.left) / rect.width)  * 2 - 1;
      const y = ((t.clientY) / window.innerHeight) * 2 - 1;
      state.mouseTargetX = x * 0.45;
      state.mouseTargetY = y * 0.30;
    }, { passive: true });

    /* ── ScrollTrigger: drive morph + progress + act num ── */
    const fillEl  = section.querySelector('.story-progress-fill');
    const numEl   = section.querySelector('#story-act-num');

    ScrollTrigger.create({
      trigger: section,
      start:   'top top',
      end:     'bottom bottom',
      onUpdate: (self) => {
        state.progress = self.progress;
        if (fillEl) fillEl.style.transform = `scaleX(${self.progress})`;
        if (numEl) {
          const a = Math.min(ACT_COUNT, Math.floor(self.progress * ACT_COUNT) + 1);
          if (numEl.textContent !== String(a)) numEl.textContent = a;
        }
      },
    });

    /* ── Per-act text scrub: fade in / out as it crosses ── */
    section.querySelectorAll('.story-act').forEach((act) => {
      const text = act.querySelector('.story-text');
      if (!text) return;
      gsap.fromTo(text,
        { opacity: 0, y: 60 },
        {
          opacity: 1, y: 0, ease: 'none',
          scrollTrigger: {
            trigger: act,
            start: 'top 70%',
            end:   'top 25%',
            scrub: 0.5,
          },
        }
      );
      gsap.to(text, {
        opacity: 0, y: -45, ease: 'none',
        scrollTrigger: {
          trigger: act,
          start: 'bottom 70%',
          end:   'bottom 25%',
          scrub: 0.5,
        },
      });
    });

    /* ── Render loop ───────────────────────────────────── */
    const clock = new THREE.Clock();
    function tick() {
      const dt = Math.min(0.05, clock.getDelta());
      state.time += dt;

      // Smooth mouse lerp
      state.mouseSmoothX += (state.mouseTargetX - state.mouseSmoothX) * 0.06;
      state.mouseSmoothY += (state.mouseTargetY - state.mouseSmoothY) * 0.06;

      // Always update morph (so the shimmer animates even between scrolls)
      updateMorph(state.progress);

      // Combined rotation: mouse + slow ambient drift + scroll spin
      points.rotation.x = state.mouseSmoothY + Math.sin(state.time * 0.18) * 0.04;
      points.rotation.y = state.mouseSmoothX + state.time * 0.06 + state.progress * Math.PI * 0.3;
      halo.rotation.copy(points.rotation);

      // Camera ease-in: pull closer as the story progresses
      const targetZ = 8.5 - state.progress * 2.2;
      camera.position.z += (targetZ - camera.position.z) * 0.08;
      camera.position.x += (state.mouseSmoothX * 0.6 - camera.position.x) * 0.05;

      renderer.render(scene, camera);
      requestAnimationFrame(tick);
    }
    tick();
  }

  // GSAP + Three.js load with `defer`; start once DOM + scripts are in.
  if (document.readyState === 'complete') {
    start();
  } else {
    window.addEventListener('load', start);
  }
})();
