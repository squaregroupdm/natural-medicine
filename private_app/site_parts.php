<?php
/* =====================================================================
   site_parts.php: the pieces every public page shares.

   The three public pages (index.php, products.php, videos.php) each
   start with:
       require __DIR__ . '/../private_app/site_parts.php';
   and then call page_head(), page_nav() ... page_foot().

   What is in here
     - small helpers        site_url(), asset(), nn()
     - loading content      load_groups(), load_talks()
     - repeated markup      product_card(), talk_card(), film()
     - page furniture       page_head(), page_nav(), page_foot()
   ===================================================================== */

require __DIR__ . '/bootstrap.php';

/* ---------- small helpers ---------- */

/** An address inside the site, correct whether it lives in a folder or not. */
function site_url(string $path = ''): string
{
    global $CONFIG;
    return rtrim($CONFIG['base_url'], '/') . '/' . ltrim($path, '/');
}

/** Link to a file in /assets, with a version stamp so browsers refresh it. */
function asset(string $path): string
{
    $file = WEB_ROOT . '/assets/' . $path;
    return site_url('assets/' . $path) . '?v=' . (is_file($file) ? filemtime($file) : 1);
}

/** A two-digit number such as 01, in the visitor's digits. */
function nn(int $n): string
{
    return digits(str_pad((string)$n, 2, '0', STR_PAD_LEFT));
}

/* ---------- loading content ---------- */

/**
 * Every visible product, with pack sizes of the same brand and form
 * folded into one card. Focus brands come first.
 * Returns [groups, map of product id => group id].
 */
