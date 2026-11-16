<?php
/* Plants: the eight that lead the home page. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

$plantKeys = ['basak','amloki','ginkgo','brahmi','papaya','kalojira','ispaghula','cranberry'];
$products  = q('SELECT id, brand, pack FROM products ORDER BY brand, pack');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $f = [
            'plant_key'  => (string)$_POST['plant_key'],
            'name_en'    => trim((string)$_POST['name_en']),
            'name_bn'    => trim((string)$_POST['name_bn']),
            'botanical'  => trim((string)$_POST['botanical']),
            'common_en'  => trim((string)($_POST['common_en'] ?? '')) ?: null,
            'part_en'    => trim((string)$_POST['part_en']),
            'part_bn'    => trim((string)$_POST['part_bn']),
            'known_en'   => trim((string)$_POST['known_en']),
            'known_bn'   => trim((string)$_POST['known_bn']),
            'does_en'    => trim((string)$_POST['does_en']),
            'does_bn'    => trim((string)$_POST['does_bn']),
            'product_id' => ((int)$_POST['product_id']) ?: null,
            'sort_order' => (int)$_POST['sort_order'],
            'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
        ];
        // the photograph and the clip, only changed when a new file is chosen
        $notes = [];
        $tag   = 'plant-' . slugify($f['name_en']);
        if ($new = save_film_upload('image', 'image', $tag, $notes)) $f['image'] = $new;
        if ($new = save_film_upload('video', 'video', $tag, $notes)) $f['video'] = $new;
        if (isset($_POST['remove_video'])) $f['video'] = null;
        if ($id) {
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($f)));
            run("UPDATE ingredients SET $set WHERE id = :id", $f + ['id' => $id]);
            log_activity('Edited plant: ' . $f['name_en']);
        } else {
            $base = slugify($f['name_en']); $slug = $base; $n = 2;
            while (q1('SELECT id FROM ingredients WHERE slug = ?', [$slug])) { $slug = $base . '-' . $n++; }
            $f['slug'] = $slug;
            $cols = implode(', ', array_keys($f));
            $vals = implode(', ', array_map(fn($k) => ":$k", array_keys($f)));
            run("INSERT INTO ingredients ($cols) VALUES ($vals)", $f);
            $id = (int)db()->lastInsertId();
            log_activity('Added plant: ' . $f['name_en']);
        }
        flash($notes ? 'Saved, but: ' . implode(' ', $notes) : 'Saved.');
        redirect('ingredients.php?id=' . $id);
    }

    if ($action === 'delete') {
        run('DELETE FROM ingredients WHERE id = ?', [(int)$_POST['id']]);
        log_activity('Deleted a plant');
        flash('Deleted.');
        redirect('ingredients.php');
    }
}

if (isset($_GET['id']) || isset($_GET['new'])) {
    $g = isset($_GET['id']) ? q1('SELECT * FROM ingredients WHERE id = ?', [(int)$_GET['id']]) : null;
    if (!$g) $g = ['id'=>0,'plant_key'=>'basak','name_en'=>'','name_bn'=>'','botanical'=>'','common_en'=>'',
                   'part_en'=>'','part_bn'=>'','known_en'=>'','known_bn'=>'','does_en'=>'','does_bn'=>'',
                   'image'=>null,'video'=>null,'product_id'=>null,'sort_order'=>999,'is_visible'=>1];

    admin_head($g['id'] ? 'Edit plant' : 'New plant');
    ?>
    <p class="crumb"><a href="ingredients.php">All plants</a></p>
    <h1><?= $g['id'] ? e($g['name_en']) : 'New plant' ?></h1>

    <form method="post" class="form" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">

      <div class="row">
        <div class="field">
          <label>Drawing <em>only used if there is no photograph</em></label>
          <select name="plant_key">
            <?php foreach ($plantKeys as $k): ?>
              <option value="<?= e($k) ?>"<?= $g['plant_key'] === $k ? ' selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Botanical name <em>shown in italics</em></label>
          <input type="text" name="botanical" value="<?= e($g['botanical']) ?>" required>
        </div>
        <div class="field">
          <label>English common name <em>optional, shown small in English only, for example "Malabar nut"</em></label>
          <input type="text" name="common_en" value="<?= e((string)($g['common_en'] ?? '')) ?>">
        </div>
      </div>

      <?php lang_pair('Common name', 'name', $g['name_en'], $g['name_bn'], false, 'the Bangla name is the big one on the card'); ?>
      <?php lang_pair('Part used', 'part', $g['part_en'], $g['part_bn']); ?>
      <?php lang_pair('What people already know', 'known', $g['known_en'], $g['known_bn'], true); ?>
      <?php lang_pair('What it does', 'does', $g['does_en'], $g['does_bn'], true); ?>

      <div class="row">
        <div class="field">
          <label>Photograph <em>JPG or WebP, wide (16:9). Plant to the right, dark space on the left for the words</em></label>
          <?php if (!empty($g['image'])): ?><img class="vprev" src="../uploads/video/<?= e(rawurlencode($g['image'])) ?>" alt=""><?php endif; ?>
          <input type="file" name="image" accept="image/jpeg,image/webp">
        </div>
        <div class="field">
          <label>Clip <em>MP4, 4 to 6 seconds, silent, one slow camera move. It plays as the visitor scrolls</em></label>
          <?php if (!empty($g['video'])): ?>
            <video class="vprev" src="../uploads/video/<?= e(rawurlencode($g['video'])) ?>" muted loop playsinline controls preload="metadata"></video>
            <label class="check"><input type="checkbox" name="remove_video" value="1"> Remove the clip and show only the photograph</label>
          <?php endif; ?>
          <input type="file" name="video" accept="video/mp4">
        </div>
      </div>

      <div class="row">
        <div class="field">
          <label>Becomes which product</label>
          <select name="product_id">
            <option value="">Not set</option>
            <?php foreach ($products as $p): ?>
              <option value="<?= (int)$p['id'] ?>"<?= (int)$g['product_id'] === (int)$p['id'] ? ' selected' : '' ?>>
                <?= e($p['brand'] . ' · ' . $p['pack']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field narrow">
          <label>Order</label>
          <input type="number" name="sort_order" value="<?= (int)$g['sort_order'] ?>">
        </div>
        <div class="field narrow">
          <label>Show on the site</label>
          <label class="check"><input type="checkbox" name="is_visible" value="1"<?= $g['is_visible'] ? ' checked' : '' ?>> Visible</label>
        </div>
      </div>

      <div class="actions">
        <button type="submit" class="primary">Save</button>
        <a href="ingredients.php" class="ghost">Cancel</a>
      </div>
    </form>

    <?php if ($g['id']): ?>
      <form method="post" class="danger" data-confirm="Remove this plant from the home page?">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
        <button type="submit">Delete this plant</button>
      </form>
    <?php endif; ?>
    <?php
    admin_foot();
    exit;
}

$rows = q('SELECT i.*, p.brand, p.pack FROM ingredients i
           LEFT JOIN products p ON p.id = i.product_id
           ORDER BY i.sort_order');

admin_head('Plants');
?>
<div class="head">
  <h1>Plants <span class="muted"><?= count($rows) ?></span></h1>
  <a href="ingredients.php?new=1" class="primary">Add a plant</a>
</div>
<p class="muted">These are the plants shown in the sideways-scrolling band on the home page, in this order.</p>

<table class="list">
  <thead><tr><th>Order</th><th>Name</th><th>Botanical</th><th>Becomes</th><th>Drawing</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr<?= $r['is_visible'] ? '' : ' class="hidden-row"' ?>>
      <td class="muted"><?= (int)$r['sort_order'] ?></td>
      <td><b lang="bn"><?= e($r['name_bn'] ?: $r['name_en']) ?></b><br><span class="muted"><?= e($r['name_en']) ?></span></td>
      <td class="muted"><i><?= e($r['botanical']) ?></i></td>
      <td><?= e($r['brand'] ? $r['brand'] . ' · ' . $r['pack'] : '-') ?></td>
      <td class="muted"><?= e($r['plant_key']) ?></td>
      <td><a href="ingredients.php?id=<?= (int)$r['id'] ?>">Edit</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php admin_foot();
