/* =====================================================================
   site.js: everything that moves or responds on the public page.
   Plain JavaScript, no libraries, no build step.

   Contents
     1. Helpers
     2. Plant drawings        (only used when a plant has no photograph)
     3. Reveal on first view  ([data-r], [data-words])
     4. Film scenes           (clips that follow the scroll, staged words,
                               weighted scrolling, pointer depth, card tilt)
     5. Top bar and phone menu
     6. Health areas          (hover preview, click to filter)
     7. Catalogue             (filter, search, sort)
     8. Product window
     9. Background video      (the export frame only)

   The page works with this file missing: all content is in the HTML
   and the CSS only hides things when <html> has the class "js".
   ===================================================================== */

(function () {
  'use strict';

  /* ---------- 1. Helpers ---------- */

  var doc = document.documentElement;
  var BN = doc.getAttribute('data-lang') === 'bn';
  var CALM = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var WIDE = window.matchMedia('(min-width: 901px)');
  var HOVER = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

  /* 0-9 become Bangla digits when the page is in Bangla */
  function digits(text) {
    text = String(text);
    if (!BN) return text;
    return text.replace(/[0-9]/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; });
  }
  function pad2(n) { return digits(n < 10 ? '0' + n : String(n)); }


  /* ---------- 2. Plant drawings ---------- */

  var NS = 'http://www.w3.org/2000/svg';

  function drawPlant(holder) {
    var plant = (typeof PLANTS !== 'undefined') && PLANTS[holder.getAttribute('data-plant')];
    if (!plant) return;
    var svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('viewBox', '30 30 340 350');
    var i = 0;
    [['fills', 'pf'], ['lines', 'pl'], ['veins', 'pv']].forEach(function (set) {
      (plant[set[0]] || []).forEach(function (d) {
        var path = document.createElementNS(NS, 'path');
        path.setAttribute('d', d);
        path.setAttribute('class', set[1]);
        if (set[1] !== 'pf') { path.setAttribute('pathLength', '1'); path.style.setProperty('--i', i++); }
        svg.appendChild(path);
      });
    });
    holder.appendChild(svg);
    /* frame each drawing snugly, whatever its shape */
    try {
      var b = svg.getBBox();
      if (b.width > 0) svg.setAttribute('viewBox', [b.x - 14, b.y - 14, b.width + 28, b.height + 28].join(' '));
    } catch (err) { /* not laid out yet: the default frame is fine */ }
  }
  $$('[data-plant]').forEach(drawPlant);


  /* ---------- 3. Reveal on first view ---------- */

  /* headlines are split into words so they can rise one after another */
  $$('[data-words]').forEach(function (el) {
    var words = el.textContent.trim().split(/\s+/);
    el.setAttribute('aria-label', el.textContent.trim());
    el.textContent = '';
    words.forEach(function (word, i) {
      var span = document.createElement('span');
      span.className = 'w';
      span.setAttribute('aria-hidden', 'true');
      span.style.setProperty('--i', i);
      span.textContent = word;
      el.appendChild(span);
      if (i < words.length - 1) el.appendChild(document.createTextNode(' '));
    });
  });

  function countUp(el) {
    var target = parseFloat(el.getAttribute('data-count')) || 0;
    if (CALM) return;
    var start = null, time = 1700;
    function frame(now) {
      if (start === null) start = now;
      var t = clamp((now - start) / time, 0, 1);
      var eased = 1 - Math.pow(1 - t, 4);
      el.textContent = digits(Math.round(target * eased).toLocaleString('en-US'));
      if (t < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  var seen = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      el.classList.add('in');
      seen.unobserve(el);
    });
  }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });

  $$('[data-r], [data-words]').forEach(function (el) { seen.observe(el); });

  /* the hero is on screen at once, so show it without waiting for a scroll */
  requestAnimationFrame(function () {
    $$('.hero [data-r], .hero [data-words]').forEach(function (el) { el.classList.add('in'); });
  });


  /* ---------- 4. Film scenes ---------- */

  /* How a scene works
     -----------------
     Every <section data-scene> is taller than the screen (its --len, in
     screens). Its .stage sticks to the screen while you scroll through.
     Each frame we work out, for every scene near the screen:

       p     0..1  position through the whole scene. The clip is set to
                   p x its length, so scrolling forwards plays it and
                   scrolling back rewinds it.
       fade  0..1  the scene dissolves in over the one before it during
                   its first FADE screens.
       q     0..1  position through the part where the words can be read:
                   after the fade in, before the next scene covers it.
       o     0..1  the words leave just before the next scene arrives.

     OVERLAP and FADE below must match the -160vh margin in site.css.    */

  var OVERLAP = 1.6;     // screens that each scene sits on top of the previous one
  var FADE = 0.6;        // screens over which a scene dissolves in
  var sceneEls = $$('[data-scene]');
  var FILM = sceneEls.length > 0 && !CALM;
  var frame = $('.frame');
  var vh = window.innerHeight, vw = window.innerWidth;
  var mx = 0, my = 0, tx = 0, ty = 0;   // pointer position, raw and eased

  var scenes = sceneEls.map(function (el, i) {
    var next = sceneEls[i + 1];
    return {
      el: el,
      video: $('video[data-src]', el),
      counts: $$('[data-count]', el),
      orbit: el.hasAttribute('data-orbit'),
      fades: i > 0 && !el.hasAttribute('data-solo'),
      covered: !!next && !next.hasAttribute('data-solo') && !el.hasAttribute('data-last') && !el.hasAttribute('data-solo'),
      top: 0, height: 0, time: 0, loaded: false, live: null, gone: null
    };
  });

  if (FILM) doc.classList.add('film-on');

  function measure() {
    vh = window.innerHeight; vw = window.innerWidth;
    var y = window.scrollY;
    scenes.forEach(function (s) {
      var r = s.el.getBoundingClientRect();
      s.top = r.top + y; s.height = r.height;
    });
  }

  /* fetch a clip shortly before it is needed, never all of them at once */
  function loadClip(s) {
    if (s.loaded || !s.video) return;
    s.loaded = true;
    var v = s.video;
    v.src = v.getAttribute('data-src');
    v.preload = 'auto';
    v.addEventListener('loadeddata', function () { v.classList.add('ready'); }, { once: true });
    v.load();
    /* phones only keep a clip in memory once it has been played */
    var kick = v.play();
    if (kick && kick.then) kick.then(function () { v.pause(); }).catch(function () {});
  }

  function tick() {
    var y = window.scrollY;
    tx += (mx - tx) * 0.07; ty += (my - ty) * 0.07;

    if (FILM) {
      scenes.forEach(function (s) {
        var into = y - s.top;                  // px scrolled into this scene
        var total = s.height - vh;             // px over which it stays pinned
        var near = into > -vh * 2.4 && into < s.height + vh * 0.6;
        var gone = !near;
        if (gone !== s.gone) { s.gone = gone; s.el.classList.toggle('gone', gone); }
        if (!near) { if (s.live) { s.live = false; s.el.classList.remove('live'); } return; }
        loadClip(s);

        var p = clamp(into / total, 0, 1);
        var fade = s.fades ? clamp(into / (FADE * vh), 0, 1) : 1;
        var start = s.fades ? (FADE * vh) / total : 0;
        var end = s.covered ? (s.height - OVERLAP * vh) / total : 1;
        var q = clamp((p - start) / (end - start), 0, 1);
        var o = s.covered ? clamp((end - p) * 14, 0, 1) : 1;
        var live = fade > 0.5 && (!s.covered || into < s.height - vh * (OVERLAP - FADE / 2));

        var st = s.el.style;
        st.setProperty('--p', p.toFixed(4));
        st.setProperty('--q', q.toFixed(4));
        st.setProperty('--o', o.toFixed(3));
        st.setProperty('--fade', fade.toFixed(3));
        st.setProperty('--mx', tx.toFixed(3));
        st.setProperty('--my', ty.toFixed(3));
        if (live !== s.live) { s.live = live; s.el.classList.toggle('live', live); }

        /* the clip follows the scroll, eased, and on plant scenes it also
           leans a little with the pointer so the plant seems to turn */
        var v = s.video;
        if (v && v.readyState >= 2 && v.duration) {
          var want = p * (v.duration - 0.08) + (s.orbit ? tx * 0.5 : 0);
          want = clamp(want, 0, v.duration - 0.05);
          s.time += (want - s.time) * 0.14;
          if (!v.seeking && Math.abs(v.currentTime - s.time) > 0.012) v.currentTime = s.time;
        }

        /* figures count up as their line arrives, once */
        s.counts.forEach(function (el) {
          if (el._done || q < parseFloat(el.getAttribute('data-at') || 0) + 0.03) return;
          el._done = true; countUp(el);
        });
      });
    }

    /* export picture drifts slightly inside its frame */
    if (frame && !CALM) {
      var f = frame.getBoundingClientRect();
      if (f.bottom > 0 && f.top < vh) frame.style.setProperty('--py', (((f.top + f.height / 2) / vh - 0.5) * -34).toFixed(1));
    }

    requestAnimationFrame(tick);
  }

  var resizeTimer;
  window.addEventListener('resize', function () { clearTimeout(resizeTimer); resizeTimer = setTimeout(measure, 150); });
  window.addEventListener('load', measure);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure);
  measure();
  requestAnimationFrame(tick);

  if (HOVER) {
    window.addEventListener('pointermove', function (e) {
      mx = e.clientX / vw - 0.5; my = e.clientY / vh - 0.5;
    }, { passive: true });
  }

  /* Weighted scrolling (mouse wheel on a computer only).
     The wheel sets a destination and the page glides to it, which is what
     makes the camera moves feel smooth instead of stepped. Touch screens,
     trackpad momentum, the keyboard and the scrollbar are left alone. */
  if (FILM && HOVER) {
    var dest = window.scrollY, gliding = false;

    function glide() {
      if (!gliding) return;
      var y = window.scrollY, gap = dest - y;
      if (Math.abs(gap) < 0.6) { gliding = false; return; }
      window.scrollTo({ top: y + gap * 0.11, behavior: 'instant' });
      requestAnimationFrame(glide);
    }

    window.addEventListener('wheel', function (e) {
      if (e.ctrlKey || e.defaultPrevented) return;                       // pinch zoom
      if (doc.classList.contains('menu-open')) return;
      if (e.target.closest && e.target.closest('dialog, .chips')) return; // let these scroll themselves
      if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
      e.preventDefault();
      var step = e.deltaMode === 1 ? e.deltaY * 32 : e.deltaMode === 2 ? e.deltaY * vh : e.deltaY;
      if (!gliding) dest = window.scrollY;
      dest = clamp(dest + step, 0, document.documentElement.scrollHeight - vh);
      if (!gliding) { gliding = true; requestAnimationFrame(glide); }
    }, { passive: false });

    /* a menu link or the keyboard moved the page: stop gliding and follow */
    ['keydown', 'pointerdown'].forEach(function (name) {
      window.addEventListener(name, function () { gliding = false; }, { passive: true });
    });
  }

  /* Cards lean towards the pointer, with a highlight that follows it */
  if (HOVER && !CALM) {
    $$('[data-tilt], .item button').forEach(function (card) {
      var target = card.hasAttribute('data-tilt') ? card : $('.shot', card);
      if (!target) return;
      card.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
        target.style.setProperty('--ry', ((px - 0.5) * 12).toFixed(2) + 'deg');
        target.style.setProperty('--rx', ((0.5 - py) * 10).toFixed(2) + 'deg');
        target.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
        target.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
        target.style.setProperty('--g', '1');
      });
      card.addEventListener('pointerleave', function () {
        ['--rx', '--ry'].forEach(function (k) { target.style.setProperty(k, '0deg'); });
        target.style.setProperty('--g', '0');
      });
    });
  }


  /* ---------- 5. Top bar and phone menu ---------- */

  var nav = $('#nav');
  var burger = $('.burger');
  var lastY = 0;

  /* the bar takes light or dark text from whichever section is under it */
  var toneWatch = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) doc.setAttribute('data-nav', entry.target.getAttribute('data-tone'));
    });
  }, { rootMargin: '-34px 0px -' + (window.innerHeight - 36) + 'px 0px' });
  $$('[data-tone]').forEach(function (s) { toneWatch.observe(s); });

  /* the bar slides away going down and returns going up */
  window.addEventListener('scroll', function () {
    var y = window.scrollY;
    nav.classList.toggle('stuck', y > 24);
    if (!doc.classList.contains('menu-open')) {
      var hide = y > lastY + 2 && y > vh * 0.9;
      nav.classList.toggle('hide', hide);
      doc.classList.toggle('nav-hidden', hide);   // lets the filter bar move up into the gap
    }
    lastY = y;
  }, { passive: true });

  /* which menu link matches the section on screen */
  var menuLinks = $$('.links a');
  var here = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      menuLinks.forEach(function (a) {
        var id = entry.target.id.indexOf('plant-') === 0 ? 'materia' : entry.target.id;
        if (a.getAttribute('href') === '#' + id) a.setAttribute('aria-current', 'true');
        else a.removeAttribute('aria-current');
      });
    });
  }, { rootMargin: '-45% 0px -50% 0px' });
  $$('main > section[id]').forEach(function (s) { here.observe(s); });

  function setMenu(open) {
    doc.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    burger.firstElementChild.textContent = burger.getAttribute(open ? 'data-close' : 'data-open');
  }
  if (burger) {
    burger.addEventListener('click', function () { setMenu(!doc.classList.contains('menu-open')); });
    menuLinks.forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });
    WIDE.addEventListener('change', function () { setMenu(false); });
  }


  /* ---------- 6. Health areas ---------- */

  var peek = $('#peek');
  var peekImg = peek && $('img', peek);

  $$('#areaIndex a').forEach(function (a) {
    a.addEventListener('click', function () { setArea(a.getAttribute('data-area')); });
    if (!HOVER || !peek) return;
    a.addEventListener('pointerenter', function () {
      var src = a.getAttribute('data-img');
      if (!src) return;
      peekImg.src = src;
      peek.classList.add('on');
    });
    a.addEventListener('pointerleave', function () { peek.classList.remove('on'); });
    a.addEventListener('pointermove', function (e) {
      peek.style.setProperty('--px', clamp(e.clientX + 28, 8, vw - 250) + 'px');
      peek.style.setProperty('--py', clamp(e.clientY - 90, 8, vh - 170) + 'px');
    });
  });


  /* ---------- 7. Catalogue ---------- */

  var grid = $('#grid');
  var items = $$('.item', grid);
  var chips = $$('.chip');
  var search = $('#search');
  var sort = $('#sort');
  var count = $('#count');
  var empty = $('#empty');
  var state = { area: '', text: '' };

  function apply() {
    var words = state.text.toLowerCase().split(/\s+/).filter(Boolean);
    var shown = 0;
    items.forEach(function (item) {
      var ok = !state.area || item.getAttribute('data-area') === state.area;
      if (ok && words.length) {
        var hay = item.getAttribute('data-hay');
        ok = words.every(function (w) { return hay.indexOf(w) !== -1; });
      }
      item.hidden = !ok;
      if (ok) shown++;
    });
    count.textContent = count.getAttribute('data-tpl')
      .replace('{n}', digits(shown)).replace('{total}', digits(count.getAttribute('data-total')));
    empty.hidden = shown !== 0;
    grid.hidden = shown === 0;
  }

  function setArea(area) {
    state.area = area || '';
    chips.forEach(function (chip) {
      var on = chip.getAttribute('data-area') === state.area;
      chip.setAttribute('aria-pressed', on ? 'true' : 'false');
      if (on && chip.scrollIntoView) chip.parentNode.scrollTo({ left: chip.offsetLeft - 24, behavior: CALM ? 'auto' : 'smooth' });
    });
    apply();
  }

  if (grid) {
    chips.forEach(function (chip) {
      chip.addEventListener('click', function () { setArea(chip.getAttribute('data-area')); });
    });
    search.addEventListener('input', function () { state.text = search.value; apply(); });
    sort.addEventListener('change', function () {
      var mode = sort.value;
      items.slice().sort(function (a, b) {
        if (mode === 'az') return a.getAttribute('data-ord') - b.getAttribute('data-ord');
        var d = a.getAttribute('data-min') - b.getAttribute('data-min');
        return mode === 'lo' ? d : -d;
      }).forEach(function (item) { grid.appendChild(item); });
    });
    $('#clear').addEventListener('click', function () {
      search.value = ''; state.text = ''; setArea('');
    });
  }


  /* ---------- 8. Product window ---------- */

  var sheet = $('#sheet');
  var data = {};
  try { data = JSON.parse($('#products').textContent); } catch (err) { data = {}; }
  var BLANK = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

  function openProduct(id) {
    var p = data[id];
    if (!p || !sheet || !sheet.showModal) return;
    var img = $('#sheetImg');

    $('#sheetTitle').textContent = p.source;
    $('#sheetBrand').innerHTML = '';
    var b = document.createElement('b'); b.textContent = p.brand;
    $('#sheetBrand').appendChild(b);
    $('#sheetBrand').appendChild(document.createTextNode(' ' + p.form));
    $('#sheetArea').textContent = p.area;
    $('#sheetAreaRow').hidden = !p.area;
    $('#sheetUses').textContent = p.uses;
    $('#sheetUsesRow').hidden = !p.uses;

    var list = $('#sheetPacks');
    list.innerHTML = '';
    p.packs.forEach(function (pack, i) {
      var li = document.createElement('li');
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.setAttribute('aria-pressed', i === 0 ? 'true' : 'false');
      var name = document.createElement('span'); name.textContent = pack.pack;
      var price = document.createElement('b'); price.textContent = pack.price;
      btn.appendChild(name); btn.appendChild(price);
      btn.addEventListener('click', function () {
        $$('button', list).forEach(function (x) { x.setAttribute('aria-pressed', x === btn ? 'true' : 'false'); });
        img.src = pack.img || p.img || BLANK;
      });
      li.appendChild(btn); list.appendChild(li);
    });

    img.src = (p.packs[0] && p.packs[0].img) || p.img || BLANK;
    img.alt = p.brand + ' ' + p.form;
    sheet.showModal();
  }

  document.addEventListener('click', function (e) {
    var opener = e.target.closest && e.target.closest('[data-open]');
    if (opener && opener.getAttribute('data-open')) openProduct(opener.getAttribute('data-open'));
    if (e.target === sheet) sheet.close();   // a click on the dimmed area closes it
  });


  /* ---------- 9. Background video ---------- */

  /* a video only loads and plays while it is on screen, and never for
     visitors who asked for less motion or who are saving data */
  var saver = navigator.connection && navigator.connection.saveData;
  if (!CALM && !saver) {
    var play = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var v = entry.target;
        if (entry.isIntersecting) { var p = v.play(); if (p && p.catch) p.catch(function () {}); }
        else v.pause();
      });
    }, { threshold: 0.15 });
    $$('video[data-autoplay]').forEach(function (v) { play.observe(v); });
  }
})();
