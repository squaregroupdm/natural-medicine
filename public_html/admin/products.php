<?php
/* =====================================================================
   Products: the list, and the form for editing one product.
   ===================================================================== */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

$areas = q('SELECT * FROM areas ORDER BY sort_order, name_en');

/* ---------------- saving ---------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id     = (int)($_POST['id'] ?? 0);
        $brand  = trim((string)$_POST['brand']);
        $pack   = trim((string)$_POST['pack']);
        $fields = [
            'brand'      => $brand,
            'form_en'    => trim((string)$_POST['form_en']),
            'form_bn'    => trim((string)$_POST['form_bn']),
            'source_en'  => trim((string)$_POST['source_en']),
            'source_bn'  => trim((string)$_POST['source_bn']),
            'pack'       => $pack,
            'mrp'        => (float)$_POST['mrp'],
            'area_id'    => ((int)$_POST['area_id']) ?: null,
            'uses_en'    => trim((string)$_POST['uses_en']),
            'uses_bn'    => trim((string)$_POST['uses_bn']),
            'image'      => trim((string)$_POST['image']),
            'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
            'is_featured'=> isset($_POST['is_featured']) ? 1 : 0,
            'sort_order' => (int)$_POST['sort_order'],
        ];

        if ($brand === '' || $pack === '') {
            flash('A product needs a brand name and a pack size.');
            redirect('products.php' . ($id ? '?id=' . $id : '?new=1'));
        }

        if ($id) {
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
            run("UPDATE products SET $set WHERE id = :id", $fields + ['id' => $id]);
            log_activity("Edited product: $brand $pack");
            flash('Saved.');
        } else {
            // make a web address for it that is not already taken
            $base = slugify($brand . '-' . $pack);
            $slug = $base; $n = 2;
            while (q1('SELECT id FROM products WHERE slug = ?', [$slug])) { $slug = $base . '-' . $n++; }
            $fields['slug'] = $slug;
            $cols = implode(', ', array_keys($fields));
            $vals = implode(', ', array_map(fn($k) => ":$k", array_keys($fields)));
            run("INSERT INTO products ($cols) VALUES ($vals)", $fields);
            $id = (int)db()->lastInsertId();
            log_activity("Added product: $brand $pack");
            flash('Added.');
        }
        redirect('products.php?id=' . $id);
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $p  = q1('SELECT brand, pack FROM products WHERE id = ?', [$id]);
        run('DELETE FROM products WHERE id = ?', [$id]);
        log_activity('Deleted product: ' . ($p['brand'] ?? '') . ' ' . ($p['pack'] ?? ''));
        flash('Deleted.');
        redirect('products.php');
    }
}

/* ---------------- the edit form ---------------- */

$editing = isset($_GET['id']) || isset($_GET['new']);

