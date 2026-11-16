<?php
/* Questions: the common questions near the bottom of the home page. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        run('DELETE FROM faqs WHERE id = ?', [$id]);
        log_activity('Deleted a question');
        flash('Deleted.');
        redirect('faqs.php');
    }

    $v = [trim((string)$_POST['q_en']), trim((string)$_POST['q_bn']), trim((string)$_POST['a_en']), trim((string)$_POST['a_bn']),
          (int)$_POST['sort_order'], isset($_POST['is_visible']) ? 1 : 0];
    if ($v[0] === '' || $v[2] === '') {
        flash('A question needs the question and the answer, at least in English.');
        redirect('faqs.php' . ($id ? '?id=' . $id : ''));
    }
    if ($id) run('UPDATE faqs SET q_en=?, q_bn=?, a_en=?, a_bn=?, sort_order=?, is_visible=? WHERE id=?', [...$v, $id]);
    else     run('INSERT INTO faqs (q_en, q_bn, a_en, a_bn, sort_order, is_visible) VALUES (?,?,?,?,?,?)', $v);
    log_activity(($id ? 'Edited' : 'Added') . ' a question');
    flash('Saved.');
    redirect('faqs.php');
}

$edit = isset($_GET['id']) ? q1('SELECT * FROM faqs WHERE id = ?', [(int)$_GET['id']]) : null;
$f    = $edit ?: ['id'=>0,'q_en'=>'','q_bn'=>'','a_en'=>'','a_bn'=>'','sort_order'=>999,'is_visible'=>1];
$list = q('SELECT * FROM faqs ORDER BY sort_order, id');

admin_head('Questions');
?>
<h1>Questions</h1>
<p class="muted">The common questions near the bottom of the home page. Keep answers short, and send people to a physician for anything personal.</p>

<table class="list">
  <thead><tr><th>Order</th><th>Question</th><th>বাংলা</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($list as $r): ?>
    <tr<?= $r['is_visible'] ? '' : ' class="hidden-row"' ?>>
      <td class="muted" style="width:70px"><?= (int)$r['sort_order'] ?></td>
      <td><a href="faqs.php?id=<?= (int)$r['id'] ?>"><strong><?= e($r['q_en']) ?></strong></a></td>
      <td lang="bn"><?= e((string)$r['q_bn']) ?></td>
      <td><a href="faqs.php?id=<?= (int)$r['id'] ?>">Edit</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<section class="block">
  <h2><?= $f['id'] ? 'Edit question' : 'Add a question' ?></h2>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
    <?php lang_pair('Question', 'q', $f['q_en'], $f['q_bn']) ?>
    <?php lang_pair('Answer', 'a', $f['a_en'], $f['a_bn'], true) ?>
    <div class="row">
      <div class="field narrow"><label>Order <em>lower shows first</em></label><input type="number" name="sort_order" value="<?= (int)$f['sort_order'] ?>"></div>
      <div class="field narrow"><label>Show on the site</label><label class="check"><input type="checkbox" name="is_visible" value="1"<?= $f['is_visible'] ? ' checked' : '' ?>> Visible</label></div>
    </div>
    <div class="actions">
      <button type="submit" class="primary" name="action" value="save"><?= $f['id'] ? 'Save' : 'Add question' ?></button>
      <?php if ($f['id']): ?>
        <a href="faqs.php">Cancel</a>
        <button type="submit" class="linky" name="action" value="delete" onclick="return confirm('Delete this question?')">Delete</button>
      <?php endif; ?>
    </div>
  </form>
</section>
<?php admin_foot();
