/* =====================================================================
   site.js: everything that moves or responds on the public page.
   Plain JavaScript, no libraries, no build step.

   Contents
     1. Helpers
     2. Plant drawings        (fills every [data-plant] from plants.js)
     3. Reveal on first view  ([data-r], [data-words], plates, counters)
     4. Scroll loop           (hero depth, plants row, leaf to bottle)
     5. Top bar and phone menu
     6. Health areas          (hover preview, click to filter)
     7. Catalogue             (filter, search, sort)
     8. Product window
     9. Background video

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
      $$('[data-count]', el).forEach(countUp);
      seen.unobserve(el);
    });
  }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });

  $$('[data-r], [data-words], .plate').forEach(function (el) { seen.observe(el); });

  /* the hero is on screen at once, so show it without waiting for a scroll */
  requestAnimationFrame(function () {
    $$('.hero [data-r], .hero [data-words], .hero .plate').forEach(function (el) { el.classList.add('in'); });
  });


  /* ---------- 4. Scroll loop ---------- */

  var hero = $('.hero');
  var heroLayers = $$('.hero .layer');
  var heroPlates = $('.hero-plates');
  var heroCopy = $('.hero-copy');
  var materia = $('.materia');
  var track = $('#track');
  var rail = $('#rail');
  var railNow = $('#railNow');
  var railBar = $('#railBar');
  var specimens = $$('.specimen:not(.last)');
  var journey = $('#journey');
  var frame = $('.frame');

  var vh = window.innerHeight, vw = window.innerWidth;
  var travel = 0;          // how far the plants row has to slide, in px
  var pinned = false;
  var mx = 0, my = 0, tx = 0, ty = 0;   // pointer position, eased

  function measure() {
    vh = window.innerHeight; vw = window.innerWidth;
    pinned = !!(track && WIDE.matches && !CALM);
    doc.classList.toggle('rail-pin', pinned);
    if (pinned) {
      travel = Math.max(0, track.scrollWidth - vw);
      materia.style.setProperty('--travel', travel + 'px');
    } else if (track) {
      track.style.removeProperty('--x');
    }
  }

  function setCounter(index) {
    if (!railNow) return;
    var text = pad2(clamp(index, 0, specimens.length - 1) + 1);
    if (railNow.textContent !== text) railNow.textContent = text;
  }

  function tick() {
    var y = window.scrollY;

    /* hero: leaves at three depths, plates drifting, copy easing away */
    if (hero && y < vh * 1.3 && !CALM) {
      tx += (mx - tx) * 0.06; ty += (my - ty) * 0.06;
      heroLayers.forEach(function (layer) {
        var depth = parseFloat(layer.getAttribute('data-depth')) || 0;
        layer.style.transform = 'translate3d(' + (tx * depth * -46).toFixed(1) + 'px,' + (y * depth * -0.9 + ty * depth * -30).toFixed(1) + 'px,0)';
      });
      if (heroPlates) {
        heroPlates.style.setProperty('--sy', (y * 0.012).toFixed(2));
        heroPlates.style.setProperty('--mx', tx.toFixed(3));
        heroPlates.style.setProperty('--my', ty.toFixed(3));
      }
      if (heroCopy) {
        var fade = clamp(y / (vh * 0.8), 0, 1);
        heroCopy.style.opacity = (1 - fade * 0.9).toFixed(3);
        heroCopy.style.transform = 'translate3d(0,' + (y * -0.12).toFixed(1) + 'px,0)';
      }
    }

    /* plants row: scrolling down slides the row sideways while it is pinned */
    if (pinned) {
      var box = materia.getBoundingClientRect();
      var p = clamp(-box.top / (box.height - vh), 0, 1);
      track.style.setProperty('--x', (p * travel).toFixed(1));
      if (railBar) railBar.style.setProperty('--p', p.toFixed(4));
      var mid = vw / 2, best = 0, gap = Infinity;
      specimens.forEach(function (s, i) {
        var r = s.getBoundingClientRect();
        var d = Math.abs(r.left + r.width / 2 - mid);
        if (d < gap) { gap = d; best = i; }
      });
      setCounter(best);
    }

    /* leaf to bottle: the link draws and the pack arrives as it crosses the screen */
    if (journey && !CALM) {
      var j = journey.getBoundingClientRect();
      journey.style.setProperty('--p', clamp((vh * 0.92 - j.top) / (vh * 0.62), 0, 1).toFixed(3));
    }

    /* export picture drifts slightly inside its frame */
    if (frame && !CALM) {
      var f = frame.getBoundingClientRect();
      if (f.bottom > 0 && f.top < vh) frame.style.setProperty('--py', (((f.top + f.height / 2) / vh - 0.5) * -34).toFixed(1));
    }

    requestAnimationFrame(tick);
  }

  /* on phones the row is swiped by hand, so the counter follows the swipe */
  if (rail) {
    rail.addEventListener('scroll', function () {
      if (pinned) return;
      var p = rail.scrollLeft / Math.max(1, rail.scrollWidth - rail.clientWidth);
      if (railBar) railBar.style.setProperty('--p', p.toFixed(4));
      var mid = rail.getBoundingClientRect().left + rail.clientWidth / 2, best = 0, gap = Infinity;
      specimens.forEach(function (s, i) {
        var r = s.getBoundingClientRect();
        var d = Math.abs(r.left + r.width / 2 - mid);
        if (d < gap) { gap = d; best = i; }
      });
      setCounter(best);
    }, { passive: true });

    /* arrow keys move through the row */
    rail.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
      e.preventDefault();
      var dir = e.key === 'ArrowRight' ? 1 : -1;
      if (pinned) window.scrollBy({ top: dir * travel / specimens.length, behavior: CALM ? 'auto' : 'smooth' });
      else rail.scrollBy({ left: dir * rail.clientWidth * 0.86, behavior: CALM ? 'auto' : 'smooth' });
    });
  }

  if (HOVER) {
    window.addEventListener('pointermove', function (e) {
      mx = e.clientX / vw - 0.5; my = e.clientY / vh - 0.5;
    }, { passive: true });
  }

  var resizeTimer;
  window.addEventListener('resize', function () { clearTimeout(resizeTimer); resizeTimer = setTimeout(measure, 150); });
  WIDE.addEventListener('change', measure);
  window.addEventListener('load', measure);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure);
  measure();
  requestAnimationFrame(tick);


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
      var hide = y > lastY && y > vh * 0.9;
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
        if (a.getAttribute('href') === '#' + entry.target.id) a.setAttribute('aria-current', 'true');
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