function load_groups(): array
{
    $rows = q('SELECT p.*, a.slug AS area_slug, a.name_en AS area_en, a.name_bn AS area_bn
               FROM products p LEFT JOIN areas a ON a.id = p.area_id
               WHERE p.is_visible = 1
               ORDER BY p.brand, p.form_en, p.mrp');
    $groups = [];
    foreach ($rows as $p) {
        $key = strtolower($p['brand'] . '|' . $p['form_en']);
        if (!isset($groups[$key])) {
            $groups[$key] = [
                'id' => '', 'ids' => [], 'brand' => $p['brand'],
                'form'     => pick($p, 'form'),
                'source'   => pick($p, 'source'),
                'srcSort'  => strtolower($p['source_en']),
                'area'     => $p['area_slug'] ? pick(['v_en' => $p['area_en'], 'v_bn' => $p['area_bn']], 'v') : '',
                'areaKey'  => (string)$p['area_slug'],
                'uses'     => pick($p, 'uses'),
                'featured' => 0, 'img' => '', 'min' => (float)$p['mrp'], 'packs' => [], 'hay' => '', 'videos' => [],
            ];
        }
        $g   = &$groups[$key];
        $img = upload_url($p['image']);
        if ($g['img'] === '' && $img !== '') $g['img'] = $img;
        if (!empty($p['is_featured'])) $g['featured'] = 1;
        $g['ids'][]   = (int)$p['id'];
        $g['min']     = min($g['min'], (float)$p['mrp']);
        $g['packs'][] = ['pack' => digits($p['pack']), 'price' => money((float)$p['mrp']), 'img' => $img];
        $g['hay']    .= ' ' . implode(' ', [$p['brand'], $p['form_en'], $p['form_bn'], $p['source_en'],
                                           $p['source_bn'], $p['area_en'], $p['area_bn'], $p['uses_en'], $p['uses_bn']]);
        unset($g);
    }
    uasort($groups, fn($a, $b) => ($b['featured'] <=> $a['featured'])
        ?: strcmp($a['srcSort'], $b['srcSort']) ?: strcmp($a['brand'], $b['brand']));

    $byProduct = [];
    $n = 0;
    foreach ($groups as &$g) {
        $g['id'] = 'p' . (++$n);
        foreach ($g['ids'] as $pid) $byProduct[$pid] = $g['id'];
    }
    unset($g);
    return [$groups, $byProduct];
}

/** Every visible video, newest order first, grouped by kind. */
function load_talks(): array
{
    $out = ['creator' => [], 'doctor' => [], 'why' => [], 'all' => []];
    try {
        $rows = q('SELECT * FROM talks WHERE is_visible = 1 ORDER BY sort_order, id DESC');
    } catch (Throwable $e) {
        return $out;   // an older database without the table: simply no videos
    }
    foreach ($rows as $t) {
        $out['all'][] = $t;
        if (isset($out[$t['kind']])) $out[$t['kind']][] = $t;
    }
    return $out;
}

/** The data the product window reads, printed once per page. */
function products_json(array $groups, array $byProduct, array $talks): string
{
    foreach ($talks['all'] as $t) {
        $gid = $t['product_id'] ? ($byProduct[(int)$t['product_id']] ?? null) : null;
        if ($gid === null) continue;
        foreach ($groups as &$g) {
            if ($g['id'] === $gid && $t['youtube_id'] !== '') $g['videos'][] = ['id' => $t['youtube_id'], 'title' => pick($t, 'title'), 'short' => (int)$t['is_short']];
        }
        unset($g);
    }
    $js = [];
    foreach ($groups as $g) {
        $js[$g['id']] = ['brand' => $g['brand'], 'form' => $g['form'], 'source' => $g['source'], 'area' => $g['area'],
                         'uses' => $g['uses'], 'img' => $g['img'], 'packs' => $g['packs'], 'videos' => $g['videos']];
    }
    return '<script type="application/json" id="products">'
         . json_encode($js, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)
         . '</script>';
}

/* ---------- repeated markup ---------- */

/** One product card in a grid. */
function product_card(array $g, int $order): string
{
    $price = count($g['packs']) > 1 ? copyText('plate', 'from', ['price' => money($g['min'])]) : money($g['min']);
    $pic   = $g['img'] !== ''
        ? '<img src="' . e($g['img']) . '" alt="' . e($g['brand'] . ' ' . $g['form']) . '" loading="lazy" decoding="async" width="600" height="400">'
        : '<svg class="noimg" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20 C4 10 10 4 20 4 C20 14 14 20 4 20 Z M4 20 L14 10"/></svg>';
    return '<li class="item" data-area="' . e($g['areaKey']) . '" data-min="' . e((string)$g['min']) . '" data-ord="' . $order . '" data-hay="' . e(mb_strtolower($g['hay'])) . '">'
         . '<button type="button" data-open="' . e($g['id']) . '">'
         . '<span class="shot">' . $pic . '</span>'
         . '<span class="it-src">' . e($g['source']) . '</span>'
         . '<span class="it-row"><span class="it-brand"><b>' . e($g['brand']) . '</b> ' . e($g['form']) . '</span>'
         . '<span class="it-price">' . e($price) . '</span></span>'
         . ($g['area'] !== '' ? '<span class="it-area">' . e($g['area']) . '</span>' : '')
         . '</button></li>';
}

/** One video card. A click plays it in the on-page player. */
function talk_card(array $t): string
{
    $id    = (string)$t['youtube_id'];
    $title = pick($t, 'title');
    $who   = pick($t, 'speaker');
    // our own picture if one was uploaded, otherwise the one YouTube made
    $pic   = !empty($t['thumb']) ? upload_url($t['thumb'], 'video')
           : ($id !== '' ? 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg' : '');
    return '<li class="vid' . ($t['is_short'] ? ' short' : '') . '">'
         . '<button type="button" data-video="' . e($id) . '"' . ($t['is_short'] ? ' data-short="1"' : '')
         . ($id === '' ? ' data-sample="' . e($pic) . '"' : '') . ' aria-label="' . e(copyText('videos', 'play') . ': ' . $title) . '">'
         . '<span class="thumb">' . ($pic !== '' ? '<img src="' . e($pic) . '" alt="" loading="lazy" decoding="async">' : '')
         . '<svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="23"/><path d="M20 16 L33 24 L20 32 Z"/></svg></span>'
         . '<b>' . e($title) . '</b>'
         . ($who !== '' ? '<small>' . e($who) . '</small>' : '')
         . '</button></li>';
}

/**
 * The full product list with its filter bar, search and sort.
 * Used on the home page and on products.php. $tag is the heading level.
 * $first, when set, shows only that many cards until "Show all" is pressed
 * (filtering or searching always looks through every product).
 */
function catalogue_block(array $groups, string $tag = 'h2', int $first = 0): void
{
    $total = count($groups);
    // only show filter buttons for health areas that have products
    $has = [];
    foreach ($groups as $g) if ($g['areaKey'] !== '') $has[$g['areaKey']] = true;
    $areas = array_values(array_filter(
        q('SELECT * FROM areas WHERE is_visible = 1 ORDER BY sort_order, name_en'),
        fn($a) => isset($has[$a['slug']])
    ));
    ?>
  <div class="cat<?= ($first && $total > $first) ? ' short' : '' ?>"<?= $first ? ' data-first="' . $first . '"' : '' ?>>
  <div class="wrap">
    <p class="label" data-r><?= e(copyText('catalogue', 'label')) ?></p>
    <<?= $tag ?> class="display" data-words><?= e(copyText('catalogue', 'title')) ?></<?= $tag ?>>
  </div>

  <div class="tools" id="tools">
    <div class="wrap tools-in">
      <div class="chips" id="chips" role="group" aria-label="<?= e(copyText('catalogue', 'areas')) ?>">
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

    <ul class="grid" id="grid"><?php $n = 0; foreach ($groups as $g) echo product_card($g, ++$n); ?></ul>

    <?php if ($first && $total > $first): ?>
      <p class="more-row"><button type="button" class="btn dark-btn" id="showAll"><?= e(copyText('catalogue', 'show_all', ['total' => digits((string)$total)])) ?> <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 2v11M3.5 8.5 8 13l4.5-4.5"/></svg></button></p>
    <?php endif; ?>

    <div class="empty" id="empty" hidden>
      <p><?= e(copyText('catalogue', 'empty')) ?></p>
      <button type="button" class="btn dark-btn" id="clear"><?= e(copyText('catalogue', 'clear')) ?></button>
    </div>
  </div>
  </div>
    <?php
}

/**
 * The moving picture behind a scene: a clip that follows the scroll,
 * a still, or (when neither has been uploaded) painted leaves.
 */
function film(?string $mp4, ?string $still, string $alt = '', bool $eager = false): string
{
    global $CONFIG;
    // a file named in the database but missing from the server is treated as not there
    $dir   = WEB_ROOT . '/' . $CONFIG['upload_dir'] . '/video/';
    $mp4   = ($mp4   && is_file($dir . $mp4))   ? upload_url($mp4, 'video')   : '';
    $still = ($still && is_file($dir . $still)) ? upload_url($still, 'video') : '';
    if ($mp4 === '' && $still === '') {
        return '<div class="film painted"><canvas data-foliage="wall"></canvas></div>';
    }
    $out = '<div class="film">';
    if ($still !== '') {
        $out .= '<img src="' . e($still) . '" alt="' . e($alt) . '" decoding="async"'
              . ($eager ? ' fetchpriority="high"' : ' loading="lazy"') . '>';
    }
    if ($mp4 !== '') {
        $out .= '<video muted playsinline preload="none" data-src="' . e($mp4) . '" aria-hidden="true" tabindex="-1"></video>';
    }
    return $out . '</div>';
}

/** The clip and still for one of the named film slots (Admin > Film). */
function slot_film(string $slot, string $alt = '', bool $eager = false): string
{
    static $media = null;
    if ($media === null) {
        $media = [];
        foreach (q('SELECT * FROM videos WHERE is_enabled = 1') as $v) $media[$v['slot']] = $v;
    }
    $m = $media[$slot] ?? null;
    return film($m['file_mp4'] ?? null, $m['poster'] ?? null, $alt, $eager);
}

/* ---------- page furniture ---------- */

/** Everything from <!DOCTYPE> to the opening <body>. $page is home, products or videos. */
function page_head(string $page, string $title, string $desc): void
{
    $lang = lang();
    $bn   = $lang === 'bn';
    $self = site_url($page === 'home' ? '' : $page . '.php');
    ?><!DOCTYPE html>
<html lang="<?= $lang ?>" data-lang="<?= $lang ?>" data-page="<?= e($page) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="theme-color" content="#0B2526">
<link rel="alternate" hreflang="en" href="<?= e($self) ?>?lang=en">
<link rel="alternate" hreflang="bn" href="<?= e($self) ?>?lang=bn">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e(site_url('assets/img/og.jpg')) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(site_url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preload" href="<?= e(site_url('assets/fonts/' . ($bn ? 'tiro-bengali' : 'tiro-latin') . '.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(site_url('assets/fonts/' . ($bn ? 'anek-bengali' : 'anek-latin') . '.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<script>
/* tells the stylesheet that scripts are on, and whether to play the opening
   drawing (home page only, once per visit, not for reduced motion) */
(function (d) {
  d.classList.add('js');
  try {
    if (d.getAttribute('data-page') === 'home' && !matchMedia('(prefers-reduced-motion: reduce)').matches && !sessionStorage.getItem('sqn-intro')) {
      d.classList.add('intro'); sessionStorage.setItem('sqn-intro', '1');
    }
  } catch (e) {}
})(document.documentElement);
</script>
</head>
<body data-more="<?= e(copyText('nav', 'more')) ?>">
<a class="skip" href="#main"><?= e(copyText('nav', 'skip')) ?></a>
<?php
}

/** The top bar. */
function page_nav(string $page): void
{
    $bn   = lang() === 'bn';
    $home = $page === 'home' ? '' : site_url('');
    $self = site_url($page === 'home' ? '' : $page . '.php');
    ?>
<header class="nav" id="nav">
  <i class="progress" id="progress" aria-hidden="true"></i>
  <a class="brand" href="<?= e($page === 'home' ? '#top' : site_url('')) ?>" aria-label="<?= e(copyText('brand', 'name')) ?>">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20 C4 10 10 4 20 4 C20 14 14 20 4 20 Z M4 20 L14 10"/></svg>
    <span><b><?= e(copyText('brand', 'name')) ?></b><i><?= e(copyText('brand', 'org')) ?></i></span>
  </a>

  <nav class="links" id="menu" aria-label="Main">
    <a href="<?= e($home) ?>#finder"><?= e(copyText('nav', 'finder')) ?></a>
    <a href="<?= e($home) ?>#roots"><?= e(copyText('nav', 'plants')) ?></a>
    <a href="<?= e(site_url('products.php')) ?>"<?= $page === 'products' ? ' aria-current="page"' : '' ?>><?= e(copyText('nav', 'products')) ?></a>
    <a href="<?= e(site_url('videos.php')) ?>"<?= $page === 'videos' ? ' aria-current="page"' : '' ?>><?= e(copyText('nav', 'videos')) ?></a>
  </nav>

  <div class="nav-end">
    <div class="lang" role="group" aria-label="Language">
      <a href="<?= e($self) ?>?lang=en" lang="en" hreflang="en"<?= $bn ? '' : ' aria-current="true"' ?>>EN</a>
      <a href="<?= e($self) ?>?lang=bn" lang="bn" hreflang="bn"<?= $bn ? ' aria-current="true"' : '' ?>>বাংলা</a>
    </div>
    <button class="burger" type="button" aria-expanded="false" aria-controls="menu"
            data-open="<?= e(copyText('nav', 'menu')) ?>" data-close="<?= e(copyText('nav', 'close')) ?>">
      <span><?= e(copyText('nav', 'menu')) ?></span>
    </button>
  </div>
</header>
<?php
}

/** The footer, the two pop-up windows, and the scripts. $json is the product data, if the page needs it. */
function page_foot(string $page, string $json = ''): void
{
    $home = $page === 'home' ? '' : site_url('');
    $blank = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    ?>
<footer class="foot dark" data-tone="dark">
  <div class="wrap">
    <p class="wordmark"><b><?= e(copyText('brand', 'name')) ?></b><i><?= e(copyText('brand', 'tagline')) ?></i></p>

    <div class="foot-grid">
      <div>
        <p class="org"><?= e(copyText('footer', 'org')) ?></p>
        <?php if (copyText('footer', 'contact') !== ''): ?><p class="contact"><?= nl2br(e(copyText('footer', 'contact'))) ?></p><?php endif; ?>
      </div>
      <nav aria-label="Footer">
        <a href="<?= e($home) ?>#finder"><?= e(copyText('nav', 'finder')) ?></a>
        <a href="<?= e($home) ?>#roots"><?= e(copyText('nav', 'plants')) ?></a>
        <a href="<?= e(site_url('products.php')) ?>"><?= e(copyText('nav', 'products')) ?></a>
        <a href="<?= e(site_url('videos.php')) ?>"><?= e(copyText('nav', 'videos')) ?></a>
      </nav>
      <p class="disclaimer"><?= e(copyText('footer', 'disclaimer')) ?></p>
    </div>

    <p class="legal">
      <span>&copy; <?= e(digits(date('Y'))) ?> <?= e(copyText('footer', 'org')) ?>. <?= e(copyText('footer', 'rights')) ?></span>
      <a href="#top"><?= e(copyText('footer', 'top')) ?> <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 14V3M3.5 7.5 8 3l4.5 4.5"/></svg></a>
    </p>
  </div>
</footer>

<!-- product window -->
<dialog class="sheet" id="sheet" aria-labelledby="sheetTitle">
  <form method="dialog"><button class="x" aria-label="<?= e(copyText('product', 'close')) ?>"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3 3 10 10M13 3 3 13"/></svg></button></form>
  <div class="sheet-grid">
    <div class="shot big"><img id="sheetImg" alt="" src="<?= $blank ?>"></div>
    <div class="sheet-copy">
      <p class="label"><?= e(copyText('product', 'made')) ?></p>
      <h2 id="sheetTitle"></h2>
      <p class="sheet-brand" id="sheetBrand"></p>
      <dl>
        <div id="sheetAreaRow"><dt><?= e(copyText('product', 'area')) ?></dt><dd id="sheetArea"></dd></div>
        <div id="sheetUsesRow"><dt><?= e(copyText('product', 'uses')) ?></dt><dd id="sheetUses"></dd></div>
        <div><dt><?= e(copyText('product', 'packs')) ?></dt><dd><ul class="packs" id="sheetPacks"></ul></dd></div>
        <div id="sheetVideosRow" hidden><dt><?= e(copyText('product', 'watch')) ?></dt><dd><ul class="mini-vids" id="sheetVideos"></ul></dd></div>
      </dl>
      <p class="fine"><?= e(copyText('product', 'mrp')) ?> <?= e(copyText('product', 'advice')) ?></p>
    </div>
  </div>
</dialog>

<!-- video player window -->
<dialog class="player" id="player" aria-label="<?= e(copyText('videos', 'label')) ?>">
  <form method="dialog"><button class="x" aria-label="<?= e(copyText('product', 'close')) ?>"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3 3 10 10M13 3 3 13"/></svg></button></form>
  <div class="player-box" id="playerBox" data-sample="<?= e(copyText('videos', 'sample')) ?>"></div>
</dialog>

<?= $json ?>
<script src="<?= e(asset('js/plants.js')) ?>" defer></script>
<script src="<?= e(asset('js/scene.js')) ?>" defer></script>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
<?php
}
