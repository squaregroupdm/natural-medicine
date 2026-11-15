<?php
/* =====================================================================
   index.php: the public website, one page.

   Order of the page:
     1. hero        the painted leaves and the opening headline
     2. why         three figures from the booklet
     3. materia     the plants, one large card each, in a sideways row
     4. topack      one plant becoming one product
     5. areas       the health areas, as a large index
     6. interlude   optional full-screen video (only if switched on)
     7. catalogue   every product, with filters, search and sort
     8. export      export operations
     9. close       closing line and footer

   All wording comes from copyText('section','key'), which is edited in
   Admin > Page text. All products, plants and areas come from the
   database. Nothing a visitor reads is typed into this file.
   ===================================================================== */

require __DIR__ . '/../private_app/bootstrap.php';

$lang = lang();
$bn   = $lang === 'bn';

/* ---------- small helpers used only on this page ---------- */

/** Link to a file in /assets, with a version stamp so browsers refresh it. */
function asset(string $path): string
{
    global $CONFIG;
    $file = WEB_ROOT . '/assets/' . $path;
    $v    = is_file($file) ? filemtime($file) : 1;
    return rtrim($CONFIG['base_url'], '/') . '/assets/' . $path . '?v=' . $v;
}

/** The same page in the other language. */
function lang_link(string $to): string
{
    return '?lang=' . $to;
}

/** A two-digit number such as 01, in the visitor's digits. */
function nn(int $n): string
{
    return digits(str_pad((string)$n, 2, '0', STR_PAD_LEFT));
}

/* ---------- content ---------- */

$areas = q('SELECT * FROM areas WHERE is_visible = 1 ORDER BY sort_order, name_en');

