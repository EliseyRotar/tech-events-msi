/**
 * TECH DRAGONS EVENTS — WebGL Hero Background (optimised)
 *
 * Domain-warped FBM fluid in cyan/blue palette.
 * Rendered at 35 % of screen resolution then scaled up by CSS —
 * completely invisible on a smooth fluid but gives ~8× speedup.
 *
 * Pixel cost breakdown vs v1:
 *   resolution : 0.35² = ~8× fewer pixels
 *   octaves    : 4 instead of 6  (−33 %)
 *   warp layers: 1 instead of 2  (q only, skip r)
 *   aurora     : pure trig, zero fbm calls
 *   total      : roughly 15–20× faster than v1
 */

(function () {
  'use strict';

  /* ── Render resolution scale ──────────────────────────────
     Fluid detail is invisible above ~0.4; browser bilinear
     upscale fills the rest.                                  */
  const RENDER_SCALE = 0.35;

  /* ══════════════════════════════════════════════════════════
     VERTEX SHADER
  ══════════════════════════════════════════════════════════ */
  const VERT = /* glsl */`
    attribute vec2 a_pos;
    void main() { gl_Position = vec4(a_pos, 0.0, 1.0); }
  `;

  /* ══════════════════════════════════════════════════════════
     FRAGMENT SHADER
  ══════════════════════════════════════════════════════════ */
  const FRAG = /* glsl */`
    precision mediump float;

    uniform float u_time;
    uniform vec2  u_res;
    uniform vec2  u_mouse;
    uniform vec2  u_click_pos;
    uniform float u_click_t;

    /* ── Fast value noise (no trig in hash) ─────────────── */
    float hash(vec2 p) {
      p = fract(p * vec2(234.34, 435.345));
      p += dot(p, p + 34.23);
      return fract(p.x * p.y);
    }

    float vnoise(vec2 p) {
      vec2 i = floor(p);
      vec2 f = fract(p);
      vec2 u = f * f * (3.0 - 2.0 * f);
      return mix(
        mix(hash(i),             hash(i + vec2(1,0)), u.x),
        mix(hash(i + vec2(0,1)), hash(i + vec2(1,1)), u.x),
        u.y
      );
    }

    /* ── FBM — 4 octaves (was 6) ────────────────────────── */
    float fbm(vec2 p) {
      float v = 0.0, a = 0.5;
      for (int i = 0; i < 4; i++) {
        v += a * vnoise(p);
        p  = p * 2.1 + vec2(1.7, 9.2);   /* offset breaks symmetry */
        a *= 0.5;
      }
      return v;
    }

    /* ── Color ramp ─────────────────────────────────────── */
    vec3 ramp(float t) {
      vec3 col = vec3(0.01, 0.01, 0.04);
      col = mix(col, vec3(0.00, 0.07, 0.22), smoothstep(0.00, 0.30, t));
      col = mix(col, vec3(0.00, 0.24, 0.56), smoothstep(0.28, 0.55, t));
      col = mix(col, vec3(0.00, 0.62, 0.90), smoothstep(0.52, 0.78, t));
      col = mix(col, vec3(0.00, 0.83, 1.00), smoothstep(0.76, 1.00, t));
      return col;
    }

    void main() {
      vec2 uv = gl_FragCoord.xy / u_res;
      float ar = u_res.x / u_res.y;

      vec2 st  = uv - 0.5;
      st.x    *= ar;

      float t = u_time * 0.10;

      /* ── Mouse ─────────────────────────────────────── */
      vec2  m    = (u_mouse - 0.5) * vec2(ar, 1.0);
      float mD   = length(st - m);
      float mG   = exp(-mD * mD * 4.5);

      /* ── Single domain-warp (q) ─────────────────── */
      vec2 q;
      q.x = fbm(st + t);
      q.y = fbm(st + vec2(5.2, 1.3) + t);

      /* Mouse gently pulls the warp field */
      q  += (m - st) / (mD * mD + 0.06) * mG * 0.04;

      float f = fbm(st + 2.2 * q + t * 0.55);
      f = clamp(f * 1.35 + 0.12, 0.0, 1.0);

      vec3 col = ramp(f);

      /* ── Aurora ribbons — pure sin, zero fbm ────── */
      float aurora = 0.0;
      for (int i = 0; i < 3; i++) {
        float fi  = float(i);
        float yc  = -0.08 + fi * 0.20
                  + sin(t * 0.28 + fi * 2.09) * 0.07;
        float band = exp(-pow(st.y - yc, 2.0) * 55.0);
        /* Horizontal ripple driven by q instead of fbm */
        float wave = sin(st.x * 2.8 + fi * 1.9 + t * 0.7 + q.x * 3.0)
                   * 0.5 + 0.5;
        aurora += band * wave * (0.22 - fi * 0.03);
      }
      col += vec3(0.00, 0.52, 0.88) * aurora * 0.45;

      /* ── Mouse halo ─────────────────────────────── */
      col += vec3(0.00, 0.70, 1.00) * mG * 0.22;
      col += vec3(0.45, 0.95, 1.00) * exp(-mD * mD * 55.0) * 0.30;

      /* ── Click shockwave ────────────────────────── */
      float el = u_time - u_click_t;
      if (el > 0.0 && el < 2.6) {
        vec2  cp = (u_click_pos - 0.5) * vec2(ar, 1.0);
        float cD = length(st - cp);
        float rg = exp(-pow(cD - el * 0.42, 2.0) * 280.0)
                 * exp(-el * 1.9);
        col += vec3(0.00, 0.83, 1.00) * rg * 4.5;
        col += vec3(0.60, 1.00, 1.00)
             * exp(-cD * 12.0) * exp(-el * 5.0) * 2.5;
      }

      /* ── Vignette ───────────────────────────────── */
      col *= 1.0 - smoothstep(0.30, 1.10, length(uv - 0.5) * 1.95);

      gl_FragColor = vec4(col * 0.42, 1.0);
    }
  `;

  /* ══════════════════════════════════════════════════════════
     BOOT
  ══════════════════════════════════════════════════════════ */
  function init() {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;

    const gl = canvas.getContext('webgl', {
      antialias:       false,
      alpha:           false,
      depth:           false,
      stencil:         false,
      powerPreference: 'high-performance',
    });

    if (!gl) {
      canvas.style.display = 'none';
      const fb = document.querySelector('.mesh-fallback');
      if (fb) fb.style.display = 'block';
      return;
    }

    /* Compile & link */
    function mkShader(type, src) {
      const s = gl.createShader(type);
      gl.shaderSource(s, src);
      gl.compileShader(s);
      if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
        console.error('[hero-bg] shader:', gl.getShaderInfoLog(s));
        return null;
      }
      return s;
    }

    const vert = mkShader(gl.VERTEX_SHADER,   VERT);
    const frag = mkShader(gl.FRAGMENT_SHADER, FRAG);
    if (!vert || !frag) return;

    const prog = gl.createProgram();
    gl.attachShader(prog, vert);
    gl.attachShader(prog, frag);
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
      console.error('[hero-bg] link:', gl.getProgramInfoLog(prog));
      return;
    }
    gl.useProgram(prog);

    /* Fullscreen quad */
    const vb = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, vb);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
      -1,-1,  1,-1,  -1, 1,
      -1, 1,  1,-1,   1, 1,
    ]), gl.STATIC_DRAW);

    const aPos = gl.getAttribLocation(prog, 'a_pos');
    gl.enableVertexAttribArray(aPos);
    gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);

    /* Uniforms */
    const uTime  = gl.getUniformLocation(prog, 'u_time');
    const uRes   = gl.getUniformLocation(prog, 'u_res');
    const uMouse = gl.getUniformLocation(prog, 'u_mouse');
    const uCPos  = gl.getUniformLocation(prog, 'u_click_pos');
    const uCTime = gl.getUniformLocation(prog, 'u_click_t');

    const hero = canvas.closest('.hero') || canvas.parentElement;

    /* State */
    let mouseX = 0.5, mouseY = 0.5;
    let smX    = 0.5, smY    = 0.5;
    let clickX = 0.5, clickY = 0.5;
    let clickT = -10.0;
    const t0   = performance.now();

    /* Resize — render at RENDER_SCALE, CSS fills the rest */
    function resize() {
      const w = hero.clientWidth  || window.innerWidth;
      const h = hero.clientHeight || window.innerHeight;
      canvas.width  = Math.max(1, Math.round(w * RENDER_SCALE));
      canvas.height = Math.max(1, Math.round(h * RENDER_SCALE));
      gl.viewport(0, 0, canvas.width, canvas.height);
    }
    resize();
    window.addEventListener('resize', resize, { passive: true });

    /* Input */
    function setMouse(nx, ny) { mouseX = nx; mouseY = ny; }

    hero.addEventListener('mousemove', (e) => {
      const r = hero.getBoundingClientRect();
      setMouse((e.clientX - r.left) / r.width,
               1.0 - (e.clientY - r.top)  / r.height);
    }, { passive: true });

    hero.addEventListener('touchmove', (e) => {
      const tc = e.touches[0];
      const r  = hero.getBoundingClientRect();
      setMouse((tc.clientX - r.left) / r.width,
               1.0 - (tc.clientY - r.top) / r.height);
    }, { passive: true });

    hero.addEventListener('click', (e) => {
      const r = hero.getBoundingClientRect();
      clickX  = (e.clientX - r.left) / r.width;
      clickY  = 1.0 - (e.clientY - r.top) / r.height;
      clickT  = (performance.now() - t0) / 1000.0;
    });

    /* Render loop */
    function render() {
      const now = (performance.now() - t0) / 1000.0;

      /* Lazy lerp — cursor lag feel */
      smX += (mouseX - smX) * 0.07;
      smY += (mouseY - smY) * 0.07;

      gl.uniform1f(uTime,  now);
      gl.uniform2f(uRes,   canvas.width, canvas.height);
      gl.uniform2f(uMouse, smX, smY);
      gl.uniform2f(uCPos,  clickX, clickY);
      gl.uniform1f(uCTime, clickT);

      gl.drawArrays(gl.TRIANGLES, 0, 6);
      requestAnimationFrame(render);
    }
    render();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
