<?php
/* Videos: YouTube links shown on the Videos page and around the site. */

require __DIR__ . '/../../private_app/bootstrap.php';
require __DIR__ . '/_layout.php';
require_login();

$kinds = [
    'creator' => 'Real routines (people using the products)',
    'doctor'  => 'Doctors explain (podcasts, interviews)',
    'why'     => 'Why natural (short explainers)',
];

/** Pull the video id out of any normal YouTube address. Returns [id, is it a Short]. */
function youtube_id(string $url): array
{
    $url = trim($url);
    if (preg_match('~^[\w-]{11}$~', $url)) return [$url, false];
    if (preg_match('~youtube\.com/shorts/([\w-]{6,20})~i', $url, $m)) return [$m[1], true];
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|live/|v/))([\w-]{6,20})~i', $url, $m)) return [$m[1], false];
    return ['', false];
}

/** Ask YouTube for the video's title, so it does not have to be typed. Quietly gives up if the server cannot reach it. */
function youtube_title(string $id): string
{
    $ctx  = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true]]);
    $json = @file_get_contents('https://www.youtube.com/oembed?format=json&url=' . rawurlencode('https://www.youtube.com/watch?v=' . $id), false, $ctx);
    $data = $json ? json_decode($json, true) : null;
    return is_array($data) && !empty($data['title']) ? mb_substr((string)$data['title'], 0, 190) : '';
}

$needs    = q('SELECT id, name_en FROM needs ORDER BY sort_order, id');
$products = q('SELECT MIN(id) AS id, brand, form_en FROM products GROUP BY brand, form_en ORDER BY brand, form_en');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete_samples') {
        run("DELETE FROM talks WHERE youtube_id = ''");
        log_activity('Deleted the sample videos');
        flash('The sample videos are gone.');
        redirect('talks.php');
    }

    if ($action === 'delete') {
        run('DELETE FROM talks WHERE id = ?', [$id]);
        log_activity('Deleted a video');
        flash('Deleted.');
        redirect('talks.php');
    }

    $url = trim((string)($_POST['url'] ?? ''));
    [$yt, $short] = youtube_id($url);
    if ($url !== '' && $yt === '') {
        flash('That does not look like a YouTube link. Copy the address from the browser or from the Share button.');
        redirect('talks.php' . ($id ? '?id=' . $id : ''));
    }
    $title = trim((string)($_POST['title_en'] ?? ''));
    if ($title === '' && $yt !== '') $title = youtube_title($yt);
    if ($title === '') $title = $yt === '' ? 'Placeholder' : 'Untitled video';

    $f = [
        'kind'       => isset($kinds[$_POST['kind'] ?? '']) ? $_POST['kind'] : 'creator',
        'youtube_id' => $yt,
        'is_short'   => ($short || isset($_POST['is_short'])) ? 1 : 0,
        'title_en'   => $title,
        'title_bn'   => trim((string)($_POST['title_bn'] ?? '')),
        'speaker_en' => trim((string)($_POST['speaker_en'] ?? '')),
        'speaker_bn' => trim((string)($_POST['speaker_bn'] ?? '')),
        'need_id'    => ((int)($_POST['need_id'] ?? 0)) ?: null,
        'product_id' => ((int)($_POST['product_id'] ?? 0)) ?: null,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
    ];
    // an optional picture of our own, used instead of the one from YouTube
    $notes = [];
    if ($new = save_film_upload('thumb', 'image', 'video-' . ($yt ?: 'card'), $notes)) $f['thumb'] = $new;
    elseif (isset($_POST['remove_thumb'])) $f['thumb'] = null;

    if ($id) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($f)));
        run("UPDATE talks SET $set WHERE id = ?", [...array_values($f), $id]);
    } else {
        $cols = implode(', ', array_keys($f));
        run("INSERT INTO talks ($cols) VALUES (" . rtrim(str_repeat('?,', count($f)), ',') . ')', array_values($f));
    }
    log_activity(($id ? 'Edited' : 'Added') . ' video: ' . $title);
    flash($notes ? 'Saved, but: ' . implode(' ', $notes) : 'Saved.');
    redirect('talks.php');
}

$edit = isset($_GET['id']) ? q1('SELECT * FROM talks WHERE id = ?', [(int)$_GET['id']]) : null;
$t    = $edit ?: ['id'=>0,'kind'=>$_GET['kind'] ?? 'creator','youtube_id'=>'','thumb'=>null,'is_short'=>0,'title_en'=>'','title_bn'=>'',
                  'speaker_en'=>'','speaker_bn'=>'','need_id'=>null,'product_id'=>null,'sort_order'=>0,'is_visible'=>1];
$list = q('SELECT * FROM talks ORDER BY kind, sort_order, id DESC');

admin_head('Videos');
?>
<h1>Videos</h1>
<p class="muted">Paste a YouTube link and the video appears on the site with its picture. Visitors play it without leaving the page.
   The three groups are separate shelves on the Videos page. A group with no videos is simply not shown.</p>

