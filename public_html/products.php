<?php
/* =====================================================================
   products.php: every product, with filters, search and sort.
   Focus brands are listed first. A click on a card opens its details.
   Shared pieces are in private_app/site_parts.php.
   ===================================================================== */

require __DIR__ . '/../private_app/site_parts.php';

[$groups, $byProduct] = load_groups();
$talks = load_talks();
$total = count($groups);

/* only show filter buttons for health areas that have products */
$has = [];
foreach ($groups as $g) if ($g['areaKey'] !== '') $has[$g['areaKey']] = true;
$areas = array_values(array_filter(
    q('SELECT * FROM areas WHERE is_visible = 1 ORDER BY sort_order, name_en'),
    fn($a) => isset($has[$a['slug']])
));

page_head('products', copyText('nav', 'products') . ' | ' . copyText('brand', 'name'), copyText('catalogue', 'title'));
page_nav('products');
?>

<main id="main">
<section class="catalogue light page-top" id="top" data-tone="light">
  <div class="wrap">
    <p class="label" data-r><?= e(copyText('catalogue', 'label')) ?></p>
    <h1 class="display" data-words><?= e(copyText('catalogue', 'title')) ?></h1>
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

    <div class="empty" id="empty" hidden>
      <p><?= e(copyText('catalogue', 'empty')) ?></p>
      <button type="button" class="btn dark-btn" id="clear"><?= e(copyText('catalogue', 'clear')) ?></button>
    </div>

    <p class="fine page-note"><?= e(copyText('product', 'mrp')) ?> <?= e(copyText('footer', 'disclaimer')) ?></p>
  </div>
</section>
</main>

<?php page_foot('products', products_json($groups, $byProduct, $talks)); ?>
