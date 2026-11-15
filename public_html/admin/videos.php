<?php
/* Film: the full-screen clips and stills behind the story sections. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

global $CONFIG;
$dir = WEB_ROOT . '/' . $CONFIG['upload_dir'] . '/video';
if (!is_dir($dir)) @mkdir($dir, 0755, true);
$notes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $slot = (string)($_POST['slot'] ?? '');
    $row  = q1('SELECT * FROM videos WHERE slot = ?', [$slot]);
    if (!$row) redirect('videos.php');

    $file   = $row['file_mp4'];
    $poster = $row['poster'];

    // the video itself
    if (!empty($_FILES['mp4']['name']) && $_FILES['mp4']['error'] === UPLOAD_ERR_OK) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($_FILES['mp4']['tmp_name']);
        if ($mime !== 'video/mp4') {
            $notes[] = 'The video must be an MP4 file.';
        } elseif ($_FILES['mp4']['size'] > (int)($CONFIG['max_upload_mb'] ?? 8) * 1024 * 1024) {
            $notes[] = 'That video is over ' . (int)($CONFIG['max_upload_mb'] ?? 8) . ' MB. Please compress it first.';
        } else {
            $name = $slot . '-' . time() . '.mp4';
            if (move_uploaded_file($_FILES['mp4']['tmp_name'], $dir . '/' . $name)) {
                if ($file) @unlink($dir . '/' . $file);
                $file = $name;
                $notes[] = 'Video uploaded.';
            }
        }
    }

    // the still image shown on phones
    if (!empty($_FILES['poster']['name']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($_FILES['poster']['tmp_name']);
        $ok    = ['image/jpeg' => 'jpg', 'image/webp' => 'webp'];
        if (!isset($ok[$mime])) {
            $notes[] = 'The still image must be a JPG or WebP.';
        } else {
            $name = $slot . '-poster-' . time() . '.' . $ok[$mime];
            if (move_uploaded_file($_FILES['poster']['tmp_name'], $dir . '/' . $name)) {
                if ($poster) @unlink($dir . '/' . $poster);
                $poster = $name;
                $notes[] = 'Still image uploaded.';
            }
        }
    }

    run('UPDATE videos SET file_mp4 = ?, poster = ?, is_enabled = ? WHERE slot = ?',
        [$file, $poster, isset($_POST['is_enabled']) ? 1 : 0, $slot]);
    log_activity('Updated video slot: ' . $slot);
    $_SESSION['vnotes'] = $notes;
    flash('Saved.');
    redirect('videos.php');
}

$notes = $_SESSION['vnotes'] ?? [];
unset($_SESSION['vnotes']);
$rows = q('SELECT * FROM videos ORDER BY id');

admin_head('Video');
?>
<h1>Film</h1>
<p class="muted">These are the full-screen scenes behind the story. Each one takes a still picture, a short clip, or both.
   The clip does not play by itself: it moves forwards and backwards as the visitor scrolls.
   The still is shown while the clip loads, and on its own for visitors who ask for less motion.
   Switch a scene off and that section falls back to painted leaves.
   The clips for the eight plants are on the <a href="ingredients.php">Plants</a> screen.</p>
<p class="muted"><strong>Best results:</strong> MP4, 16:9, 4 to 6 seconds, silent, one slow continuous camera move with no cuts,
   under <?= (int)($CONFIG['max_upload_mb'] ?? 8) ?> MB. Keep the left third of the picture dark and calm, because the words sit there.</p>

<?php if ($notes): ?>
  <div class="notes"><?php foreach ($notes as $n) echo '<div>' . e($n) . '</div>'; ?></div>
<?php endif; ?>

<?php foreach ($rows as $v): ?>
  <section class="block">
    <h2><?= e(ucfirst($v['slot'])) ?></h2>
    <p class="muted"><?= e($v['label']) ?></p>
    <form method="post" enctype="multipart/form-data" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="slot" value="<?= e($v['slot']) ?>">
      <div class="row">
        <div class="field">
          <label>Video file <em>MP4, 16:9, under 8 MB, no sound</em></label>
          <input type="file" name="mp4" accept="video/mp4">
          <?php if ($v['file_mp4']): ?>
            <p class="muted">Now using <?= e($v['file_mp4']) ?></p>
          <?php endif; ?>
        </div>
        <div class="field">
          <label>Still image <em>JPG or WebP. Shown on its own, on phones, and while the video loads</em></label>
          <input type="file" name="poster" accept="image/jpeg,image/webp">
          <?php if ($v['poster']): ?>
            <p class="muted">Now using <?= e($v['poster']) ?></p>
          <?php endif; ?>
        </div>
        <div class="field narrow">
          <label>Use it</label>
          <label class="check"><input type="checkbox" name="is_enabled" value="1"<?= $v['is_enabled'] ? ' checked' : '' ?>> On</label>
        </div>
      </div>
      <div class="actions"><button type="submit" class="primary">Save this slot</button></div>
    </form>
    <?php if ($v['poster'] && !$v['file_mp4']): ?>
      <img class="vprev" src="../uploads/video/<?= e(rawurlencode($v['poster'])) ?>" alt="">
    <?php endif; ?>
    <?php if ($v['file_mp4']): ?>
      <video class="vprev" src="../uploads/video/<?= e(rawurlencode($v['file_mp4'])) ?>" muted loop autoplay playsinline></video>
    <?php endif; ?>
  </section>
<?php endforeach; ?>
<?php admin_foot();
