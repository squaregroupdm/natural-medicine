<?php
/* Health areas: rename, reorder, hide. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (($_POST['action'] ?? '') === 'add') {
        $en = trim((string)$_POST['new_en']);
        if ($en !== '') {
            $base = slugify($en); $slug = $base; $n = 2;
            while (q1('SELECT id FROM areas WHERE slug = ?', [$slug])) { $slug = $base . '-' . $n++; }
            run('INSERT INTO areas (slug, name_en, name_bn, sort_order) VALUES (?,?,?,?)',
                [$slug, $en, trim((string)$_POST['new_bn']), 999]);
            log_activity('Added health area: ' . $en);
            flash('Added.');
        }
        redirect('areas.php');
    }

    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int)$_POST['id'];
        $n  = (int)q1('SELECT COUNT(*) c FROM products WHERE area_id = ?', [$id])['c'];
        if ($n) {
            flash("That area still has $n product(s). Move them first.");
        } else {
            run('DELETE FROM areas WHERE id = ?', [$id]);
            log_activity('Deleted a health area');
            flash('Deleted.');
        }
        redirect('areas.php');
    }

    foreach (($_POST['area'] ?? []) as $id => $v) {
        run('UPDATE areas SET name_en = ?, name_bn = ?, sort_order = ?, is_visible = ? WHERE id = ?', [
            trim((string)$v['en']), trim((string)$v['bn']), (int)$v['order'],
            isset($v['vis']) ? 1 : 0, (int)$id,
        ]);
    }
    log_activity('Edited health areas');
    flash('Saved.');
    redirect('areas.php');
}

$rows = q('SELECT a.*, (SELECT COUNT(*) FROM products p WHERE p.area_id = a.id) AS n
           FROM areas a ORDER BY a.sort_order, a.name_en');

admin_head('Health areas');
?>
<h1>Health areas</h1>
<p class="muted">These are the buttons on the home page and the filters above the product list.</p>

<form method="post" class="form">
  <?= csrf_field() ?>
  <table class="list">
    <thead><tr><th>Order</th><th>English</th><th>বাংলা</th><th>Products</th><th>Show</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $a): ?>
      <tr>
        <td style="width:80px"><input type="number" name="area[<?= (int)$a['id'] ?>][order]" value="<?= (int)$a['sort_order'] ?>"></td>
        <td><input type="text" name="area[<?= (int)$a['id'] ?>][en]" value="<?= e($a['name_en']) ?>"></td>
        <td><input type="text" name="area[<?= (int)$a['id'] ?>][bn]" value="<?= e($a['name_bn']) ?>" lang="bn"></td>
        <td class="muted"><?= (int)$a['n'] ?></td>
        <td><input type="checkbox" name="area[<?= (int)$a['id'] ?>][vis]" value="1"<?= $a['is_visible'] ? ' checked' : '' ?>></td>
        <td><?php if (!$a['n']): ?>
          <button type="submit" class="linky" name="action" value="delete"
                  onclick="this.form.querySelector('#delid').value=<?= (int)$a['id'] ?>">Delete</button>
        <?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <input type="hidden" name="id" id="delid" value="">
  <div class="actions"><button type="submit" class="primary">Save changes</button></div>
</form>

<section class="block">
  <h2>Add an area</h2>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="row">
      <div class="field"><label>English</label><input type="text" name="new_en" required></div>
      <div class="field"><label>বাংলা</label><input type="text" name="new_bn" lang="bn"></div>
    </div>
    <div class="actions"><button type="submit" class="primary">Add</button></div>
  </form>
</section>
<?php admin_foot();