<?php $samples = count(array_filter($list, fn($r) => $r['youtube_id'] === '')); if ($samples): ?>
<form method="post" class="danger" style="margin:18px 0">
  <?= csrf_field() ?>
  <p><strong><?= $samples ?> sample video<?= $samples > 1 ? 's are' : ' is' ?> showing on the site.</strong>
     They are placeholders with no real video, there so the sections can be seen. Replace them with real links or remove them before launch.</p>
  <button type="submit" name="action" value="delete_samples" onclick="return confirm('Delete every sample video?')">Delete all samples</button>
</form>
<?php endif; ?>

<section class="block">
  <h2><?= $t['id'] ? 'Edit video' : 'Add a video' ?></h2>
  <form method="post" class="form" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">

    <div class="row">
      <div class="field">
        <label>YouTube link <em>a normal video, a Short, or a youtu.be link. Leave empty for a placeholder card</em></label>
        <input type="text" name="url" placeholder="https://www.youtube.com/watch?v=..." value="<?= $t['youtube_id'] !== '' ? e('https://www.youtube.com/' . ($t['is_short'] ? 'shorts/' : 'watch?v=') . $t['youtube_id']) : '' ?>">
      </div>
      <div class="field">
        <label>Group</label>
        <select name="kind">
          <?php foreach ($kinds as $k => $label): ?><option value="<?= e($k) ?>"<?= $t['kind'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>

    <?php lang_pair('Title', 'title', $t['title_en'], $t['title_bn'], false, 'leave English empty to use the title from YouTube') ?>
    <?php lang_pair('Who is speaking', 'speaker', $t['speaker_en'], $t['speaker_bn'], false, 'optional, for example a name and what they do') ?>

    <div class="row">
      <div class="field">
        <label>About which need <em>optional. The video then also shows in the Wellness Finder</em></label>
        <select name="need_id"><option value="0">none</option>
          <?php foreach ($needs as $n): ?><option value="<?= (int)$n['id'] ?>"<?= (int)$t['need_id'] === (int)$n['id'] ? ' selected' : '' ?>><?= e($n['name_en']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>About which product <em>optional. The video then also shows in that product's window</em></label>
        <select name="product_id"><option value="0">none</option>
          <?php foreach ($products as $p): ?><option value="<?= (int)$p['id'] ?>"<?= (int)$t['product_id'] === (int)$p['id'] ? ' selected' : '' ?>><?= e($p['brand'] . ' ' . $p['form_en']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="field">
      <label>Our own picture <em>optional. JPG or WebP, wide (16:9), or upright for a Short. Without one, the picture from YouTube is used</em></label>
      <?php if (!empty($t['thumb'])): ?>
        <img class="vprev" src="../uploads/video/<?= e(rawurlencode($t['thumb'])) ?>" alt="">
        <label class="check"><input type="checkbox" name="remove_thumb" value="1"> Remove this picture</label>
      <?php endif; ?>
      <input type="file" name="thumb" accept="image/jpeg,image/webp">
    </div>

    <div class="row">
      <div class="field narrow"><label>Order <em>lower shows first</em></label><input type="number" name="sort_order" value="<?= (int)$t['sort_order'] ?>"></div>
      <div class="field narrow"><label>Shape</label><label class="check"><input type="checkbox" name="is_short" value="1"<?= $t['is_short'] ? ' checked' : '' ?>> Upright (a Short)</label></div>
      <div class="field narrow"><label>Show on the site</label><label class="check"><input type="checkbox" name="is_visible" value="1"<?= $t['is_visible'] ? ' checked' : '' ?>> Visible</label></div>
    </div>

    <div class="actions">
      <button type="submit" class="primary" name="action" value="save"><?= $t['id'] ? 'Save' : 'Add video' ?></button>
      <?php if ($t['id']): ?>
        <a href="talks.php">Cancel</a>
        <button type="submit" class="linky" name="action" value="delete" onclick="return confirm('Delete this video from the site?')">Delete</button>
      <?php endif; ?>
    </div>
  </form>
</section>

<?php foreach ($kinds as $k => $label): $rows = array_filter($list, fn($r) => $r['kind'] === $k); ?>
  <h2><?= e($label) ?> <span class="muted">(<?= count($rows) ?>)</span></h2>
  <?php if (!$rows): ?><p class="muted">None yet.</p><?php else: ?>
  <table class="list">
    <thead><tr><th></th><th>Title</th><th>Speaker</th><th>Order</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr<?= $r['is_visible'] ? '' : ' class="hidden-row"' ?>>
        <td style="width:110px"><img src="<?= $r['thumb'] ? '../uploads/video/' . e(rawurlencode($r['thumb'])) : 'https://i.ytimg.com/vi/' . e($r['youtube_id']) . '/mqdefault.jpg' ?>" alt="" style="width:96px;aspect-ratio:16/9;object-fit:cover;border-radius:6px;display:block"></td>
        <td><a href="talks.php?id=<?= (int)$r['id'] ?>"><strong><?= e($r['title_en']) ?></strong></a><?= $r['is_short'] ? ' <span class="muted">Short</span>' : '' ?><?= $r['youtube_id'] === '' ? ' <span class="muted">(sample, no video)</span>' : '' ?></td>
        <td class="muted"><?= e((string)$r['speaker_en']) ?></td>
        <td class="muted"><?= (int)$r['sort_order'] ?></td>
        <td><a href="talks.php?id=<?= (int)$r['id'] ?>">Edit</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
<?php endforeach; ?>
<?php admin_foot();
