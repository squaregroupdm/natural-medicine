<?php
/* =====================================================================
   install.php

   Run this once, in a browser, after uploading the files. It creates
   the tables, loads the starting content, and makes your first login.

   DELETE THIS FILE once it says it is finished.
   ===================================================================== */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/../../private_app/sqlsplit.php';

$TABLES = ['activity','media','videos','figures','copy','products','ingredients','areas','users'];

/* Has a real install already happened? */
$alreadyDone = false;
try {
    $row = q1('SELECT COUNT(*) c FROM users');
    $alreadyDone = $row && (int)$row['c'] > 0;
} catch (Throwable $e) {
    // the users table does not exist yet, which is the normal first run
}

if ($alreadyDone) {
    exit('<p style="font:16px/1.6 system-ui;padding:40px;max-width:40em">'
       . 'Setup has already been completed. Delete this file '
       . '(<code>admin/install.php</code>) and go to '
       . '<a href="login.php">the sign in page</a>.</p>');
}

/* What is in the database right now? */
$existing = [];
try {
    foreach (q('SHOW TABLES') as $r) $existing[] = array_values($r)[0];
} catch (Throwable $e) {}

$ran    = [];
$errors = [];
$ready  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 10) {
        $errors[] = 'Fill in all three boxes. The password needs at least 10 characters.';
    } else {
        try {
            // Clear out anything left behind by an earlier attempt, so this
            // always starts from a clean slate.
            if ($existing) {
                db()->exec('SET FOREIGN_KEY_CHECKS = 0');
                foreach ($TABLES as $t) {
                    if (in_array($t, $existing, true)) db()->exec("DROP TABLE IF EXISTS `$t`");
                }
                db()->exec('SET FOREIGN_KEY_CHECKS = 1');
                $ran[] = 'cleared ' . count($existing) . ' leftover table(s)';
            }

            foreach (['schema.sql', 'seed_1.sql', 'seed_2.sql'] as $file) {
                $path = APP_ROOT . '/' . $file;
                if (!is_file($path)) throw new RuntimeException("Missing private_app/$file");

                $statements = sql_statements(file_get_contents($path));
                if (!$statements) throw new RuntimeException("private_app/$file looks empty");

                $ok = 0;
                foreach ($statements as $stmt) {
                    db()->exec($stmt);
                    $ok++;
                }
                $ran[] = "$file - $ok of " . count($statements) . ' statements';
            }

            run('INSERT INTO users (email, name, password_hash) VALUES (?, ?, ?)',
                [$email, $name, password_hash($pass, PASSWORD_DEFAULT)]);

            $ran[] = sync_copy() . ' pieces of page text';

            // Copy the starter pictures into the uploads folder, then
            // register every product image found there.
            global $CONFIG;
            $up   = WEB_ROOT . '/' . $CONFIG['upload_dir'];
            $seed = is_dir(dirname(APP_ROOT) . '/seed') ? dirname(APP_ROOT) . '/seed' : APP_ROOT . '/seed';
            $copied = 0;
            foreach (['product-images' => 'products', 'media' => 'video'] as $from => $to) {
                if (!is_dir("$up/$to")) @mkdir("$up/$to", 0755, true);
                foreach (glob("$seed/$from/*.{jpg,jpeg,png,webp,mp4}", GLOB_BRACE) ?: [] as $src) {
                    $dest = "$up/$to/" . basename($src);
                    if (!is_file($dest) && @copy($src, $dest)) $copied++;
                }
            }
            $dir = $up . '/products';
            $found = 0;
            foreach (glob($dir . '/*.{jpg,jpeg,png,webp,JPG,PNG}', GLOB_BRACE) ?: [] as $img) {
                $size = @getimagesize($img);
                run('INSERT INTO media (filename, original, kind, width, height, bytes)
                     VALUES (?, ?, "image", ?, ?, ?)',
                    [basename($img), basename($img), $size[0] ?? null, $size[1] ?? null, filesize($img)]);
                $found++;
            }
            $ran[] = "your login, $copied starter picture(s) copied, $found product image(s) registered";
            if ($found === 0) $errors[] = 'Note: no product images were found. Upload the seed/product-images folder into public_html/uploads/products, then use Admin > Images > Scan the uploads folder.';

            // Prove it worked rather than assuming it did.
            $check = [
                'products'    => 71,
                'areas'       => 17,
                'ingredients' => 8,
            ];
            foreach ($check as $table => $expect) {
                $n = (int)q1("SELECT COUNT(*) c FROM `$table`")['c'];
                if ($n === 0) throw new RuntimeException("The $table table came out empty.");
                if ($n !== $expect) $errors[] = "Note: $table has $n rows, expected $expect.";
            }

            $ready = true;

        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Set up | Natural Medicine</title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">

<?php if ($ready): ?>
  <div class="login" style="width:min(520px,94vw)">
    <h1>All set</h1>
    <p class="sub">The website is built and ready.</p>
    <ul style="font-size:13px;line-height:1.9;color:#6B7A70;padding-left:18px;margin:0 0 18px">
      <?php foreach ($ran as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
    </ul>
    <?php if ($errors): ?>
      <div class="error"><?php foreach ($errors as $x) echo e($x) . '<br>'; ?></div>
    <?php endif; ?>
    <div class="error" style="background:#7a2222;border-color:#7a2222;color:#fff">
      <b>One more step.</b> Delete the file <b>admin/install.php</b> from the
      server now. Leaving it there is a security hole.
    </div>
    <p style="margin-top:18px"><a href="login.php">Go to the sign in page</a></p>
  </div>

<?php else: ?>
  <form class="login" method="post">
    <h1>Set up <span>Natural Medicine</span></h1>
    <p class="sub">This builds the database and creates your login. It only needs doing once.</p>

    <?php if ($errors): ?>
      <div class="error">
        <b>That did not work.</b><br>
        <?php foreach ($errors as $x) echo e($x) . '<br>'; ?>
      </div>
    <?php endif; ?>

    <?php if ($existing): ?>
      <div class="error" style="background:#FFF6E5;border-color:#E8D3A8;color:#6B4E1A">
        Found <?= count($existing) ?> table(s) from an earlier attempt.
        They will be removed and rebuilt. No content is lost, because
        nothing has been added yet.
      </div>
    <?php endif; ?>

    <label>Your name
      <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
    </label>
    <label>Your email
      <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
    </label>
    <label>Choose a password <span class="muted">at least 10 characters</span>
      <input type="password" name="password" required minlength="10">
    </label>
    <button type="submit">Build the site</button>
  </form>
<?php endif; ?>

</body>
</html>
