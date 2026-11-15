/* =====================================================================
   scene.js: the painted foliage behind the dark sections.

   There is no photograph here. Each layer is drawn once onto a canvas
   from a handful of leaf shapes, then the layers are moved at different
   speeds to give depth: a soft far layer, a sharp middle layer and a
   blurred near layer, the way a camera sees through leaves.

   Any element with a data-foliage attribute gets a drawing:
     <canvas data-foliage="hero-back"></canvas>

   The drawing is seeded, so it comes out the same on every visit.
   To change the look, edit PALETTE and PRESETS below.
   ===================================================================== */

(function () {
  'use strict';

  var PALETTE = {
    far:   { a: '#143F40', b: '#0F3233', vein: 'rgba(150,205,190,.07)', rim: 'rgba(150,205,190,.05)' },
    mid:   { a: '#2F8177', b: '#174F4C', vein: 'rgba(205,238,226,.30)', rim: 'rgba(190,235,220,.22)' },
    midLo: { a: '#226A63', b: '#113F3E', vein: 'rgba(205,238,226,.20)', rim: 'rgba(190,235,220,.12)' },
    near:  { a: '#0E3536', b: '#061A1B', vein: 'rgba(120,180,165,.05)', rim: 'rgba(0,0,0,0)' }
  };

  /* A small seeded random generator, so the scene is repeatable. */
  function seeded(seed) {
    return function () {
      seed |= 0; seed = (seed + 0x6D2B79F5) | 0;
      var t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  /* One lance-shaped leaf, drawn from its base along the x axis. */
  function leaf(ctx, len, half, tone) {
    var g = ctx.createLinearGradient(0, -half, 0, half);
    g.addColorStop(0, tone.a);
    g.addColorStop(0.5, tone.a);
    g.addColorStop(0.52, tone.b);
    g.addColorStop(1, tone.b);

    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.bezierCurveTo(len * 0.26, -half * 1.08, len * 0.72, -half * 0.94, len, 0);
    ctx.bezierCurveTo(len * 0.72, half * 0.94, len * 0.26, half * 1.08, 0, 0);
    ctx.fillStyle = g;
    ctx.fill();

    // a thin light edge on the upper side
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.bezierCurveTo(len * 0.26, -half * 1.08, len * 0.72, -half * 0.94, len, 0);
    ctx.strokeStyle = tone.rim;
    ctx.lineWidth = Math.max(1, len * 0.006);
    ctx.stroke();

    // midrib
    ctx.beginPath();
    ctx.moveTo(len * 0.03, 0);
    ctx.lineTo(len * 0.97, 0);
    ctx.strokeStyle = tone.vein;
    ctx.lineWidth = Math.max(1, len * 0.011);
    ctx.stroke();

    // side veins
    ctx.lineWidth = Math.max(0.6, len * 0.005);
    for (var k = 0; k < 7; k++) {
      var x = len * (0.12 + k * 0.115);
      var reach = half * 0.86 * Math.sin(Math.PI * (0.16 + k * 0.105));
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.quadraticCurveTo(x + len * 0.05, -reach * 0.55, x + len * 0.12, -reach);
      ctx.moveTo(x, 0);
      ctx.quadraticCurveTo(x + len * 0.05, reach * 0.55, x + len * 0.12, reach);
      ctx.stroke();
    }
  }

  /* A stem carrying pairs of leaves that get smaller towards the tip. */
  function branch(ctx, rnd, x, y, angle, length, leafLen, pairs, tone) {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(angle);

    var bend = length * (rnd() * 0.24 - 0.12);

    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.quadraticCurveTo(length * 0.5, bend * 0.2, length, bend);
    ctx.strokeStyle = tone.b;
    ctx.lineWidth = Math.max(1.5, leafLen * 0.035);
    ctx.lineCap = 'round';
    ctx.stroke();

    for (var i = 0; i < pairs; i++) {
      var t = (i + 0.55) / pairs;
      var px = length * t;
      var py = bend * t * t;
      var size = leafLen * (1 - 0.5 * t) * (0.86 + rnd() * 0.28);
      for (var side = -1; side <= 1; side += 2) {
        ctx.save();
        ctx.translate(px, py);
        ctx.rotate(side * (0.72 + rnd() * 0.34));
        leaf(ctx, size, size * (0.17 + rnd() * 0.04), tone);
        ctx.restore();
      }
    }
    ctx.save();
    ctx.translate(length, bend);
    ctx.rotate(rnd() * 0.3 - 0.15);
    leaf(ctx, leafLen * 0.5, leafLen * 0.09, tone);
    ctx.restore();

    ctx.restore();
  }

  /* What each named drawing contains. W and H are the canvas size. */
  var PRESETS = {
    'hero-back': function (ctx, W, H, rnd) {
      var S = Math.max(W, H);
      for (var i = 0; i < 13; i++) {
        var x = W * (0.2 + rnd() * 0.95);
        var y = H * (rnd() * 1.2 - 0.1);
        branch(ctx, rnd, x, y, rnd() * Math.PI * 2, S * (0.22 + rnd() * 0.2),
               S * (0.09 + rnd() * 0.05), 4 + Math.floor(rnd() * 3), PALETTE.far);
      }
    },
    'hero-mid': function (ctx, W, H, rnd) {
      var S = Math.max(W, H);
      branch(ctx, rnd, W * 1.04, H * -0.08, 2.42, S * 0.56, S * 0.17, 6, PALETTE.mid);
      branch(ctx, rnd, W * 1.06, H * 0.70, 3.45, S * 0.44, S * 0.15, 5, PALETTE.midLo);
      branch(ctx, rnd, W * 0.56, H * 1.10, -1.86, S * 0.36, S * 0.13, 5, PALETTE.midLo);
      branch(ctx, rnd, W * 0.30, H * -0.10, 1.15, S * 0.22, S * 0.09, 4, PALETTE.midLo);
    },
    'hero-front': function (ctx, W, H, rnd) {
      var S = Math.max(W, H);
      var spots = [
        [W * -0.06, H * 1.04, -0.62, 0.50],
        [W * 1.08,  H * 0.22,  2.72, 0.42],
        [W * 0.86,  H * 1.12, -2.25, 0.38]
      ];
      spots.forEach(function (s) {
        ctx.save();
        ctx.translate(s[0], s[1]);
        ctx.rotate(s[2]);
        leaf(ctx, S * s[3], S * s[3] * 0.2, PALETTE.near);
        ctx.restore();
      });
    },
    /* a quiet wall of far leaves, used behind the plant rail and the footer */
    'wall': function (ctx, W, H, rnd) {
      var S = Math.max(W, H);
      for (var i = 0; i < 16; i++) {
        branch(ctx, rnd, W * rnd(), H * (rnd() * 1.3 - 0.15), rnd() * Math.PI * 2,
               S * (0.2 + rnd() * 0.22), S * (0.08 + rnd() * 0.06), 4 + Math.floor(rnd() * 3), PALETTE.far);
      }
    }
  };

  function paint(canvas) {
    var name = canvas.getAttribute('data-foliage');
    var preset = PRESETS[name];
    if (!preset) return;

    var box = canvas.getBoundingClientRect();
    if (box.width < 2 || box.height < 2) return;

    // far and near layers are blurred anyway, so they are drawn small
    var soft = name !== 'hero-mid';
    var scale = soft ? 0.5 : Math.min(window.devicePixelRatio || 1, 1.5);
    var W = Math.round(box.width * scale);
    var H = Math.round(box.height * scale);
    if (canvas.width === W && canvas.height === H) return;

    canvas.width = W;
    canvas.height = H;
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, W, H);
    preset(ctx, W, H, seeded(name.length * 7919 + 20261115));
  }

  function paintAll() {
    var list = document.querySelectorAll('canvas[data-foliage]');
    for (var i = 0; i < list.length; i++) paint(list[i]);
  }

  var timer;
  window.addEventListener('resize', function () {
    clearTimeout(timer);
    timer = setTimeout(paintAll, 220);
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', paintAll);
  else paintAll();

  window.NMScene = { repaint: paintAll };
})();
