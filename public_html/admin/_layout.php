<?php
/* Shared top and bottom of every admin page. */

function admin_head(string $title): void
{
    $u = current_user();
    $here = basename($_SERVER['SCRIPT_NAME']);
    $nav = [
        'index.php'       => 'Dashboard',
        'products.php'    => 'Products',
        'ingredients.php' => 'Plants',
        'needs.php'       => 'Wellness Finder',
        'talks.php'       => 'Videos',
        'faqs.php'        => 'Questions',
        'areas.php'       => 'Health areas',
        'copy.php'        => 'Page text',
        'media.php'       => 'Images',
        'videos.php'      => 'Film',
    ];
    ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Square Naturals admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="bar">
  <div class="bar-brand">Square <span>Naturals</span></div>
  <nav class="bar-nav">
    <?php foreach ($nav as $file => $label): ?>
      <a href="<?= e($file) ?>"<?= $here === $file ? ' class="on"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="bar-right">
    <a href="../index.php" target="_blank" class="ghost">View site</a>
    <span class="who"><?= e($u['name'] ?? '') ?></span>
    <a href="logout.php" class="ghost">Sign out</a>
  </div>
</header>
<main class="wrap">
<?php
    if ($m = flash()) echo '<div class="flash">' . e($m) . '</div>';
}

function admin_foot(): void
{
    ?>
</main>
<script>
// Ask before deleting anything.
document.addEventListener('submit', function (ev) {
  const f = ev.target;
  if (f.dataset.confirm && !confirm(f.dataset.confirm)) ev.preventDefault();
});
</script>
</body>
</html>
<?php
}

/**
 * A pair of boxes for one piece of text: English on the left,
 * Bangla on the right. Used on every editing screen.
 */
function lang_pair(string $label, string $name, ?string $en, ?string $bn, bool $area = false, string $hint = ''): void
{
    $tag = $area ? 'textarea' : 'input';
    ?>
    <div class="field">
      <label><?= e($label) ?><?php if ($hint): ?> <em><?= e($hint) ?></em><?php endif; ?></label>
      <div class="pair">
        <div>
          <span class="tag">English</span>
          <?php if ($area): ?>
            <textarea name="<?= e($name) ?>_en" rows="3"><?= e($en) ?></textarea>
          <?php else: ?>
            <input type="text" name="<?= e($name) ?>_en" value="<?= e($en) ?>">
          <?php endif; ?>
        </div>
        <div>
          <span class="tag bn">বাংলা<?= trim((string)$bn) === '' ? ' · empty, will show English' : '' ?></span>
          <?php if ($area): ?>
            <textarea name="<?= e($name) ?>_bn" rows="3" lang="bn"><?= e($bn) ?></textarea>
          <?php else: ?>
            <input type="text" name="<?= e($name) ?>_bn" value="<?= e($bn) ?>" lang="bn">
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php
}
