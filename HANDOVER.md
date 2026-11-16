# Handover notes

For the developer taking this site live and looking after it. Read this once
and you should not need to ask anyone how it works.

## What you are receiving

| Item | Where | State |
|---|---|---|
| Public website, English and Bangla | `public_html/index.php`, `assets/` | Finished and tested |
| Admin (products, plants, health areas, page text, images, video) | `public_html/admin/` | Finished and tested |
| Database structure and starting content | `private_app/schema.sql`, `seed_1.sql`, `seed_2.sql`, `copy_defaults.php` | 71 products, 17 health areas, 8 plants, 3 figures, 71 pieces of page text |
| Starter pictures | `seed/product-images/` | 75 product photographs |
| Film | `seed/media/` | 12 clips and 12 stills (hero, why, leaf, close, 8 plants), 1 facility photograph. About 40 MB |
| One-time installer | `public_html/admin/install.php` | Finished. Must be deleted after use |
| Live server, domain, database | Hostinger account of the site owner | **Not set up by this repository.** See the checklist below |

There are no hidden dependencies. No Composer, no npm, no CDN, no API keys,
no analytics, no outside fonts. The fonts are in `assets/fonts`.

## Go-live checklist

1. **PHP 8.0 or newer** with the `pdo_mysql`, `gd` and `fileinfo` extensions. Hostinger has all three switched on by default.
2. **Create a MySQL database and user.** Note the name, user and password.
3. **Create `private_app/config.php`** from `config.sample.php`. Fill in the four database values and replace `session_secret` with a long random string.
4. **Upload three folders** so they sit side by side: `private_app/`, `public_html/` (its contents go into the server's web root) and `seed/`.
5. **Open `/admin/install.php`**, fill in a name, email and password. It reports what it did and checks its own row counts.
6. **Delete `public_html/admin/install.php`** from the server.
7. **Confirm `/private_app/config.php` returns 403 or 404** from a browser. If the host forces `private_app` inside the web root, the `.htaccess` in that folder (`Require all denied`) blocks it, but test it.
8. **Turn on HTTPS** and force it.
9. **Open the site** in English and with `?lang=bn`, on a computer and on a phone.

The click-by-click version of this, for a non-developer, is in
`docs/INSTALL-HOSTINGER.md`.

### Running it inside a folder (for example dm.squarepharma.com.bd/natural-medicine/)

This is the planned first home and has been tested.

1. Put the **contents** of `public_html/` into the folder that the address points at, for example `.../natural-medicine/`.
2. Put `private_app/` and `seed/` one level **above** that folder if the server allows it, so they sit beside it and not inside it. `index.php` and the admin look for `../private_app/`.
3. If they have to sit inside the web root, keep the `.htaccess` that ships in `private_app/` (Apache). On Nginx add a rule that denies `/private_app/` and `/seed/`. Then confirm in a browser that `/private_app/config.php` does not open.
4. In `private_app/config.php` set `'base_url' => '/natural-medicine'` (the folder name, with a leading slash and no trailing slash).
5. Open `/natural-medicine/admin/install.php`, then delete that file.

Moving later to its own subdomain only needs `base_url` set back to an empty string.

### Deploying from GitHub

The repository keeps `private_app` and `public_html` as sibling folders. A
deploy must preserve that, with the server's web root pointing at
`public_html`. Two ways that work:

- **Upload by hand or by SFTP.** Simplest, and fine for a site that changes rarely.
- **Automatic deploy.** Either Hostinger's Git feature (hPanel, Advanced, Git) if your plan lets the target folder sit above the web root, or a GitHub Action that copies the three folders over FTP or SSH. Check which your plan allows before choosing. Do not deploy the whole repository into the web root as it is, because the site would then be served from `/public_html/`.

Whichever you choose, a deploy must never overwrite `private_app/config.php`
or `public_html/uploads/`. Both are in `.gitignore` for that reason.

## Day-to-day operations

Everything below is done at `/admin/` and needs no code.

| Task | Screen |
|---|---|
| Change a price, pack, ingredient, description or photo | Products |
| Hide a product without deleting it | Products, untick Visible |
| Change a plant's words, photograph, clip, or which product it becomes | Plants |
| Rename or reorder health areas | Health areas |
| Change any headline, paragraph, button or menu label | Page text |
| Upload product photographs | Images |
| Change the forest scenes (hero, why, leaf, close) or the export picture | Film |

Things worth knowing:

- **Every text box is a pair,** English and Bangla. An empty Bangla box falls back to English, so nothing ever shows blank.
- **Products are stored one row per pack size.** The site groups rows that share a brand and a form into one card and lists the packs inside it. To add a new pack size, add a product row with the same brand and form.
- **The health area list hides empty areas** and counts product cards, not pack sizes.
- **Every film scene takes a still, a clip, or both.** The four forest scenes are on the Film screen and the eight plant scenes are on the Plants screen. A clip does not play by itself. It moves forwards and backwards as the visitor scrolls. A scene with neither falls back to painted leaves, so the page never breaks.
- **Making a replacement clip.** 16:9, 4 to 6 seconds, silent, one slow continuous camera move with no cuts, subject on the right and dark calm space on the left third. Then run it through ffmpeg once so that every frame is a keyframe, which is what lets it follow the scroll smoothly:
  `ffmpeg -i in.mp4 -an -vf "scale=1280:720,fps=24" -c:v libx264 -crf 27 -g 1 -pix_fmt yuv420p -movflags +faststart out.mp4`
  A clip that skips this step still works but moves in visible steps. Keep each file under the `max_upload_mb` value in `config.php` (12 by default).
- **Product photographs** are resized to 1200 px on upload. Clean shots on a white or transparent background look best, because they sit on a white card.

## Where to change things in code

| To change | Edit |
|---|---|
| A colour, font size, spacing or section gap | `public_html/assets/css/site.css`, section 2 (the `:root` block) |
| The order of sections, or the markup of one | `public_html/index.php`. Each section is labelled with a comment |
| The default wording, or add a new piece of text | `private_app/copy_defaults.php`. Add a row, call `copyText('section','key')` in `index.php`, then open Admin, Page text once and the new row appears |
| Film scenes, weighted scrolling, card tilt, filters, product window | `public_html/assets/js/site.js`. Nine numbered parts, listed at the top. Part 4 is the film engine and opens with an explanation of how a scene works |
| The painted leaves (shown only when a scene has no still or clip) | `public_html/assets/js/scene.js` |
| A plant line drawing (legacy, not shown while plants have photographs) | `public_html/assets/js/plants.js`. Paths on a 400 by 400 canvas. After adding one, add its key to `$plantKeys` at the top of `admin/ingredients.php` so it appears in the Drawing list |
| Database structure | `private_app/schema.sql`. There is no migration tool. For a live site, apply `ALTER TABLE` by hand in phpMyAdmin and update this file to match |
| Shared PHP helpers | `private_app/bootstrap.php` (`q`, `q1`, `run`, `pick`, `copyText`, `digits`, `money`, CSRF, login) |

Design rules are in `docs/DESIGN.md`. Read it before changing the look.

### How the pieces fit

- `index.php` runs five queries, groups products in PHP, and prints the whole page. Product details for the pop-up window are printed once as JSON in a `<script type="application/json">` tag. There are no AJAX calls.
- The page is complete without JavaScript. CSS only hides things for animation when `<html>` has the class `js`, which a one-line inline script adds.
- Motion is hand-written, with no library. One `requestAnimationFrame` loop in `site.js` runs the film scenes: for each scene near the screen it works out how far through it the visitor is, sets the clip to that moment with `video.currentTime`, and writes the numbers `--p`, `--q`, `--o` and `--fade` onto the scene. The stylesheet does the rest from those numbers.
- Scenes are tall blocks whose stage is `position: sticky`. Each one overlaps the one before by 160vh and fades in over it. The overlap is set in two places that must agree: `OVERLAP` and `FADE` in `site.js`, and the `-160vh` margin in `site.css`.
- Clips load one at a time, shortly before they are needed, never all at once. Clips are not loaded at all for visitors who ask for reduced motion, who then get a normal still page.
- The server must answer byte-range requests for MP4 files, or clips cannot seek. Apache and Nginx do this by default. PHP's built-in development server does not.
- Bangla mode switches digits and currency (`৳`) in PHP (`digits()`, `money()`) and in `site.js` (`digits()`).

## Data and privacy

- The public site collects nothing. No forms, no analytics, no tracking. It sets one cookie, `sqnm_lang`, to remember the language.
- The admin stores a name, email and password hash per user (`password_hash`), and a plain activity log of who changed what.
- All admin forms carry a CSRF token. Uploads are checked by content type, images are re-encoded through GD, and PHP execution is switched off inside `uploads/`.
- There is one admin role. Every user can change everything.

## Known limits and suggested next steps

Content that still needs a person:

0. **The plant pictures are AI-generated.** All 12 stills and clips were made with image and video models from botanical descriptions. They look right to a careful non-specialist, but someone on the herbal team must confirm each of the eight plants is the correct species and part before launch, or replace it with real photography from the Plants screen.
1. **Bangla product indications.** The 71 "used for" descriptions exist in English only and currently fall back to English. They need a qualified medical translator, not machine translation.
2. **Regulatory review.** Public indications and prices, and the three figures in "Why natural medicine" (especially the 2 million adverse drug reactions figure), should be cleared by regulatory affairs before launch. All are taken from the division's booklet.
3. **Two products have no photograph:** D-Sitol and Probio R. They show a leaf placeholder.
4. **Five products on the SQUARE website are not in the booklet** and so are not in the starting data: Carazol, Caroba, Laxisoft, Manli, Q-late. Their photographs are in `seed/product-images` if they are added.
5. **Photography.** Several pack shots have a grey studio background that shows as a rectangle on the white card. Reshooting or cutting them out would lift the product list noticeably. The facility photograph is a low-resolution crop from the booklet and should be replaced with an original.
7. **Official logo.** The top bar uses a text wordmark and a leaf mark. Replace with the official SQUARE Herbal & Ayurvedic logo when the file is available.
8. **Contact details.** The booklet has none, so the footer has none. Add them in Admin, Page text, Footer, Contact line.

Technical:

0. **Test on real phones before launch.** The film was tested in desktop Chromium at computer, tablet and phone sizes. It has not been run on a physical iPhone or Android phone. Scroll-driven video is the part most likely to behave differently there. If it stutters on a device, the simplest fix is to skip the clip on small screens and show the still (one condition in `loadClip` in `site.js`).
0. **Upgrading an older install.** The `ingredients` table gained a `video` column and the `videos` table has new slots. For a database made before this version, run `ALTER TABLE ingredients ADD video VARCHAR(255) NULL AFTER image;` and re-create the rows of `videos` from `seed_2.sql`. A fresh install needs nothing.
1. Add a second admin role (editor without user management) if more people will log in.
2. Add rate limiting to `admin/login.php`.
3. Serve product photographs as WebP with a `srcset`.
4. Add a privacy page if analytics or a contact form is ever added.
5. Individual product pages with their own addresses would help search engines. Today all products live on the one page.
