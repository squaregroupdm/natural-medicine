# Square Naturals

The public website for Square Naturals, the natural wellness range of SQUARE
Pharmaceuticals PLC (Herbal & Ayurvedic Division). Three pages in English and
Bangla, plus an admin where every word, product, price, picture, clip and video
can be changed without touching code.

- **Stack:** PHP 8, MySQL or MariaDB, plain CSS and JavaScript. No framework, no build step, no outside libraries.
- **Hosting:** any PHP host. Tested at the top of a domain and inside a folder.
- **Handing this to a developer?** Start with [HANDOVER.md](HANDOVER.md).
- **Installing it yourself?** Follow [docs/INSTALL-HOSTINGER.md](docs/INSTALL-HOSTINGER.md). Every click is spelled out.

## The story the site tells

The theme line is the map: **প্রকৃতির শিকড় থেকে প্রতিদিনের সুস্থতায়**, from nature's
roots to everyday wellness. It follows the launch plan: establish the umbrella
brand first, then bring in products through people's everyday needs.

### Home page

| # | Section | What happens |
|---|---|---|
| 0 | Opening | On a first visit, three plant outlines draw themselves, then lift to reveal the film |
| 1 | Hero | Brand, tagline and theme line. The camera pushes into a misty forest as you scroll |
| 2 | Why natural | Three plain answers, with two supporting figures |
| 3 | From the root | One pinned scene. Eight plants take turns: the camera circles each one, its story arrives, then the product it becomes rises into view |
| 4 | Wellness Finder | "What would you like to support?" Five needs. Pick one to see the products that fit and who each is for |
| 5 | Trusted by Square | Three trust points, then doctors' videos |
| 6 | Real routines | Creators' videos |
| 7 | Where to start | The focus brands, then common questions |
| 8 | Close and footer | Closing line over the forest, footer |

Video sections only appear once videos have been added.

### Other pages

| Page | What it is |
|---|---|
| `products.php` | Every product, focus brands first, with filter, search and sort. A click opens pack sizes, prices and any related videos |
| `videos.php` | Three shelves: Real routines, Doctors explain, Why natural. Videos play on the page |

## What is in this repository

| Path | What it is |
|---|---|
| `.gitignore` | Keeps the database password and uploaded files out of Git |
| `HANDOVER.md` | Everything a developer needs to take this live and look after it |
| `README.md` | This file |
| `docs/DESIGN.md` | Colours, type, spacing and motion rules, so later changes stay consistent |
| `docs/INSTALL-HOSTINGER.md` | Click-by-click install guide for a non-developer |
| `private_app/` | Settings, database structure, starting content and shared PHP (including `site_parts.php`, the pieces all three pages share). Never reachable from the internet |
| `public_html/` | The web root: the three pages, the admin, styles, scripts and fonts |
| `seed/media/` | The film: 12 short clips and their stills (hero, why, leaf, close, eight plants), plus the facility photograph |
| `seed/product-images/` | 75 starter product photographs. The installer copies them into `uploads` |

## Run it on your own computer

You need PHP 8 and MySQL or MariaDB.

```bash
# 1. make an empty database, then copy the settings file and fill in the four database values
cp private_app/config.sample.php private_app/config.php

# 2. start PHP's built-in server
cd public_html && php -S 127.0.0.1:8080
# note: PHP's built-in server cannot seek inside video files, so the clips
# will not follow the scroll locally. Apache, Nginx and Hostinger all can.

# 3. open http://127.0.0.1:8080/admin/install.php and fill in the form
```

The installer builds the tables, loads 71 products, 8 plants and all page text,
copies the starter pictures and film, and creates your admin login. Delete
`public_html/admin/install.php` once it says it is finished.

## Two rules that keep the site safe

1. `private_app/config.php` holds the database password. It is never committed and never uploaded anywhere public.
2. `public_html/admin/install.php` is deleted from the server straight after it runs.
