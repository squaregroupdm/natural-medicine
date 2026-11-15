<?php
/* Images: upload, view and delete. Uploads are resized and cleaned up. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

global $CONFIG;
$dir = WEB_ROOT . '/' . $CONFIG['upload_dir'] . '/products';
if (!is_dir($dir)) @mkdir($dir, 0755, true);

$ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$MAXW    = 1200;   // wider than this gets scaled down
$notes   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int)$_POST['id'];
        $m  = q1('SELECT * FROM media WHERE id = ?', [$id]);
        if ($m) {
            $inUse = q1('SELECT id FROM products WHERE image = ?', [$m['filename']]);
            if ($inUse) {
                flash('That image is still used by a product. Change the product first.');
            } else {
                @unlink($dir . '/' . $m['filename']);
                run('DELETE FROM media WHERE id = ?', [$id]);
                log_activity('Deleted image: ' . $m['filename']);
                flash('Deleted.');
            }
        }
        redirect('media.php');
    }

    if (($_POST['action'] ?? '') === 'scan') {
        // Register images that were put into the uploads folder directly,
        // for example copied up over FTP, so they appear in the library.
        $found = 0;
        foreach (glob($dir . '/*.{jpg,jpeg,png,webp,JPG,PNG}', GLOB_BRACE) ?: [] as $path) {
            $file = basename($path);
            if (q1('SELECT id FROM media WHERE filename = ?', [$file])) continue;
            $size = @getimagesize($path);
            run('INSERT INTO media (filename, original, kind, width, height, bytes, uploaded_by)
                 VALUES (?, ?, "image", ?, ?, ?, ?)',
                [$file, $file, $size[0] ?? null, $size[1] ?? null, filesize($path), current_user()['id']]);
            $found++;
        }
        log_activity("Scanned the uploads folder, added $found image(s)");
        flash($found ? "Added $found image(s) that were already on the server." : 'Nothing new found.');
        redirect('media.php');
    }

    /* ---- uploading ---- */
    $files = $_FILES['files'] ?? null;
    if ($files && is_array($files['name'])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            $name = $files['name'][$i];
            if ($files['error'][$i] !== UPLOAD_ERR_OK) { $notes[] = "$name could not be uploaded."; continue; }

            $tmp  = $files['tmp_name'][$i];
            $mime = $finfo->file($tmp);
            if (!isset($ALLOWED[$mime])) { $notes[] = "$name is not a JPG, PNG or WebP."; continue; }
            if ($files['size'][$i] > $CONFIG['max_upload_mb'] * 1024 * 1024) {
                $notes[] = "$name is larger than {$CONFIG['max_upload_mb']} MB."; continue;
            }

            $ext  = $ALLOWED[$mime];
            $stem = slugify(pathinfo($name, PATHINFO_FILENAME));
            $file = $stem . '.' . $ext;
            $n = 2;
            while (is_file($dir . '/' . $file)) { $file = $stem . '-' . $n++ . '.' . $ext; }
            $dest = $dir . '/' . $file;

            // Re-encode through GD. This also strips anything hidden
            // inside the file, so only a real picture is ever saved.
            $img = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($tmp),
                'image/png'  => @imagecreatefrompng($tmp),
                'image/webp' => @imagecreatefromwebp($tmp),
            };
            if (!$img) { $notes[] = "$name is not a readable image."; continue; }

            $w = imagesx($img); $h = imagesy($img);
            if ($w > $MAXW) {
                $nh  = (int)round($h * ($MAXW / $w));
                $dst = imagecreatetruecolor($MAXW, $nh);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $img, 0, 0, 0, 0, $MAXW, $nh, $w, $h);
                imagedestroy($img);
                $img = $dst; $w = $MAXW; $h = $nh;
            }

            $ok = match ($ext) {
                'jpg'  => imagejpeg($img, $dest, 86),
                'png'  => imagepng($img, $dest, 7),
                'webp' => imagewebp($img, $dest, 86),
            };
            imagedestroy($img);

            if (!$ok) { $notes[] = "$name could not be saved."; continue; }

            run('INSERT INTO media (filename, original, kind, width, height, bytes, uploaded_by)
                 VALUES (?, ?, "image", ?, ?, ?, ?)',
                [$file, $name, $w, $h, filesize($dest), current_user()['id']]);
            $notes[] = "$name uploaded.";
        }
        log_activity('Uploaded ' . $count . ' image(s)');
    }
}

$rows = q('SELECT m.*, (SELECT COUNT(*) FROM products p WHERE p.image = m.filename) AS used
           FROM media m WHERE m.kind = "image" ORDER BY m.id DESC');

admin_head('Images');
?>
<h1>Images</h1>

<form method="post" enctype="multipart/form-data" class="upload">
  <?= csrf_field() ?>
  <label class="drop">
    <input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp">
    <span>Choose images to upload</span>
  </label>
  <button type="submit" class="primary">Upload</button>
  <p class="muted">JPG, PNG or WebP, up to <?= (int)$CONFIG['max_upload_mb'] ?> MB each.
     Anything wider than <?= $MAXW ?> pixels is scaled down automatically.</p>
</form>

<form method="post" class="upload">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="scan">
  <b>Copied images up by FTP?</b>
  <p class="muted">If you put files straight into uploads/products without using the box above,
     press this and they will be added to the library.</p>
  <button type="submit" class="ghost">Scan the uploads folder</button>
</form>

<?php if ($notes): ?>
  <div class="notes"><?php foreach ($notes as $n) echo '<div>' . e($n) . '</div>'; ?></div>
<?php endif; ?>

<div class="grid">
  <?php foreach ($rows as $m): ?>
    <figure class="tile">
      <img src="../uploads/products/<?= e(rawurlencode($m['filename'])) ?>" alt="" loading="lazy">
      <figcaption>
        <b><?= e($m['filename']) ?></b>
        <span class="muted"><?= (int)$m['width'] ?> &times; <?= (int)$m['height'] ?> ·
          <?= number_format(((int)$m['bytes']) / 1024) ?> KB</span>
        <span class="<?= $m['used'] ? 'ok' : 'muted' ?>">
          <?= $m['used'] ? 'used by ' . (int)$m['used'] . ' product(s)' : 'not used' ?>
        </span>
        <form method="post" data-confirm="Delete this image?">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
          <button type="submit" class="linky">Delete</button>
        </form>
      </figcaption>
    </figure>
  <?php endforeach; ?>
</div>
<?php admin_foot();