if ($editing) {
    $p = isset($_GET['id'])
        ? q1('SELECT * FROM products WHERE id = ?', [(int)$_GET['id']])
        : null;
    if (!$p) {
        $p = ['id'=>0,'brand'=>'','form_en'=>'','form_bn'=>'','source_en'=>'','source_bn'=>'',
              'pack'=>'','mrp'=>0,'area_id'=>null,'uses_en'=>'','uses_bn'=>'','image'=>'',
              'is_visible'=>1,'is_featured'=>0,'sort_order'=>999];
    }
    $images = q("SELECT filename FROM media WHERE kind='image' ORDER BY filename");
    // Keep whatever image this product already uses in the list, even if
    // it was put on the server by hand and never registered as media.
    $known = array_column($images, 'filename');
    if ($p['image'] !== '' && !in_array($p['image'], $known, true)) {
        array_unshift($images, ['filename' => $p['image']]);
    }

    admin_head($p['id'] ? 'Edit ' . $p['brand'] : 'New product');
    ?>
    <p class="crumb"><a href="products.php">All products</a></p>
    <h1><?= $p['id'] ? e($p['brand'] . ' ' . $p['pack']) : 'New product' ?></h1>

    <form method="post" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

      <div class="row">
        <div class="field">
          <label>Brand name <em>shown large on the card</em></label>
          <input type="text" name="brand" value="<?= e($p['brand']) ?>" required>
        </div>
        <div class="field">
          <label>Pack size <em>for example 100 ml or 30&rsquo;s</em></label>
          <input type="text" name="pack" value="<?= e($p['pack']) ?>" required>
        </div>
        <div class="field narrow">
          <label>Price in Taka</label>
          <input type="number" name="mrp" step="1" min="0" value="<?= e((string)(int)$p['mrp']) ?>" required>
        </div>
      </div>

      <?php lang_pair('Form', 'form', $p['form_en'], $p['form_bn'], false, 'Capsule, Syrup, Cream and so on'); ?>
      <?php lang_pair('Made from', 'source', $p['source_en'], $p['source_bn'], false, 'the plant or molecule, shown first on the card'); ?>
      <?php lang_pair('What it is for', 'uses', $p['uses_en'], $p['uses_bn'], true, 'indications, no dosage'); ?>

      <div class="row">
        <div class="field">
          <label>Health area</label>
          <select name="area_id">
            <option value="">Not set</option>
            <?php foreach ($areas as $a): ?>
              <option value="<?= (int)$a['id'] ?>"<?= (int)$p['area_id'] === (int)$a['id'] ? ' selected' : '' ?>>
                <?= e($a['name_en']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field narrow">
          <label>Order <em>lower shows first</em></label>
          <input type="number" name="sort_order" value="<?= (int)$p['sort_order'] ?>">
        </div>
        <div class="field narrow">
          <label>Show on the site</label>
          <label class="check"><input type="checkbox" name="is_visible" value="1"<?= $p['is_visible'] ? ' checked' : '' ?>> Visible</label>
        </div>
        <div class="field narrow">
          <label>Focus brand <em>listed first, and on the home page</em></label>
          <label class="check"><input type="checkbox" name="is_featured" value="1"<?= !empty($p['is_featured']) ? ' checked' : '' ?>> Featured</label>
        </div>
      </div>

      <div class="field">
        <label>Image <em>upload new ones on the Images page</em></label>
        <div class="pair">
          <div>
            <select name="image" onchange="document.getElementById('prev').src = this.value ? '../uploads/products/' + encodeURIComponent(this.value) : '';">
              <option value="">No image</option>
              <?php foreach ($images as $m): ?>
                <option value="<?= e($m['filename']) ?>"<?= $p['image'] === $m['filename'] ? ' selected' : '' ?>>
                  <?= e($m['filename']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="preview">
            <img id="prev" alt="" src="<?= e($p['image'] ? '../uploads/products/' . rawurlencode($p['image']) : '') ?>">
          </div>
        </div>
      </div>

      <div class="actions">
        <button type="submit" class="primary">Save</button>
        <a href="products.php" class="ghost">Cancel</a>
      </div>
    </form>

    <?php if ($p['id']): ?>
      <form method="post" class="danger" data-confirm="Delete this product for good?">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <button type="submit">Delete this product</button>
      </form>
    <?php endif; ?>
    <?php
    admin_foot();
    exit;
}

/* ---------------- the list ---------------- */

$search  = trim((string)($_GET['q'] ?? ''));
$areaId  = (int)($_GET['area'] ?? 0);
$missing = (string)($_GET['missing'] ?? '');

$sql  = 'SELECT p.*, a.name_en AS area FROM products p LEFT JOIN areas a ON a.id = p.area_id WHERE 1';
$args = [];
if ($search !== '') {
    $sql .= ' AND (p.brand LIKE ? OR p.source_en LIKE ? OR p.uses_en LIKE ?)';
    $like = '%' . $search . '%';
    array_push($args, $like, $like, $like);
}
if ($areaId)                 { $sql .= ' AND p.area_id = ?'; $args[] = $areaId; }
if ($missing === 'image')    { $sql .= " AND (p.image IS NULL OR p.image = '')"; }
if ($missing === 'bn')       { $sql .= " AND (p.uses_bn IS NULL OR p.uses_bn = '' OR p.source_bn IS NULL OR p.source_bn = '')"; }
$sql .= ' ORDER BY p.brand, p.pack';

$rows = q($sql, $args);

admin_head('Products');
?>
<div class="head">
  <h1>Products <span class="muted"><?= count($rows) ?></span></h1>
  <a href="products.php?new=1" class="primary">Add a product</a>
</div>

<form method="get" class="filters">
  <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search brand, plant or use">
  <select name="area">
    <option value="">All health areas</option>
    <?php foreach ($areas as $a): ?>
      <option value="<?= (int)$a['id'] ?>"<?= $areaId === (int)$a['id'] ? ' selected' : '' ?>><?= e($a['name_en']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="missing">
    <option value="">Everything</option>
    <option value="image"<?= $missing === 'image' ? ' selected' : '' ?>>Missing an image</option>
    <option value="bn"<?= $missing === 'bn' ? ' selected' : '' ?>>Missing Bangla</option>
  </select>
  <button type="submit">Filter</button>
  <a href="products.php" class="ghost">Clear</a>
</form>

<table class="list">
  <thead>
    <tr><th></th><th>Made from</th><th>Brand</th><th>Pack</th><th>Price</th><th>Area</th><th>Bangla</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r):
      $bnDone = trim((string)$r['uses_bn']) !== '' && trim((string)$r['source_bn']) !== ''; ?>
    <tr<?= $r['is_visible'] ? '' : ' class="hidden-row"' ?>>
      <td class="thumb">
        <?php if ($r['image']): ?>
          <img src="../uploads/products/<?= e(rawurlencode($r['image'])) ?>" alt="">
        <?php else: ?><span class="noimg">no image</span><?php endif; ?>
      </td>
      <td><?= e($r['source_en']) ?></td>
      <td><b><?= e($r['brand']) ?></b></td>
      <td class="muted"><?= e($r['pack']) ?></td>
      <td class="muted"><?= e(money((float)$r['mrp'])) ?></td>
      <td class="muted"><?= e($r['area'] ?? '') ?></td>
      <td><?= $bnDone ? '<span class="ok">done</span>' : '<span class="warn">needed</span>' ?></td>
      <td><a href="products.php?id=<?= (int)$r['id'] ?>">Edit</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php admin_foot();
