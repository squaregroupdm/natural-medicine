<?php
/* Page text: every headline and paragraph on the site, grouped by section. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // page copy
    foreach (($_POST['copy'] ?? []) as $id => $vals) {
        run('UPDATE copy SET val_en = ?, val_bn = ? WHERE id = ?',
            [trim((string)$vals['en']), trim((string)$vals['bn']), (int)$id]);
    }
    // the three statistics
    foreach (($_POST['fig'] ?? []) as $id => $vals) {
        run('UPDATE figures SET number = ?, suffix = ?, note_en = ?, note_bn = ? WHERE id = ?',
            [trim((string)$vals['number']), trim((string)$vals['suffix']),
             trim((string)$vals['en']), trim((string)$vals['bn']), (int)$id]);
    }
    log_activity('Edited page text');
    flash('Saved.');
    redirect('copy.php');
}

$added = sync_copy();   // picks up any wording added in copy_defaults.php
// wording from an older version of the site that no page uses any more is not listed
$live = [];
foreach (copy_defaults() as $d) $live[$d[0] . '.' . $d[1]] = true;
$rows  = array_values(array_filter(q('SELECT * FROM copy ORDER BY sort_order, id'), fn($r) => isset($live[$r['section'] . '.' . $r['k']])));
$figs = q('SELECT * FROM figures ORDER BY sort_order');

$bySection = [];
foreach ($rows as $r) $bySection[$r['section']][] = $r;

$titles = [
    'brand'     => 'Brand name and tagline',
    'nav'       => 'Top bar',
    'hero'      => 'Opening screen',
    'why'       => 'Why natural',
    'materia'   => 'From the root (the plants)',
    'plate'     => 'Plant product card',
    'finder'    => 'Wellness Finder',
    'trust'     => 'Trusted by Square',
    'videos'    => 'Videos',
    'catalogue' => 'Product list',
    'product'   => 'Product detail window',
    'faq'       => 'Questions',
    'close'     => 'Closing screen',
    'footer'    => 'Footer',
];

admin_head('Page text');
?>
<h1>Page text</h1>
<p class="muted">Everything written on the website. Leave a Bangla box empty and the English shows instead.</p>

<form method="post" class="form">
  <?= csrf_field() ?>

  <?php foreach ($bySection as $section => $items): ?>
    <section class="block">
      <h2><?= e($titles[$section] ?? $section) ?></h2>
      <?php foreach ($items as $r):
          $long = mb_strlen((string)$r['val_en']) > 70 || mb_strlen((string)$r['val_bn']) > 70; ?>
        <div class="field">
          <label><?= e($r['label']) ?></label>
          <div class="pair">
            <div>
              <span class="tag">English</span>
              <?php if ($long): ?>
                <textarea name="copy[<?= (int)$r['id'] ?>][en]" rows="3"><?= e($r['val_en']) ?></textarea>
              <?php else: ?>
                <input type="text" name="copy[<?= (int)$r['id'] ?>][en]" value="<?= e($r['val_en']) ?>">
              <?php endif; ?>
            </div>
            <div>
              <span class="tag bn">বাংলা</span>
              <?php if ($long): ?>
                <textarea name="copy[<?= (int)$r['id'] ?>][bn]" rows="3" lang="bn"><?= e($r['val_bn']) ?></textarea>
              <?php else: ?>
                <input type="text" name="copy[<?= (int)$r['id'] ?>][bn]" value="<?= e($r['val_bn']) ?>" lang="bn">
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>

  <section class="block">
    <h2>The three statistics</h2>
    <p class="muted">These count up from zero as the visitor scrolls. They appear as a line of proof under the three answers in "Why natural".</p>
    <?php foreach ($figs as $f): ?>
      <div class="field">
        <div class="row">
          <div class="field narrow">
            <label>Number</label>
            <input type="text" name="fig[<?= (int)$f['id'] ?>][number]" value="<?= e($f['number']) ?>">
          </div>
          <div class="field narrow">
            <label>After it <em>% or m</em></label>
            <input type="text" name="fig[<?= (int)$f['id'] ?>][suffix]" value="<?= e($f['suffix']) ?>">
          </div>
        </div>
        <div class="pair">
          <div>
            <span class="tag">English</span>
            <textarea name="fig[<?= (int)$f['id'] ?>][en]" rows="2"><?= e($f['note_en']) ?></textarea>
          </div>
          <div>
            <span class="tag bn">বাংলা</span>
            <textarea name="fig[<?= (int)$f['id'] ?>][bn]" rows="2" lang="bn"><?= e($f['note_bn']) ?></textarea>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <div class="actions sticky">
    <button type="submit" class="primary">Save all changes</button>
  </div>
</form>
<?php admin_foot();
