<?php
/* Wellness Finder: the everyday needs, and which products answer each one. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

$plantKeys = ['amloki','basak','brahmi','cranberry','ginkgo','ispaghula','kalojira','papaya'];

/* one choice per product card (pack sizes of the same brand and form count as one) */
$choices = q('SELECT MIN(id) AS id, brand, form_en FROM products GROUP BY brand, form_en ORDER BY brand, form_en');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'add') {
        $en = trim((string)$_POST['new_en']);
        if ($en !== '') {
            $base = slugify($en); $slug = $base; $n = 2;
            while (q1('SELECT id FROM needs WHERE slug = ?', [$slug])) { $slug = $base . '-' . $n++; }
            run('INSERT INTO needs (slug, name_en, sort_order, plant_key) VALUES (?,?,?,?)', [$slug, $en, 999, 'amloki']);
            log_activity('Added a need: ' . $en);
            redirect('needs.php?id=' . (int)db()->lastInsertId());
        }
        redirect('needs.php');
    }

    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        run('DELETE FROM needs WHERE id = ?', [$id]);
        log_activity('Deleted a need');
        flash('Deleted.');
        redirect('needs.php');
    }

    run('UPDATE needs SET name_en=?, name_bn=?, line_en=?, line_bn=?, body_en=?, body_bn=?, plant_key=?, sort_order=?, is_visible=? WHERE id=?', [
        trim((string)$_POST['name_en']), trim((string)$_POST['name_bn']),
        trim((string)$_POST['line_en']), trim((string)$_POST['line_bn']),
        trim((string)$_POST['body_en']), trim((string)$_POST['body_bn']),
        in_array($_POST['plant_key'] ?? '', $plantKeys, true) ? $_POST['plant_key'] : 'amloki',
        (int)$_POST['sort_order'], isset($_POST['is_visible']) ? 1 : 0, $id,
    ]);

    /* the product rows are simply rewritten from the form */
    run('DELETE FROM need_products WHERE need_id = ?', [$id]);
    $order = 0;
    foreach (($_POST['np'] ?? []) as $row) {
        $pid = (int)($row['product'] ?? 0);
        if (!$pid) continue;
        run('INSERT INTO need_products (need_id, product_id, for_en, for_bn, sort_order) VALUES (?,?,?,?,?)',
            [$id, $pid, trim((string)$row['en']), trim((string)$row['bn']), $order += 10]);
    }
    log_activity('Edited a need');
    flash('Saved.');
    redirect('needs.php?id=' . $id);
}

/* ---------- one need ---------- */
if (isset($_GET['id'])) {
    $n = q1('SELECT * FROM needs WHERE id = ?', [(int)$_GET['id']]);
    if (!$n) redirect('needs.php');
    $rows = q('SELECT * FROM need_products WHERE need_id = ? ORDER BY sort_order, id', [(int)$n['id']]);
    for ($i = 0; $i < 2; $i++) $rows[] = ['product_id' => 0, 'for_en' => '', 'for_bn' => ''];   // two spare rows

    admin_head('Edit need');
    ?>
    <p class="crumb"><a href="needs.php">All needs</a></p>
    <h1><?= e($n['name_en']) ?></h1>

    <form method="post" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">

      <?php lang_pair('Name', 'name', $n['name_en'], $n['name_bn'], false, 'the button text, for example "Digestive comfort"') ?>
      <?php lang_pair('Headline', 'line', $n['line_en'], $n['line_bn'], false, 'one short line shown large when this need is picked') ?>
      <?php lang_pair('A sentence or two', 'body', $n['body_en'], $n['body_bn'], true, 'the everyday moment, in plain words') ?>

      <div class="row">
        <div class="field">
          <label>Icon <em>which plant drawing sits on the button</em></label>
          <select name="plant_key">
            <?php foreach ($plantKeys as $k): ?>
              <option value="<?= e($k) ?>"<?= $n['plant_key'] === $k ? ' selected' : '' ?>><?= e(ucfirst($k)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field narrow">
          <label>Order <em>lower shows first</em></label>
          <input type="number" name="sort_order" value="<?= (int)$n['sort_order'] ?>">
        </div>
        <div class="field narrow">
          <label>Show on the site</label>
          <label class="check"><input type="checkbox" name="is_visible" value="1"<?= $n['is_visible'] ? ' checked' : '' ?>> Visible</label>
        </div>
      </div>

      <h2>Products for this need</h2>
      <p class="muted">Each row is one product and one short line saying who it is good for. Rows show in this order.
         To remove a row, set its product to "none". Two empty rows are always offered at the bottom.</p>
      <table class="list">
        <thead><tr><th>Product</th><th>Good for (English)</th><th>যার জন্য (বাংলা)</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td style="width:26%">
              <select name="np[<?= $i ?>][product]">
                <option value="0">none</option>
                <?php foreach ($choices as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"<?= (int)$r['product_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['brand'] . ' ' . $c['form_en']) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="text" name="np[<?= $i ?>][en]" value="<?= e((string)$r['for_en']) ?>"></td>
            <td><input type="text" name="np[<?= $i ?>][bn]" value="<?= e((string)$r['for_bn']) ?>" lang="bn"></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <div class="actions">
        <button type="submit" class="primary" name="action" value="save">Save</button>
        <button type="submit" class="linky" name="action" value="delete" onclick="return confirm('Delete this need?')">Delete this need</button>
      </div>
    </form>
    <?php
    admin_foot();
    exit;
}

/* ---------- the list ---------- */
$list = q('SELECT n.*, (SELECT COUNT(*) FROM need_products np WHERE np.need_id = n.id) AS c FROM needs n ORDER BY n.sort_order, n.id');

admin_head('Wellness Finder');
?>
<h1>Wellness Finder</h1>
<p class="muted">These are the buttons under "What would you like to support?" on the home page.
   Each one opens a short message and a list of products with a line saying who each is good for.</p>

<table class="list">
  <thead><tr><th>Order</th><th>Need</th><th>বাংলা</th><th>Products</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($list as $n): ?>
    <tr<?= $n['is_visible'] ? '' : ' class="hidden-row"' ?>>
      <td class="muted" style="width:70px"><?= (int)$n['sort_order'] ?></td>
      <td><a href="needs.php?id=<?= (int)$n['id'] ?>"><strong><?= e($n['name_en']) ?></strong></a></td>
      <td lang="bn"><?= e((string)$n['name_bn']) ?></td>
      <td class="muted"><?= (int)$n['c'] ?></td>
      <td><a href="needs.php?id=<?= (int)$n['id'] ?>">Edit</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<section class="block">
  <h2>Add a need</h2>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="row">
      <div class="field"><label>Name in English</label><input type="text" name="new_en" required></div>
    </div>
    <div class="actions"><button type="submit" class="primary">Add and edit</button></div>
  </form>
</section>
<?php admin_foot();
