# Natural Medicine

The public website for the Herbal & Ayurvedic Division of SQUARE Pharmaceuticals PLC.
One scrolling page in English and Bangla, plus an admin where every word, product,
price and picture can be changed without touching code.

- **Stack:** PHP 8, MySQL or MariaDB, plain CSS and JavaScript. No framework, no build step, no outside services.
- **Hosting:** any shared PHP host. Written and tested for Hostinger.
- **Handing this to a developer?** Start with [HANDOVER.md](HANDOVER.md).
- **Installing it yourself?** Follow [docs/INSTALL-HOSTINGER.md](docs/INSTALL-HOSTINGER.md). Every click is spelled out.

## What the visitor sees

The plant leads and the product follows. The first half of the page is a
film: scrolling moves a camera through a forest and around each plant.

| # | Section | What happens |
|---|---|---|
| 1 | Hero | The camera pushes into a misty forest behind the headline as you scroll |
| 2 | Why natural medicine | Three figures from the booklet count up under the forest canopy |
| 3 | The plants | Eight full-screen scenes. The camera circles each plant, its story arrives line by line, then the product it becomes rises into view |
| 4 | Leaf to bottle | A close view of one leaf, then the pack turns into frame |
| 5 | Health areas | A large index. Hover shows a pack photo, a click filters the product list |
| 6 | Products | Every product, grouped by brand, with filter, search and sort. Cards lean towards the pointer. A click opens pack sizes and prices |
| 7 | Export | The facility photo inside the booklet's curve, and what the division offers partners |
| 8 | Close and footer | The view over the forest, closing line, wordmark, health notice |

Each picture dissolves into the next. On a computer the mouse wheel glides
and the plants turn slightly with the pointer. Visitors who ask their device
for less motion get the same content as a still page.

## What is in this repository

| Path | What it is |
|---|---|
| `.gitignore` | Keeps the database password and uploaded files out of Git |
| `HANDOVER.md` | Everything a developer needs to take this live and look after it |
| `README.md` | This file |
| `docs/DESIGN.md` | Colours, type, spacing and motion rules, so later changes stay consistent |
| `docs/INSTALL-HOSTINGER.md` | Click-by-click install guide for a non-developer |
| `private_app/` | Settings, database structure, starting content and shared PHP. Never reachable from the internet |
| `public_html/` | The web root: the page, the admin, styles, scripts and fonts |
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
