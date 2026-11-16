<?php
/* =====================================================================
   products.php: every product, with filters, search and sort.
   Focus brands are listed first. A click on a card opens its details.
   Shared pieces are in private_app/site_parts.php.
   ===================================================================== */

require __DIR__ . '/../private_app/site_parts.php';

[$groups, $byProduct] = load_groups();
$talks = load_talks();
page_head('products', copyText('nav', 'products') . ' | ' . copyText('brand', 'name'), copyText('catalogue', 'title'));
page_nav('products');
?>

<main id="main">
<section class="catalogue light page-top" id="top" data-tone="light">
  <?php catalogue_block($groups, 'h1'); ?>
  <div class="wrap">
    <p class="fine page-note"><?= e(copyText('product', 'mrp')) ?> <?= e(copyText('footer', 'disclaimer')) ?></p>
  </div>
</section>
</main>

<?php page_foot('products', products_json($groups, $byProduct, $talks)); ?>