$rows = q('SELECT p.*, a.slug AS area_slug, a.name_en AS area_en, a.name_bn AS area_bn
           FROM products p LEFT JOIN areas a ON a.id = p.area_id
           WHERE p.is_visible = 1
           ORDER BY p.brand, p.form_en, p.mrp');

$ingredients = q('SELECT i.*, p.brand, p.form_en, p.form_bn, p.pack, p.mrp, p.image AS product_image
                  FROM ingredients i LEFT JOIN products p ON p.id = i.product_id
                  WHERE i.is_visible = 1 ORDER BY i.sort_order');

$figures = q('SELECT * FROM figures WHERE is_visible = 1 ORDER BY sort_order');

$media = [];
foreach (q('SELECT * FROM videos WHERE is_enabled = 1') as $v) $media[$v['slot']] = $v;

/* The price list has one row per pack size. Visitors think in products,
   so packs of the same brand and form are shown as one card. */
$groups = [];
foreach ($rows as $p) {
    $key = strtolower($p['brand'] . '|' . $p['form_en']);
    if (!isset($groups[$key])) {
        $groups[$key] = [
            'id'      => 'p' . (count($groups) + 1),
            'brand'   => $p['brand'],
            'form'    => pick($p, 'form'),
            'source'  => pick($p, 'source'),
            'srcSort' => strtolower($p['source_en']),
            'area'    => $p['area_slug'] ? pick(['v_en' => $p['area_en'], 'v_bn' => $p['area_bn']], 'v') : '',
            'areaKey' => (string)$p['area_slug'],
            'uses'    => pick($p, 'uses'),
            'img'     => '',
            'min'     => (float)$p['mrp'],
            'packs'   => [],
            'hay'     => '',
        ];
    }
    $g   = &$groups[$key];
    $img = upload_url($p['image']);
    if ($g['img'] === '' && $img !== '') $g['img'] = $img;
    $g['min']     = min($g['min'], (float)$p['mrp']);
    $g['packs'][] = ['pack' => digits($p['pack']), 'price' => money((float)$p['mrp']), 'img' => $img];
    $g['hay']    .= ' ' . implode(' ', [$p['brand'], $p['form_en'], $p['form_bn'], $p['source_en'],
                                       $p['source_bn'], $p['area_en'], $p['area_bn'], $p['uses_en'], $p['uses_bn']]);
    unset($g);
}
uasort($groups, fn($a, $b) => strcmp($a['srcSort'], $b['srcSort']) ?: strcmp($a['brand'], $b['brand']));
$total = count($groups);

/* How many products sit in each area, and one picture to preview it. */
$areaCount = [];
$areaImg   = [];
foreach ($groups as $g) {
    if ($g['areaKey'] === '') continue;
    $areaCount[$g['areaKey']] = ($areaCount[$g['areaKey']] ?? 0) + 1;
    if (!isset($areaImg[$g['areaKey']]) && $g['img'] !== '') $areaImg[$g['areaKey']] = $g['img'];
}
$areas = array_values(array_filter($areas, fn($a) => !empty($areaCount[$a['slug']])));

/* Which product card belongs to a plant, so its button can open it. */
$groupOf = [];
foreach ($groups as $g) $groupOf[strtolower($g['brand'])] = $g['id'];

/* Data the product window reads. */
$forJs = [];
foreach ($groups as $g) {
    $forJs[$g['id']] = [
        'brand' => $g['brand'], 'form' => $g['form'], 'source' => $g['source'],
        'area'  => $g['area'],  'uses' => $g['uses'], 'img'    => $g['img'], 'packs' => $g['packs'],
    ];
}

/* ---------- pieces of markup used more than once ---------- */

/** A paper "plate" carrying one plant drawing. */
function plate(array $i, int $n, string $extra = ''): string
{
    return '<figure class="plate ' . $extra . '">'
         . '<figcaption class="plate-top"><span>' . e(copyText('plate', 'word')) . ' ' . nn($n) . '</span>'
         . '<span>' . e(pick($i, 'part')) . '</span></figcaption>'
         . '<div class="plate-art" data-plant="' . e($i['plant_key']) . '" aria-hidden="true"></div>'
         . '<div class="plate-foot"><i lang="la">' . e($i['botanical']) . '</i><b>' . e(pick($i, 'name')) . '</b></div>'
         . '</figure>';
}

/** A background video or still for one of the three slots. */
function media_slot(array $media, string $slot, string $alt = ''): string
{
    if (empty($media[$slot])) return '';
    $m      = $media[$slot];
    $poster = $m['poster'] ? upload_url($m['poster'], 'video') : '';
    $mp4    = $m['file_mp4'] ? upload_url($m['file_mp4'], 'video') : '';
    if ($mp4 !== '') {
        return '<video class="media" muted loop playsinline preload="none" data-autoplay'
             . ($poster ? ' poster="' . e($poster) . '"' : '') . ' aria-label="' . e($alt) . '">'
             . '<source src="' . e($mp4) . '" type="video/mp4"></video>';
    }
    if ($poster !== '') {
        return '<img class="media" src="' . e($poster) . '" alt="' . e($alt) . '" loading="lazy" decoding="async">';
    }
    return '';
}

$heroMedia   = media_slot($media, 'hero');
$interlude   = media_slot($media, 'interlude');
$exportMedia = media_slot($media, 'export', copyText('export', 'photo_alt'));

$siteTitle = copyText('footer', 'wordmark') . ' | ' . copyText('footer', 'org');
$siteDesc  = copyText('hero', 'sub');
$base      = rtrim($CONFIG['base_url'], '/');
$curve     = '<svg class="curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 120 C 320 18 860 -14 1440 86 L1440 121 L0 121 Z"/></svg>';
?><!DOCTYPE html>
<html lang="<?= $lang ?>" data-lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($siteTitle) ?></title>
<meta name="description" content="<?= e($siteDesc) ?>">
<meta name="theme-color" content="#0B2526">
<link rel="alternate" hreflang="en" href="<?= e($base) ?>/?lang=en">
<link rel="alternate" hreflang="bn" href="<?= e($base) ?>/?lang=bn">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($siteTitle) ?>">
<meta property="og:description" content="<?= e($siteDesc) ?>">
<meta property="og:image" content="<?= e($base) ?>/assets/img/og.jpg">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e($base) ?>/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preload" href="<?= e($base) ?>/assets/fonts/<?= $bn ? 'tiro-bengali' : 'tiro-latin' ?>.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e($base) ?>/assets/fonts/<?= $bn ? 'anek-bengali' : 'anek-latin' ?>.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<script>document.documentElement.classList.add('js');</script>
</head>
<body>

<a class="skip" href="#main"><?= e(copyText('nav', 'skip')) ?></a>

<!-- ======================= top bar ======================= -->
<header class="nav" id="nav">
  <a class="brand" href="#top" aria-label="<?= e(copyText('footer', 'org')) ?>">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20 C4 10 10 4 20 4 C20 14 14 20 4 20 Z M4 20 L14 10"/></svg>
    <span><b><?= e(copyText('nav', 'brand')) ?></b><i><?= e(copyText('nav', 'division')) ?></i></span>
  </a>

  <nav class="links" id="menu" aria-label="Main">
    <a href="#materia"><?= e(copyText('nav', 'plants')) ?></a>
    <a href="#areas"><?= e(copyText('nav', 'areas')) ?></a>
    <a href="#catalogue"><?= e(copyText('nav', 'products')) ?></a>
    <a href="#export"><?= e(copyText('nav', 'export')) ?></a>
  </nav>

  <div class="nav-end">
    <div class="lang" role="group" aria-label="Language">
      <a href="<?= lang_link('en') ?>" lang="en" hreflang="en"<?= $bn ? '' : ' aria-current="true"' ?>>EN</a>
      <a href="<?= lang_link('bn') ?>" lang="bn" hreflang="bn"<?= $bn ? ' aria-current="true"' : '' ?>>বাংলা</a>
    </div>
    <button class="burger" type="button" aria-expanded="false" aria-controls="menu"
            data-open="<?= e(copyText('nav', 'menu')) ?>" data-close="<?= e(copyText('nav', 'close')) ?>">
      <span><?= e(copyText('nav', 'menu')) ?></span>
    </button>
  </div>
</header>

<main id="main">

<!-- ======================= 1. hero ======================= -->
<section class="hero dark" id="top" data-tone="dark">
  <div class="scene" aria-hidden="true">
    <?= $heroMedia ?>
    <div class="layer far"  data-depth="0.10"><canvas data-foliage="hero-back"></canvas></div>
    <div class="layer mid"  data-depth="0.24"><canvas data-foliage="hero-mid"></canvas></div>
    <div class="scrim"></div>
    <div class="layer near" data-depth="0.52"><canvas data-foliage="hero-front"></canvas></div>
  </div>

  <div class="wrap hero-grid">
    <div class="hero-copy">
      <p class="eyebrow" data-r><?= e(copyText('hero', 'eyebrow')) ?></p>
      <h1 data-words><?= e(copyText('hero', 'title')) ?></h1>
      <p class="lede" data-r style="--d:.35s"><?= e(copyText('hero', 'sub')) ?></p>
      <p class="cta-row" data-r style="--d:.5s">
        <a class="btn" href="#materia"><?= e(copyText('hero', 'cta1')) ?> <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 2v11M3.5 8.5 8 13l4.5-4.5"/></svg></a>
        <a class="link" href="#catalogue"><?= e(copyText('hero', 'cta2')) ?></a>
      </p>
    </div>

    <?php if ($ingredients): ?>
    <div class="hero-plates" aria-hidden="true">
      <?php foreach (array_slice($ingredients, 0, 3) as $n => $i): ?>
        <?= plate($i, $n + 1, 'hp hp' . ($n + 1)) ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <p class="cue" aria-hidden="true"><span><?= e(copyText('hero', 'cue')) ?></span></p>
  <?= $curve ?>
</section>

<!-- ======================= 2. why ======================= -->
<section class="why light" id="why" data-tone="light">
  <div class="wrap">
    <p class="label" data-r><?= e(copyText('why', 'label')) ?></p>
    <h2 class="display" data-words><?= e(copyText('why', 'title')) ?></h2>

    <div class="figs">
      <?php foreach ($figures as $n => $f):
          $num   = (float)preg_replace('/[^0-9.]/', '', $f['number']);
          $unit  = trim((string)$f['suffix']);
          $word  = ['m' => $bn ? 'মিলিয়ন' : 'million', 'k' => $bn ? 'হাজার' : 'thousand'][$unit] ?? $unit;
          $isPct = $unit === '%'; ?>
        <article class="fig fig<?= $n + 1 ?>" data-r style="--d:<?= $n * 0.12 ?>s">
          <p class="num"><span data-count="<?= e((string)$num) ?>"><?= e(digits(number_format($num))) ?></span><?php if ($word !== ''): ?><small><?= e($word) ?></small><?php endif; ?></p>
          <?php if ($isPct): $on = (int)round(min(100, $num) / 10); ?>
            <p class="leaves" aria-hidden="true">
              <?php for ($k = 0; $k < 10; $k++): ?><svg viewBox="0 0 24 24" class="<?= $k < $on ? 'on' : '' ?>" style="--k:<?= $k ?>"><path d="M4 20 C4 10 10 4 20 4 C20 14 14 20 4 20 Z"/></svg><?php endfor; ?>
            </p>
          <?php endif; ?>
          <p class="note"><?= e(pick($f, 'note')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>

    <p class="sources" data-r><?= e(copyText('why', 'sources')) ?></p>
  </div>
</section>

<!-- ======================= 3. the plants ======================= -->
<section class="materia dark" id="materia" data-tone="dark" style="--n:<?= count($ingredients) + 1 ?>">
  <div class="pin">
    <div class="scene" aria-hidden="true">
      <div class="layer far"><canvas data-foliage="wall"></canvas></div>
    </div>

    <header class="rail-head wrap">
      <div>
        <p class="label"><?= e(copyText('materia', 'label')) ?></p>
        <h2 class="display sm"><?= e(copyText('materia', 'title')) ?></h2>
      </div>
      <p class="counter" aria-hidden="true"><b id="railNow"><?= nn(1) ?></b><span><?= nn(count($ingredients)) ?></span></p>
    </header>

    <div class="rail" id="rail" tabindex="0" aria-label="<?= e(copyText('materia', 'label')) ?>">
      <ol class="track" id="track">
        <?php foreach ($ingredients as $n => $i):
            $gid = $groupOf[strtolower((string)$i['brand'])] ?? ''; ?>
          <li class="specimen">
            <?= plate($i, $n + 1) ?>
            <div class="spec-copy">
              <p class="spec-n"><?= nn($n + 1) ?></p>
              <h3><?= e(pick($i, 'name')) ?></h3>
              <p class="botanical" lang="la"><?= e($i['botanical']) ?></p>
              <p class="known"><?= e(pick($i, 'known')) ?></p>
              <p class="does"><?= e(pick($i, 'does')) ?></p>
              <?php if ($i['brand']): ?>
                <button class="becomes" type="button"<?= $gid ? ' data-open="' . e($gid) . '"' : ' disabled' ?>>
                  <?php if ($i['product_image']): ?>
                    <span class="shot"><img src="<?= e(upload_url($i['product_image'])) ?>" alt="" loading="lazy" decoding="async"></span>
                  <?php endif; ?>
                  <span class="b-text">
                    <small><?= e(copyText('plate', 'becomes')) ?></small>
                    <b><?= e($i['brand']) ?></b>
                    <i><?= e(pick($i, 'form')) ?>, <?= e(copyText('plate', 'from', ['price' => money((float)$i['mrp'])])) ?></i>
                  </span>
                  <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M8.5 3.5 13 8l-4.5 4.5"/></svg>
                </button>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
        <li class="specimen last">
          <p><?= e(copyText('materia', 'end')) ?></p>
          <a class="btn" href="#catalogue"><?= e(copyText('hero', 'cta2')) ?> <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M8.5 3.5 13 8l-4.5 4.5"/></svg></a>
        </li>
      </ol>
    </div>

    <div class="rail-bar" aria-hidden="true"><i id="railBar"></i></div>
  </div>
</section>

<!-- ======================= 4. leaf to bottle ======================= -->
<?php if ($ingredients): $lead = $ingredients[0]; ?>
<section class="topack light" id="topack" data-tone="light">
  <div class="wrap topack-grid">
    <div class="topack-copy">
      <p class="label" data-r><?= e(copyText('topack', 'label')) ?></p>
      <h2 class="display" data-words><?= e(copyText('topack', 'title')) ?></h2>
      <p class="lede" data-r><?= e(copyText('topack', 'body')) ?></p>
    </div>

    <div class="journey" id="journey" aria-hidden="true">
      <?= plate($lead, 1, 'j-plate') ?>
      <svg class="j-link" viewBox="0 0 300 200" preserveAspectRatio="none"><path pathLength="1" d="M4 150 C 90 150 110 40 170 40 S 250 110 296 96"/></svg>
      <?php if ($lead['product_image']): ?>
        <div class="j-pack">
          <span class="shot"><img src="<?= e(upload_url($lead['product_image'])) ?>" alt="" loading="lazy" decoding="async"></span>
          <p><b><?= e((string)$lead['brand']) ?></b><i><?= e(pick($lead, 'form')) ?>, <?= e(digits((string)$lead['pack'])) ?></i></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ======================= 5. health areas ======================= -->
<section class="areas dark deep" id="areas" data-tone="dark">
  <?= str_replace('class="curve"', 'class="curve top"', $curve) ?>
  <div class="wrap">
    <header class="split-head">
      <div>
        <p class="label" data-r><?= e(copyText('areas', 'label')) ?></p>
        <h2 class="display" data-words><?= e(copyText('areas', 'title')) ?></h2>
      </div>
      <p class="lede" data-r><?= e(copyText('areas', 'body')) ?></p>
    </header>

    <ul class="index" id="areaIndex">
      <?php foreach ($areas as $n => $a): $c = $areaCount[$a['slug']] ?? 0; ?>
        <li data-r style="--d:<?= ($n % 6) * 0.05 ?>s">
          <a href="#catalogue" data-area="<?= e($a['slug']) ?>" data-img="<?= e($areaImg[$a['slug']] ?? '') ?>">
            <span class="i-n"><?= nn($n + 1) ?></span>
            <span class="i-name"><?= e(pick($a, 'name')) ?></span>
            <span class="i-c"><?= e(digits((string)$c)) ?> <?= e(copyText('areas', $c === 1 ? 'unit_one' : 'unit_many')) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="peek" id="peek" aria-hidden="true"><img alt="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="></div>
</section>

<!-- ======================= 6. interlude (optional) ======================= -->
<?php if ($interlude !== ''): ?>
<section class="interlude dark" data-tone="dark" aria-hidden="true"><?= $interlude ?></section>
<?php endif; ?>

<!-- ======================= 7. every product ======================= -->
<section class="catalogue light" id="catalogue" data-tone="light">
  <div class="wrap">
    <p class="label" data-r><?= e(copyText('catalogue', 'label')) ?></p>
    <h2 class="display" data-words><?= e(copyText('catalogue', 'title')) ?></h2>
  </div>

  <div class="tools" id="tools">
    <div class="wrap tools-in">
      <div class="chips" id="chips" role="group" aria-label="<?= e(copyText('nav', 'areas')) ?>">
        <button type="button" class="chip" data-area="" aria-pressed="true"><?= e(copyText('catalogue', 'all')) ?></button>
        <?php foreach ($areas as $a): ?>
          <button type="button" class="chip" data-area="<?= e($a['slug']) ?>" aria-pressed="false"><?= e(pick($a, 'name')) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="tools-end">
        <label class="search">
          <svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="7" cy="7" r="4.5"/><path d="m10.5 10.5 3.5 3.5"/></svg>
          <span class="sr"><?= e(copyText('catalogue', 'search')) ?></span>
          <input type="search" id="search" placeholder="<?= e(copyText('catalogue', 'search')) ?>" autocomplete="off">
        </label>
        <label class="sort">
          <span><?= e(copyText('catalogue', 'sort')) ?></span>
          <select id="sort">
            <option value="az"><?= e(copyText('catalogue', 'sort_az')) ?></option>
            <option value="lo"><?= e(copyText('catalogue', 'sort_lo')) ?></option>
            <option value="hi"><?= e(copyText('catalogue', 'sort_hi')) ?></option>
          </select>
        </label>
      </div>
    </div>
  </div>

  <div class="wrap">
    <p class="count" id="count" aria-live="polite"
       data-tpl="<?= e(copyText('catalogue', 'count')) ?>" data-total="<?= $total ?>"><?= e(copyText('catalogue', 'count', ['n' => digits((string)$total), 'total' => digits((string)$total)])) ?></p>

    <ul class="grid" id="grid">
      <?php $n = 0; foreach ($groups as $g): $n++; ?>
        <li class="item" data-area="<?= e($g['areaKey']) ?>" data-min="<?= e((string)$g['min']) ?>"
            data-ord="<?= $n ?>" data-hay="<?= e(mb_strtolower($g['hay'])) ?>">
          <button type="button" data-open="<?= e($g['id']) ?>">
            <span class="shot">
              <?php if ($g['img'] !== ''): ?>
                <img src="<?= e($g['img']) ?>" alt="<?= e($g['brand'] . ' ' . $g['form']) ?>" loading="lazy" decoding="async" width="600" height="400">
              <?php else: ?>
                <svg class="noimg" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20 C4 10 10 4 20 4 C20 14 14 20 4 20 Z M4 20 L14 10"/></svg>
              <?php endif; ?>
            </span>
            <span class="it-src"><?= e($g['source']) ?></span>
            <span class="it-row">
              <span class="it-brand"><b><?= e($g['brand']) ?></b> <?= e($g['form']) ?></span>
              <span class="it-price"><?= e(count($g['packs']) > 1 ? copyText('plate', 'from', ['price' => money($g['min'])]) : money($g['min'])) ?></span>
            </span>
            <?php if ($g['area'] !== ''): ?><span class="it-area"><?= e($g['area']) ?></span><?php endif; ?>
          </button>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="empty" id="empty" hidden>
      <p><?= e(copyText('catalogue', 'empty')) ?></p>
      <button type="button" class="btn dark-btn" id="clear"><?= e(copyText('catalogue', 'clear')) ?></button>
    </div>
  </div>
</section>

<!-- ======================= 8. export ======================= -->
<section class="export teal" id="export" data-tone="dark">
  <div class="wrap export-grid">
    <figure class="frame" data-r>
      <?php if ($exportMedia !== ''): ?>
        <?= $exportMedia ?>
      <?php else: ?>
        <canvas data-foliage="wall" aria-hidden="true"></canvas>
      <?php endif; ?>
    </figure>

    <div class="export-copy">
      <p class="label" data-r><?= e(copyText('export', 'label')) ?></p>
      <h2 class="display" data-words><?= e(copyText('export', 'title')) ?></h2>
      <p class="lede" data-r><?= e(copyText('export', 'body')) ?></p>

      <h3 class="label" data-r><?= e(copyText('export', 'panel')) ?></h3>
      <ol class="points">
        <?php $k = 0; foreach (['i1', 'i2', 'i3', 'i4', 'i5'] as $key):
            $t = copyText('export', $key); if ($t === '') continue; $k++; ?>
          <li data-r style="--d:<?= $k * 0.07 ?>s"><span><?= nn($k) ?></span><?= e($t) ?></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>

<!-- ======================= 9. close ======================= -->
<section class="close dark" id="close" data-tone="dark">
  <div class="scene" aria-hidden="true">
    <div class="layer far"><canvas data-foliage="wall"></canvas></div>
    <div class="scrim flat"></div>
  </div>
  <div class="wrap">
    <h2 class="display xl" data-words><?= e(copyText('close', 'title')) ?></h2>
    <p data-r><a class="btn" href="#catalogue"><?= e(copyText('close', 'cta')) ?> <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M8.5 3.5 13 8l-4.5 4.5"/></svg></a></p>
  </div>
</section>

</main>

<footer class="foot dark" data-tone="dark">
  <div class="wrap">
    <p class="wordmark"><b><?= e(copyText('footer', 'wordmark')) ?></b><i><?= e(copyText('footer', 'tagline')) ?></i></p>

    <div class="foot-grid">
      <div>
        <p class="org"><?= e(copyText('footer', 'org')) ?></p>
        <?php if (copyText('footer', 'contact') !== ''): ?><p class="contact"><?= nl2br(e(copyText('footer', 'contact'))) ?></p><?php endif; ?>
      </div>
      <nav aria-label="Footer">
        <a href="#materia"><?= e(copyText('nav', 'plants')) ?></a>
        <a href="#areas"><?= e(copyText('nav', 'areas')) ?></a>
        <a href="#catalogue"><?= e(copyText('nav', 'products')) ?></a>
        <a href="#export"><?= e(copyText('nav', 'export')) ?></a>
      </nav>
      <p class="disclaimer"><?= e(copyText('footer', 'disclaimer')) ?></p>
    </div>

    <p class="legal">
      <span>&copy; <?= e(digits(date('Y'))) ?> <?= e(copyText('footer', 'org')) ?>. <?= e(copyText('footer', 'rights')) ?></span>
      <a href="#top"><?= e(copyText('footer', 'top')) ?> <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 14V3M3.5 7.5 8 3l4.5 4.5"/></svg></a>
    </p>
  </div>
</footer>

<!-- ======================= product window ======================= -->
<dialog class="sheet" id="sheet" aria-labelledby="sheetTitle">
  <form method="dialog"><button class="x" aria-label="<?= e(copyText('product', 'close')) ?>"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3 3 10 10M13 3 3 13"/></svg></button></form>
  <div class="sheet-grid">
    <div class="shot big"><img id="sheetImg" alt="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="></div>
    <div class="sheet-copy">
      <p class="label"><?= e(copyText('product', 'made')) ?></p>
      <h2 id="sheetTitle"></h2>
      <p class="sheet-brand" id="sheetBrand"></p>

      <dl>
        <div id="sheetAreaRow"><dt><?= e(copyText('product', 'area')) ?></dt><dd id="sheetArea"></dd></div>
        <div id="sheetUsesRow"><dt><?= e(copyText('product', 'uses')) ?></dt><dd id="sheetUses"></dd></div>
        <div><dt><?= e(copyText('product', 'packs')) ?></dt><dd><ul class="packs" id="sheetPacks"></ul></dd></div>
      </dl>

      <p class="fine"><?= e(copyText('product', 'mrp')) ?> <?= e(copyText('product', 'advice')) ?></p>
    </div>
  </div>
</dialog>

<script type="application/json" id="products"><?= json_encode($forJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="<?= e(asset('js/plants.js')) ?>" defer></script>
<script src="<?= e(asset('js/scene.js')) ?>" defer></script>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
