<?php
/* =====================================================================
   index.php: the home page. It tells one story, in this order:

     0. opening     line drawings draw themselves, then the film begins
     1. hero        brand, tagline and the theme line over the forest
     2. why         three plain answers to "why natural"
     3. roots       the plants: one pinned scene, eight plants in turn,
                    each ending with the product it becomes
     4. finder      the Wellness Finder: pick a need, see what fits
     5. trust       Trusted by Square, then doctors' videos
     6. routines    creators' videos (only when there are some)
     7. start       the focus products, and common questions
     8. close       closing line, then the footer

   The theme line is the map: "From nature's roots" is section 3,
   "to everyday wellness" is section 4.

   Sections 1, 2, 3, 5 and 8 are film scenes (see site.js part 4).
   All wording comes from copyText(), edited in Admin > Page text.
   Shared pieces (top bar, footer, cards) are in private_app/site_parts.php.
   ===================================================================== */

require __DIR__ . '/../private_app/site_parts.php';

$bn = lang() === 'bn';

[$groups, $byProduct] = load_groups();
$talks = load_talks();
$byId  = [];
foreach ($groups as $g) $byId[$g['id']] = $g;

$plants = q('SELECT i.*, p.id AS pid, p.brand, p.form_en, p.form_bn, p.mrp, p.image AS product_image
             FROM ingredients i LEFT JOIN products p ON p.id = i.product_id
             WHERE i.is_visible = 1 ORDER BY i.sort_order');

$figures = q('SELECT * FROM figures WHERE is_visible = 1 ORDER BY sort_order');
$faqs    = q('SELECT * FROM faqs WHERE is_visible = 1 ORDER BY sort_order, id');

$needs = q('SELECT * FROM needs WHERE is_visible = 1 ORDER BY sort_order, id');
$needProducts = [];
foreach (q('SELECT * FROM need_products ORDER BY sort_order, id') as $np) {
    $gid = $byProduct[(int)$np['product_id']] ?? null;
    if ($gid) $needProducts[(int)$np['need_id']][] = ['g' => $byId[$gid], 'for' => pick($np, 'for')];
}
$needTalks = [];
foreach ($talks['all'] as $t) if ($t['need_id']) $needTalks[(int)$t['need_id']][] = $t;

$featured = array_filter($groups, fn($g) => $g['featured']);

$arrow = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M8.5 3.5 13 8l-4.5 4.5"/></svg>';

page_head('home', copyText('brand', 'name') . ' | ' . copyText('brand', 'tagline'), copyText('hero', 'sub'));
?>

<!-- ======================= 0. opening ======================= -->
<div class="loader" id="loader" aria-hidden="true">
  <div class="loader-art">
    <?php foreach (array_slice($plants, 0, 3) as $p): ?><span class="line-art" data-plant="<?= e($p['plant_key']) ?>"></span><?php endforeach; ?>
  </div>
  <p class="loader-name"><?= e(copyText('brand', 'name')) ?></p>
</div>

<?php page_nav('home'); ?>

<main id="main">

<!-- ======================= 1. hero ======================= -->
<section class="scene hero dark" id="top" data-scene data-tone="dark" style="--len:2.6">
  <div class="stage">
    <?= slot_film('hero', '', true) ?>
    <div class="shade"></div>
    <div class="wrap panel low">
      <p class="eyebrow" data-r><?= e(copyText('brand', 'tagline')) ?></p>
      <h1 data-words><?= e(copyText('brand', 'theme')) ?></h1>
      <p class="lede" data-r style="--d:.35s"><?= e(copyText('hero', 'sub')) ?></p>
      <p class="cta-row" data-r style="--d:.5s">
        <a class="btn" href="#finder"><?= e(copyText('hero', 'cta1')) ?> <?= $arrow ?></a>
        <a class="link" href="#roots"><?= e(copyText('hero', 'cta2')) ?></a>
      </p>
    </div>
    <p class="cue" aria-hidden="true"><span><?= e(copyText('hero', 'cue')) ?></span></p>
  </div>
</section>

<!-- ======================= 2. why natural ======================= -->
<section class="scene why dark" id="why" data-scene data-tone="dark" style="--len:4">
  <div class="stage">
    <?= slot_film('why') ?>
    <div class="shade even"></div>
    <div class="wrap panel">
      <p class="label st" style="--at:0"><?= e(copyText('why', 'label')) ?></p>
      <h2 class="display st" style="--at:.04"><?= e(copyText('why', 'title')) ?></h2>

      <ol class="answers">
        <?php foreach ([1, 2, 3] as $n): ?>
          <li class="st" style="--at:<?= 0.16 + ($n - 1) * 0.16 ?>">
            <span><?= nn($n) ?></span>
            <h3><?= e(copyText('why', "a{$n}_t")) ?></h3>
            <p><?= e(copyText('why', "a{$n}_b")) ?></p>
          </li>
        <?php endforeach; ?>
      </ol>

      <?php if ($figures): ?>
      <p class="proof st" style="--at:.66">
        <?php foreach ($figures as $f):
            $num  = (float)preg_replace('/[^0-9.]/', '', $f['number']);
            $unit = trim((string)$f['suffix']);
            $word = ['m' => $bn ? ' মিলিয়ন' : ' million', 'k' => $bn ? ' হাজার' : ' thousand'][$unit] ?? $unit; ?>
          <span><b><i data-count="<?= e((string)$num) ?>" data-at=".66"><?= e(digits(number_format($num))) ?></i><?= e($word) ?></b> <?= e(pick($f, 'note')) ?></span>
        <?php endforeach; ?>
        <small><?= e(copyText('why', 'sources')) ?></small>
      </p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ======================= 3. from the root ======================= -->
<?php if ($plants): $total = count($plants); ?>
<section class="scene roots dark" id="roots-scene" data-scene data-roots data-last data-tone="dark" style="--len:<?= round(2.4 + $total * 0.8, 1) ?>;--n:<?= $total ?>">
  <span class="anchor" id="roots" style="top:78vh"></span>
  <div class="stage">

    <div class="root-items">
      <?php foreach ($plants as $n => $p):
          $gid = $p['pid'] ? ($byProduct[(int)$p['pid']] ?? '') : ''; ?>
        <article class="root<?= $n === 0 ? ' on' : '' ?>" data-orbit>
          <?= film($p['video'] ?? null, $p['image'] ?? null, pick($p, 'name')) ?>
          <div class="shade"></div>
          <div class="wrap panel">
            <p class="spec-n st" style="--at:0"><b><?= nn($n + 1) ?></b> / <?= nn($total) ?> <span><?= e(pick($p, 'part')) ?></span></p>
            <h3 class="plant-name st" style="--at:.04"><?= e(pick($p, 'name')) ?></h3>
            <p class="botanical st" style="--at:.1" lang="la"><?= e($p['botanical']) ?></p>
            <p class="known st" style="--at:.24"><?= e(pick($p, 'known')) ?></p>
            <p class="does st" style="--at:.36"><?= e(pick($p, 'does')) ?></p>
            <?php if ($p['brand']): ?>
              <div class="st rise" style="--at:.5">
                <button class="becomes" type="button" data-tilt<?= $gid ? ' data-open="' . e($gid) . '"' : ' disabled' ?>>
                  <?php if ($p['product_image']): ?><span class="shot"><img src="<?= e(upload_url($p['product_image'])) ?>" alt="" loading="lazy" decoding="async"></span><?php endif; ?>
                  <span class="b-text">
                    <small><?= e(copyText('plate', 'becomes')) ?></small>
                    <b><?= e($p['brand']) ?></b>
                    <i><?= e(pick($p, 'form')) ?>, <?= e(copyText('plate', 'from', ['price' => money((float)$p['mrp'])])) ?></i>
                  </span>
                  <?= $arrow ?>
                </button>
              </div>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="wrap intro">
      <p class="label"><?= e(copyText('materia', 'label')) ?></p>
      <h2 class="display xl"><?= e(copyText('materia', 'title')) ?></h2>
      <p class="lede"><?= e(copyText('materia', 'hint')) ?></p>
    </div>

    <nav class="root-tabs" aria-label="<?= e(copyText('nav', 'plants')) ?>">
      <?php foreach ($plants as $n => $p): ?>
        <button type="button" data-root="<?= $n ?>"<?= $n === 0 ? ' aria-current="true"' : '' ?>><i><?= nn($n + 1) ?></i><span><?= e(pick($p, 'name')) ?></span></button>
      <?php endforeach; ?>
    </nav>
  </div>
</section>
<?php endif; ?>

<!-- ======================= 4. wellness finder ======================= -->
<?php if ($needs): ?>
<section class="finder light lift" id="finder" data-tone="light">
  <div class="wrap">
    <header class="split-head">
      <div>
        <p class="label" data-r><?= e(copyText('finder', 'label')) ?></p>
        <h2 class="display" data-words><?= e(copyText('finder', 'title')) ?></h2>
      </div>
      <p class="lede" data-r><?= e(copyText('finder', 'body')) ?></p>
    </header>

    <div class="needs" role="tablist" aria-label="<?= e(copyText('nav', 'finder')) ?>" data-r>
      <?php foreach ($needs as $n => $need): ?>
        <button type="button" role="tab" id="need-tab-<?= e($need['slug']) ?>" aria-controls="need-<?= e($need['slug']) ?>"
                aria-selected="<?= $n === 0 ? 'true' : 'false' ?>"<?= $n === 0 ? '' : ' tabindex="-1"' ?>>
          <span class="line-art" data-plant="<?= e((string)$need['plant_key']) ?>" aria-hidden="true"></span>
          <b><?= e(pick($need, 'name')) ?></b>
        </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($needs as $n => $need): $items = $needProducts[(int)$need['id']] ?? []; ?>
      <div class="need<?= $n === 0 ? ' on' : '' ?>" id="need-<?= e($need['slug']) ?>" role="tabpanel" aria-labelledby="need-tab-<?= e($need['slug']) ?>">
        <div class="need-say">
          <h3><?= e(pick($need, 'line')) ?></h3>
          <p><?= e(pick($need, 'body')) ?></p>
        </div>
        <ul class="need-list">
          <?php foreach ($items as $k => $it): $g = $it['g']; ?>
            <li style="--k:<?= $k ?>">
              <button type="button" class="pick" data-open="<?= e($g['id']) ?>" data-tilt>
                <span class="shot"><?php if ($g['img'] !== ''): ?><img src="<?= e($g['img']) ?>" alt="<?= e($g['brand'] . ' ' . $g['form']) ?>" loading="lazy" decoding="async"><?php endif; ?></span>
                <span class="pick-text">
                  <small><?= e(copyText('finder', 'for')) ?></small>
                  <b><?= e($it['for']) ?></b>
                  <i><?= e($g['brand']) ?>, <?= e($g['form']) ?> · <?= e(count($g['packs']) > 1 ? copyText('plate', 'from', ['price' => money($g['min'])]) : money($g['min'])) ?></i>
                </span>
              </button>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if (!empty($needTalks[(int)$need['id']])): ?>
          <h4 class="label"><?= e(copyText('finder', 'videos')) ?></h4>
          <ul class="vids row"><?php foreach (array_slice($needTalks[(int)$need['id']], 0, 4) as $t) echo talk_card($t); ?></ul>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <p class="finder-foot">
      <a class="btn dark-btn" href="<?= e(site_url('products.php')) ?>"><?= e(copyText('finder', 'all')) ?> <?= $arrow ?></a>
      <span><?= e(copyText('finder', 'advice')) ?></span>
    </p>
  </div>
</section>
<?php endif; ?>

<!-- ======================= 5. trusted by Square ======================= -->
<section class="scene trust dark" id="trust" data-scene data-solo data-tone="dark" style="--len:2.6">
  <div class="stage">
    <?= slot_film('leaf') ?>
    <div class="shade"></div>
    <div class="wrap panel">
      <p class="label st" style="--at:.06"><?= e(copyText('trust', 'label')) ?></p>
      <h2 class="display st" style="--at:.1"><?= e(copyText('trust', 'title')) ?></h2>
      <ul class="points">
        <?php $k = 0; foreach ([1, 2, 3, 4] as $n):
            $t = copyText('trust', "p{$n}_t"); if ($t === '') continue; $k++; ?>
          <li class="st" style="--at:<?= 0.2 + $k * 0.12 ?>"><span><?= nn($k) ?></span><div><b><?= e($t) ?></b><p><?= e(copyText('trust', "p{$n}_b")) ?></p></div></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<?php if ($talks['doctor']): ?>
<section class="shelf dark deep" data-tone="dark">
  <div class="wrap">
    <header class="shelf-head"><h2 class="display sm" data-r><?= e(copyText('trust', 'doctors')) ?></h2>
      <a class="link" href="<?= e(site_url('videos.php')) ?>#doctors"><?= e(copyText('videos', 'all')) ?></a></header>
    <ul class="vids row" data-r><?php foreach (array_slice($talks['doctor'], 0, 3) as $t) echo talk_card($t); ?></ul>
  </div>
</section>
<?php endif; ?>

<!-- ======================= 6. real routines ======================= -->
<?php if ($talks['creator']): ?>
<section class="shelf dark deep" data-tone="dark">
  <div class="wrap">
    <header class="shelf-head"><h2 class="display sm" data-r><?= e(copyText('videos', 'teaser')) ?></h2>
      <a class="link" href="<?= e(site_url('videos.php')) ?>#creators"><?= e(copyText('videos', 'all')) ?></a></header>
    <ul class="vids row" data-r><?php foreach (array_slice($talks['creator'], 0, 4) as $t) echo talk_card($t); ?></ul>
  </div>
</section>
<?php endif; ?>

<!-- ======================= 7. where to start, and questions ======================= -->
<section class="start light lift" id="start" data-tone="light">
  <div class="wrap">
    <?php if ($featured): ?>
      <header class="shelf-head">
        <div>
          <p class="label" data-r><?= e(copyText('catalogue', 'label')) ?></p>
          <h2 class="display" data-words><?= e(copyText('catalogue', 'featured')) ?></h2>
        </div>
        <a class="btn dark-btn" href="<?= e(site_url('products.php')) ?>" data-r><?= e(copyText('catalogue', 'browse')) ?> <?= $arrow ?></a>
      </header>
      <ul class="grid" id="grid"><?php $n = 0; foreach ($featured as $g) echo product_card($g, ++$n); ?></ul>
    <?php endif; ?>

    <?php if ($faqs || $talks['why']): ?>
    <div class="faq" id="faq">
      <div class="faq-head">
        <p class="label" data-r><?= e(copyText('faq', 'label')) ?></p>
        <h2 class="display sm" data-r><?= e(copyText('faq', 'title')) ?></h2>
        <?php if ($talks['why']): ?>
          <h3 class="label" style="margin-top:36px"><?= e(copyText('why', 'watch')) ?></h3>
          <ul class="vids row two"><?php foreach (array_slice($talks['why'], 0, 4) as $t) echo talk_card($t); ?></ul>
        <?php endif; ?>
      </div>
      <div class="faq-list" data-r>
        <?php foreach ($faqs as $f): ?>
          <details>
            <summary><?= e(pick($f, 'q')) ?><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 3v10M3 8h10"/></svg></summary>
            <p><?= nl2br(e(pick($f, 'a'))) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ======================= 8. close ======================= -->
<section class="scene close dark" id="close" data-scene data-solo data-tone="dark" style="--len:2">
  <div class="stage">
    <?= slot_film('close') ?>
    <div class="shade even"></div>
    <div class="wrap panel low">
      <h2 class="display xl st" style="--at:.1"><?= e(copyText('close', 'title')) ?></h2>
      <p class="st" style="--at:.3"><a class="btn" href="#finder"><?= e(copyText('close', 'cta')) ?> <?= $arrow ?></a></p>
    </div>
  </div>
</section>

</main>

<?php page_foot('home', products_json($groups, $byProduct, $talks)); ?>
