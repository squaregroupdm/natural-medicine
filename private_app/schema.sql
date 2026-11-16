-- =====================================================================
--  SQUARE Herbal & Ayurvedic: Natural Medicine
--  Database structure
--
--  Every piece of text on the website has two columns: one ending
--  _en for English and one ending _bn for Bangla. If the Bangla is
--  left empty the website quietly shows the English instead, so the
--  site never breaks while translation is still in progress.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+06:00';

-- ---------------------------------------------------------------
--  Who can log in to the admin
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email          VARCHAR(190) NOT NULL UNIQUE,
  name           VARCHAR(120) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  is_active      TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at  DATETIME NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  Therapeutic areas, for example "Respiratory Health"
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS areas (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(80)  NOT NULL UNIQUE,
  name_en     VARCHAR(120) NOT NULL,
  name_bn     VARCHAR(160) NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  is_visible  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  The plants. These lead the story on the home page.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ingredients (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug         VARCHAR(80)  NOT NULL UNIQUE,
  plant_key    VARCHAR(40)  NOT NULL,          -- which drawing to use
  name_en      VARCHAR(120) NOT NULL,          -- "Malabar Nut"
  name_bn      VARCHAR(160) NULL,              -- "বাসক"
  botanical    VARCHAR(160) NOT NULL,          -- "Adhatoda vasica"
  part_en      VARCHAR(80)  NULL,
  part_bn      VARCHAR(120) NULL,
  known_en     TEXT NULL,                      -- what people already know
  known_bn     TEXT NULL,
  does_en      TEXT NULL,                      -- what it does
  does_bn      TEXT NULL,
  product_id   INT UNSIGNED NULL,              -- the product it becomes
  image        VARCHAR(255) NULL,              -- plant photo (also the still for the clip)
  video        VARCHAR(255) NULL,              -- short clip that plays as the visitor scrolls
  sort_order   INT NOT NULL DEFAULT 0,
  is_visible   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  The products. One row per pack size, so Adovas 100 ml and
--  Adovas 200 ml are two rows, exactly as in the price list.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(120) NOT NULL UNIQUE,
  brand       VARCHAR(120) NOT NULL,           -- "Adovas"
  form_en     VARCHAR(80)  NULL,               -- "Syrup"
  form_bn     VARCHAR(120) NULL,
  source_en   VARCHAR(255) NOT NULL,           -- "Basak Extract"
  source_bn   VARCHAR(320) NULL,
  pack        VARCHAR(60)  NOT NULL,           -- "100 ml"
  mrp         DECIMAL(10,2) NOT NULL,
  area_id     INT UNSIGNED NULL,
  uses_en     TEXT NULL,                       -- what it is for
  uses_bn     TEXT NULL,
  image       VARCHAR(255) NULL,
  is_visible  TINYINT(1) NOT NULL DEFAULT 1,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,       -- a focus brand: shown first, and on the home page
  sort_order  INT NOT NULL DEFAULT 0,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_area (area_id),
  KEY idx_brand (brand),
  CONSTRAINT fk_products_area FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE ingredients
  ADD CONSTRAINT fk_ingredient_product
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------
--  Every headline and paragraph on the page.
--  `section` is which block it belongs to, `k` is which line.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS copy (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section    VARCHAR(60) NOT NULL,
  k          VARCHAR(60) NOT NULL,
  label      VARCHAR(160) NOT NULL,   -- what the admin shows next to the box
  val_en     TEXT NULL,
  val_bn     TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_copy (section, k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  The three statistics on the "why natural medicine" band
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS figures (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  number     VARCHAR(20) NOT NULL,    -- "80", "21000", "2"
  suffix     VARCHAR(12) NULL,        -- "%", "m"
  note_en    TEXT NULL,
  note_bn    TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  Background video, one row per slot on the page
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS videos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slot        VARCHAR(40) NOT NULL UNIQUE,   -- hero, why, leaf, export, close
  label       VARCHAR(160) NOT NULL,
  file_mp4    VARCHAR(255) NULL,
  poster      VARCHAR(255) NULL,             -- the still shown on phones
  is_enabled  TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  Everything uploaded through the admin
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS media (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  filename    VARCHAR(255) NOT NULL,
  original    VARCHAR(255) NOT NULL,
  kind        VARCHAR(20)  NOT NULL DEFAULT 'image',
  width       INT NULL,
  height      INT NULL,
  bytes       INT UNSIGNED NULL,
  uploaded_by INT UNSIGNED NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_kind (kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  A plain record of who changed what
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NULL,
  what       VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- needs: the everyday wellness needs in the Wellness Finder
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS needs (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(80)  NOT NULL UNIQUE,
  name_en     VARCHAR(120) NOT NULL,           -- "Digestive comfort"
  name_bn     VARCHAR(160) NULL,
  line_en     VARCHAR(255) NULL,               -- one short line under the name
  line_bn     VARCHAR(320) NULL,
  body_en     TEXT NULL,                       -- a sentence or two about the everyday moment
  body_bn     TEXT NULL,
  plant_key   VARCHAR(40)  NULL,               -- which line drawing is its icon
  sort_order  INT NOT NULL DEFAULT 0,
  is_visible  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- which products answer a need, and who each one is for
CREATE TABLE IF NOT EXISTS need_products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  need_id     INT UNSIGNED NOT NULL,
  product_id  INT UNSIGNED NOT NULL,
  for_en      VARCHAR(255) NULL,               -- "Fibre husk for regular digestion."
  for_bn      VARCHAR(320) NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  KEY idx_need (need_id),
  CONSTRAINT fk_np_need    FOREIGN KEY (need_id)    REFERENCES needs(id)    ON DELETE CASCADE,
  CONSTRAINT fk_np_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- talks: YouTube videos shown on the Videos page and around the site
-- kind: creator (people using the products), doctor (doctors explaining),
--       why (short "why natural" explainers)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS talks (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kind        VARCHAR(20)  NOT NULL DEFAULT 'creator',
  youtube_id  VARCHAR(20)  NOT NULL,
  is_short    TINYINT(1) NOT NULL DEFAULT 0,   -- an upright YouTube Short
  title_en    VARCHAR(200) NOT NULL,
  title_bn    VARCHAR(260) NULL,
  speaker_en  VARCHAR(160) NULL,               -- who is talking
  speaker_bn  VARCHAR(200) NULL,
  need_id     INT UNSIGNED NULL,
  product_id  INT UNSIGNED NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  is_visible  TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_kind (kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- faqs: common questions on the home page
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS faqs (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  q_en        VARCHAR(255) NOT NULL,
  q_bn        VARCHAR(320) NULL,
  a_en        TEXT NOT NULL,
  a_bn        TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  is_visible  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
