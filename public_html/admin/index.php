<?php
require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

$counts = [
  'Products'   => (int)q1('SELECT COUNT(*) c FROM products')['c'],
  'Plants'     => (int)q1('SELECT COUNT(*) c FROM ingredients')['c'],
  'Areas'      => (int)q1('SELECT COUNT(*) c FROM areas')['c'],
  'Images'     => (int)q1('SELECT COUNT(*) c FROM media')['c'],
];
$noImage = q("SELECT brand, pack FROM products WHERE image IS NULL OR image = '' ORDER BY brand");
$noBnUse = (int)q1("SELECT COUNT(*) c FROM products WHERE uses_bn IS NULL OR uses_bn = ''")['c'];
$noBnSrc = (int)q1("SELECT COUNT(*) c FROM products WHERE source_bn IS NULL OR source_bn = ''")['c'];
$recent  = q('SELECT a.what, a.created_at, u.name FROM activity a
              LEFT JOIN users u ON u.id = a.user_id
              ORDER BY a.id DESC LIMIT 12');

admin_head('Dashboard');
?>
<h1>Dashboard</h1>

<div class="cards">
  <?php foreach ($counts as $label => $n): ?>
    <div class="card"><b><?= $n ?></b><span><?= e($label) ?></span></div>
  <?php endforeach; ?>
</div>

<h2>Still to do</h2>
<div class="todo">
  <div class="todo-item">
    <b><?= count($noImage) ?></b>
    <span>product<?= count($noImage) === 1 ? '' : 's' ?> with no image</span>
    <?php if ($noImage): ?>
      <p><?php foreach ($noImage as $p) echo e($p['brand'] . ' ' . $p['pack']) . '<br>'; ?></p>
    <?php endif; ?>
    <a href="products.php?missing=image">Fix these</a>
  </div>
  <div class="todo-item">
    <b><?= $noBnUse ?></b>
    <span>products with no Bangla description</span>
    <p>These show the English text until a Bangla version is added.</p>
    <a href="products.php?missing=bn">Fix these</a>
  </div>
  <div class="todo-item">
    <b><?= $noBnSrc ?></b>
    <span>products with no Bangla ingredient name</span>
    <p>Same thing: English is shown until Bangla is filled in.</p>
    <a href="products.php?missing=bn">Fix these</a>
  </div>
</div>

<h2>Recent changes</h2>
<?php if (!$recent): ?>
  <p class="muted">Nothing yet.</p>
<?php else: ?>
  <table class="list">
    <?php foreach ($recent as $r): ?>
      <tr>
        <td class="muted" style="width:150px"><?= e($r['created_at']) ?></td>
        <td class="muted" style="width:140px"><?= e($r['name'] ?? 'system') ?></td>
        <td><?= e($r['what']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
<?php admin_foot();
