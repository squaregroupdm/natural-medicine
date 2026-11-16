<?php
/* =====================================================================
   videos.php: the video collection.

   Three shelves, each hidden while it is empty:
     creators   people talking about using the products
     doctors    doctors explaining (podcasts, interviews)
     why        short "why natural" explainers

   Videos are added in Admin > Videos by pasting a YouTube link. A click
   on a card plays the video in a window on this page.
   ===================================================================== */

require __DIR__ . '/../private_app/site_parts.php';

$talks   = load_talks();
$shelves = [
    ['creators', 'creator', 'creators', 'creators_b'],
    ['doctors',  'doctor',  'doctors',  'doctors_b'],
    ['why',      'why',     'why',      'why_b'],
];

page_head('videos', copyText('nav', 'videos') . ' | ' . copyText('brand', 'name'), copyText('videos', 'title'));
page_nav('videos');
?>

<main id="main">
<section class="videos dark page-top" id="top" data-tone="dark">
  <div class="scene-bg" aria-hidden="true"><canvas data-foliage="wall"></canvas></div>
  <div class="wrap">
    <p class="label" data-r><?= e(copyText('videos', 'label')) ?></p>
    <h1 class="display" data-words><?= e(copyText('videos', 'title')) ?></h1>

    <?php if (!$talks['all']): ?>
      <p class="lede empty-note" data-r><?= e(copyText('videos', 'empty')) ?></p>
    <?php endif; ?>

    <?php foreach ($shelves as [$anchor, $kind, $title, $line]): if (!$talks[$kind]) continue; ?>
      <div class="shelf-block" id="<?= $anchor ?>">
        <header class="shelf-head">
          <div>
            <h2 class="display sm" data-r><?= e(copyText('videos', $title)) ?></h2>
            <p class="lede" data-r><?= e(copyText('videos', $line)) ?></p>
          </div>
        </header>
        <ul class="vids" data-r><?php foreach ($talks[$kind] as $t) echo talk_card($t); ?></ul>
      </div>
    <?php endforeach; ?>

    <?php if ($talks['all']): ?><p class="fine page-note"><?= e(copyText('videos', 'opinion')) ?></p><?php endif; ?>
  </div>
</section>
</main>

<?php page_foot('videos'); ?>
