/* ============================================================
   TECH DRAGONS EVENTS — Main JS
   GSAP + ScrollTrigger + Custom Cursor + Interactions
   ============================================================ */

(function () {
  'use strict';

  /* ── Page Load Overlay ─────────────────────────────────── */
  const overlay = document.getElementById('page-overlay');
  if (overlay) {
    window.addEventListener('load', () => {
      overlay.classList.add('hidden');
      setTimeout(() => overlay.remove(), 900);
    });
  }

  /* ── Custom Cursor ─────────────────────────────────────── */
  const cursorDot  = document.getElementById('cursor-dot');
  const cursorRing = document.getElementById('cursor-ring');

  if (cursorDot && cursorRing) {
    let mouseX = -100, mouseY = -100;
    let ringX  = -100, ringY  = -100;
    let raf;

    document.addEventListener('mousemove', (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;
      cursorDot.style.left = mouseX + 'px';
      cursorDot.style.top  = mouseY + 'px';
    });

    document.addEventListener('mouseleave', () => {
      cursorDot.style.opacity  = '0';
      cursorRing.style.opacity = '0';
    });
    document.addEventListener('mouseenter', () => {
      cursorDot.style.opacity  = '1';
      cursorRing.style.opacity = '1';
    });

    // Ring follows with lerp lag
    function animateCursor() {
      const speed = 0.12;
      ringX += (mouseX - ringX) * speed;
      ringY += (mouseY - ringY) * speed;
      cursorRing.style.left = ringX + 'px';
      cursorRing.style.top  = ringY + 'px';
      raf = requestAnimationFrame(animateCursor);
    }
    animateCursor();

    // Scale dot on interactive elements
    const interactives = document.querySelectorAll('a, button, [role="button"], input, select, textarea, .filter-tab, .event-card');
    interactives.forEach(el => {
      el.addEventListener('mouseenter', () => {
        cursorDot.style.transform  = 'translate(-50%,-50%) scale(2.5)';
        cursorDot.style.opacity    = '0.6';
      });
      el.addEventListener('mouseleave', () => {
        cursorDot.style.transform  = 'translate(-50%,-50%) scale(1)';
        cursorDot.style.opacity    = '1';
      });
    });
  }

  /* ── Navbar Scroll Behavior ────────────────────────────── */
  const navbar = document.getElementById('navbar');
  if (navbar) {
    const hero = document.querySelector('.hero');
    const heroBottom = hero ? hero.offsetHeight - 100 : 200;

    let ticking = false;
    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          if (window.scrollY > 60) {
            navbar.classList.add('scrolled');
          } else {
            navbar.classList.remove('scrolled');
          }
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    // Always scrolled on non-hero pages
    if (!hero) navbar.classList.add('scrolled');
  }

  /* ── Hamburger Menu ────────────────────────────────────── */
  const hamburger   = document.getElementById('hamburger');
  const mobileMenu  = document.getElementById('mobile-menu');

  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', () => {
      const isOpen = hamburger.classList.toggle('open');
      mobileMenu.classList.toggle('open', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('open');
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }

  /* ── GSAP Initialization ───────────────────────────────── */
  if (typeof gsap === 'undefined') return;
  gsap.registerPlugin(ScrollTrigger);

  /* ── Hero Text Reveal ──────────────────────────────────── */
  const heroWords = document.querySelectorAll('.hero-word');
  if (heroWords.length > 0) {
    gsap.to(heroWords, {
      opacity: 1,
      y: 0,
      duration: 0.8,
      stagger: 0.06,
      ease: 'power3.out',
      delay: 0.8,
    });
  }

  /* ── Scroll Reveal: generic .reveal elements ───────────── */
  const revealEls = document.querySelectorAll('.reveal');
  revealEls.forEach(el => {
    ScrollTrigger.create({
      trigger: el,
      start: 'top 88%',
      onEnter: () => el.classList.add('visible'),
      once: true,
    });
  });

  /* ── Stats Counter Animation ───────────────────────────── */
  const statNumbers = document.querySelectorAll('[data-count]');
  statNumbers.forEach(el => {
    const target  = parseFloat(el.dataset.count);
    const prefix  = el.dataset.prefix  || '';
    const suffix  = el.dataset.suffix  || '';
    const decimal = el.dataset.decimal === 'true';

    ScrollTrigger.create({
      trigger: el,
      start: 'top 85%',
      once: true,
      onEnter: () => {
        gsap.fromTo(
          { val: 0 },
          { val: target,
            duration: 2,
            ease: 'power2.out',
            onUpdate: function () {
              const v = this.targets()[0].val;
              el.textContent = prefix + (decimal
                ? v.toFixed(1)
                : Math.round(v).toLocaleString()) + suffix;
            },
          }
        );
      },
    });
  });

  /* ── Events Section: Filter Tabs ──────────────────────── */
  const filterTabs  = document.querySelectorAll('.filter-tab');
  const eventCards  = document.querySelectorAll('.event-card');

  if (filterTabs.length && eventCards.length) {
    filterTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        filterTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const filter = tab.dataset.filter;

        eventCards.forEach((card, i) => {
          const type   = card.dataset.type || 'all';
          const show   = filter === 'all' || type === filter;
          const delay  = i * 0.04;

          if (show) {
            card.style.display = '';
            gsap.fromTo(card,
              { opacity: 0, y: 16 },
              { opacity: 1, y: 0, duration: 0.4, delay, ease: 'power2.out' }
            );
          } else {
            gsap.to(card, {
              opacity: 0, y: 8, duration: 0.25, ease: 'power2.in',
              onComplete: () => { card.style.display = 'none'; }
            });
          }
        });

        // Recalculate bento layout after filter
        relayoutBento();
      });
    });
  }

  function relayoutBento() {
    const grid = document.querySelector('.events-grid');
    if (!grid) return;
    const visible = [...grid.querySelectorAll('.event-card')]
      .filter(c => c.style.display !== 'none');
    visible.forEach((c, i) => {
      c.style.gridColumn = '';
    });
    // Apply bento pattern: every 3rd card starting from 1st gets span 2
    if (window.innerWidth > 1024) {
      visible.forEach((c, i) => {
        c.style.gridColumn = (i % 3 === 0) ? 'span 2' : 'span 1';
      });
    }
  }

  /* ── Section Reveal Animations ─────────────────────────── */
  // Stats bar
  gsap.from('.stat-item', {
    scrollTrigger: {
      trigger: '.stats-bar',
      start: 'top 80%',
      once: true,
    },
    opacity: 0,
    y: 30,
    stagger: 0.1,
    duration: 0.8,
    ease: 'power3.out',
  });

  // Events section header
  if (document.querySelector('.events-section')) {
    gsap.from('.events-title', {
      scrollTrigger: { trigger: '.events-section', start: 'top 80%', once: true },
      opacity: 0, y: 24, duration: 0.7, ease: 'power3.out',
    });
    gsap.from('.filter-tabs', {
      scrollTrigger: { trigger: '.events-section', start: 'top 80%', once: true },
      opacity: 0, y: 16, duration: 0.7, delay: 0.15, ease: 'power3.out',
    });
    gsap.from('.event-card', {
      scrollTrigger: { trigger: '.events-grid', start: 'top 85%', once: true },
      opacity: 0, y: 36, stagger: 0.08, duration: 0.7, ease: 'power3.out',
    });
  }

  /* ── About: Word-by-word light-up ─────────────────────── */
  const aboutWords = document.querySelectorAll('.about-word');
  if (aboutWords.length > 0) {
    ScrollTrigger.create({
      trigger: '.about-statement',
      start: 'top 80%',
      end: 'bottom 40%',
      scrub: 0.5,
      onUpdate: (self) => {
        const progress = self.progress;
        const litCount  = Math.floor(progress * aboutWords.length * 1.4);
        aboutWords.forEach((w, i) => {
          w.classList.toggle('lit', i < litCount);
        });
      },
    });
  }

  // Feature blocks slide in
  const featureBlocks = document.querySelectorAll('.feature-block');
  featureBlocks.forEach((block, i) => {
    ScrollTrigger.create({
      trigger: block,
      start: 'top 88%',
      once: true,
      onEnter: () => {
        gsap.to(block, {
          opacity: 1,
          x: 0,
          duration: 0.6,
          delay: i * 0.1,
          ease: 'power3.out',
        });
      },
    });
  });

  // Organizer cards
  gsap.from('.profile-card', {
    scrollTrigger: {
      trigger: '.organizers-grid',
      start: 'top 85%',
      once: true,
    },
    opacity: 0, y: 40, stagger: 0.1, duration: 0.7, ease: 'power3.out',
  });

  // Contact section
  if (document.querySelector('.contact-section')) {
    gsap.from('.contact-header', {
      scrollTrigger: { trigger: '.contact-section', start: 'top 80%', once: true },
      opacity: 0, y: 24, duration: 0.7, ease: 'power3.out',
    });
    gsap.from('.contact-form', {
      scrollTrigger: { trigger: '.contact-section', start: 'top 75%', once: true },
      opacity: 0, y: 32, duration: 0.8, delay: 0.2, ease: 'power3.out',
    });
  }

  /* ── Contact Form Submit Animation ─────────────────────── */
  const contactForm    = document.getElementById('contact-form');
  const formSuccess    = document.getElementById('form-success');

  if (contactForm && formSuccess) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const btn = contactForm.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Sending…';
      }

      // Simulate async send
      setTimeout(() => {
        gsap.to(contactForm, {
          opacity: 0, y: -20, duration: 0.4, ease: 'power2.in',
          onComplete: () => {
            contactForm.style.display = 'none';
            formSuccess.classList.add('visible');
            gsap.from(formSuccess, {
              opacity: 0, y: 20, duration: 0.6, ease: 'power3.out',
            });
          },
        });
      }, 800);
    });
  }

  /* ── Orb Mouse Parallax (hero only) ────────────────────── */
  const heroEl = document.querySelector('.hero');
  if (heroEl) {
    const orbs = heroEl.querySelectorAll('.mesh-orb');
    document.addEventListener('mousemove', (e) => {
      const cx = window.innerWidth  / 2;
      const cy = window.innerHeight / 2;
      const dx = (e.clientX - cx) / cx;
      const dy = (e.clientY - cy) / cy;

      orbs.forEach((orb, i) => {
        const depth = (i + 1) * 12;
        gsap.to(orb, {
          x: dx * depth,
          y: dy * depth,
          duration: 1.2,
          ease: 'power2.out',
          overwrite: 'auto',
        });
      });
    }, { passive: true });
  }

  /* ── Hover glow on event cards ─────────────────────────── */
  document.querySelectorAll('.event-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = ((e.clientX - rect.left) / rect.width)  * 100;
      const y = ((e.clientY - rect.top)  / rect.height) * 100;
      card.style.setProperty('--mouse-x', x + '%');
      card.style.setProperty('--mouse-y', y + '%');
    });
  });

})();
