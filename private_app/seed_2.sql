-- =====================================================================
--  Seed part 2: the plants, the figures and the film slots. Bangla is filled in here.
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------- the eight hero plants ----------------

INSERT INTO ingredients
 (slug, plant_key, name_en, name_bn, botanical, part_en, part_bn, known_en, known_bn, does_en, does_bn, product_id, sort_order)
VALUES
('basak','basak','Malabar Nut','বাসক','Adhatoda vasica','Leaf','পাতা',
 'The leaf boiled for a cough in Bengali households for generations.',
 'প্রজন্মের পর প্রজন্ম ধরে বাঙালি ঘরে কাশির জন্য যে পাতা জ্বাল দেওয়া হয়।',
 'Loosens and clears the chest.',
 'বুকে জমা কফ পাতলা করে বের করে দেয়।',
 (SELECT id FROM products WHERE slug='adovas-100-ml'), 10),

('amloki','amloki','Indian Gooseberry','আমলকী','Phyllanthus emblica','Fruit','ফল',
 'The sour green fruit sold by the basket in every kitchen market.',
 'প্রতিটি কাঁচাবাজারে ঝুড়িভর্তি বিক্রি হওয়া টক সবুজ ফল।',
 'One of the densest natural sources of vitamin C.',
 'ভিটামিন সি-এর সবচেয়ে ঘন প্রাকৃতিক উৎসগুলোর একটি।',
 (SELECT id FROM products WHERE slug='amcivit-100-ml'), 20),

('ginkgo','ginkgo','Maidenhair Tree','গিংকো','Ginkgo biloba','Leaf','পাতা',
 'A tree species older than the dinosaurs, unchanged for 200 million years.',
 'ডাইনোসরের চেয়েও পুরোনো একটি গাছ, বিশ কোটি বছরে যার কোনো পরিবর্তন হয়নি।',
 'Improves blood flow to the brain.',
 'মস্তিষ্কে রক্ত চলাচল বাড়ায়।',
 (SELECT id FROM products WHERE slug='giloba-60-30-s'), 30),

('brahmi','brahmi','Water Hyssop','ব্রাহ্মী','Bacopa monnieri','Whole herb','সম্পূর্ণ গাছ',
 'A creeping marsh herb named after Brahma, the creator.',
 'স্রষ্টা ব্রহ্মার নামে নামকরণ করা জলাভূমির লতানো গাছ।',
 'Supports memory and attention.',
 'স্মৃতিশক্তি ও মনোযোগ ধরে রাখতে সাহায্য করে।',
 (SELECT id FROM products WHERE slug='monera-100-ml'), 40),

('papaya','papaya','Papaya Leaf','পেঁপে পাতা','Carica papaya','Leaf','পাতা',
 'The leaf, not the fruit. Crushed and taken during dengue season.',
 'ফল নয়, পাতা। ডেঙ্গুর মৌসুমে যা বেটে খাওয়া হয়।',
 'Supports platelet count.',
 'রক্তের অণুচক্রিকা বাড়াতে সহায়তা করে।',
 (SELECT id FROM products WHERE slug='caripa-30-s'), 50),

('kalojira','kalojira','Black Seed','কালোজিরা','Nigella sativa','Seed oil','বীজের তেল',
 'The black seed pressed into oil, kept in nearly every Bangladeshi pantry.',
 'বাংলাদেশের প্রায় প্রতিটি রান্নাঘরে থাকা কালো বীজ, যা থেকে তেল হয়।',
 'Eases allergic and asthmatic symptoms.',
 'অ্যালার্জি ও হাঁপানির উপসর্গ কমায়।',
 (SELECT id FROM products WHERE slug='nilagel-500-30-s'), 60),

('ispaghula','ispaghula','Psyllium','ইসবগুল','Plantago ovata','Husk','ভুসি',
 'The husk stirred into a glass of water on a hot afternoon.',
 'গরম দুপুরে এক গ্লাস পানিতে গুলে নেওয়া সেই ভুসি।',
 'Soluble fibre that regulates the gut.',
 'দ্রবণীয় আঁশ, যা পরিপাকতন্ত্র স্বাভাবিক রাখে।',
 (SELECT id FROM products WHERE slug='ispergul-15-s'), 70),

('cranberry','cranberry','Cranberry','ক্র্যানবেরি','Vaccinium macrocarpon','Fruit','ফল',
 'A bog berry too sour to eat raw, grown in flooded fields.',
 'জলাভূমিতে জন্মানো এমন টক বেরি, যা কাঁচা খাওয়া যায় না।',
 'Stops bacteria adhering to the urinary tract.',
 'মূত্রনালিতে ব্যাকটেরিয়া আটকে থাকা রোধ করে।',
 (SELECT id FROM products WHERE slug='urobery-100-ml'), 80);

-- ---------------- the three statistics ----------------

INSERT INTO figures (number, suffix, note_en, note_bn, sort_order) VALUES
('80','%',
 'of people worldwide rely on herbal medicine for some part of their primary health care, by WHO estimate.',
 'বিশ্বের এই পরিমাণ মানুষ প্রাথমিক স্বাস্থ্যসেবার কোনো না কোনো অংশে ভেষজ ওষুধের ওপর নির্ভর করেন, বিশ্ব স্বাস্থ্য সংস্থার হিসাবে।', 10),
('21000',NULL,
 'plant species have been identified by WHO as having medicinal potential.',
 'এতগুলো উদ্ভিদ প্রজাতিকে বিশ্ব স্বাস্থ্য সংস্থা ঔষধি সম্ভাবনাময় হিসেবে চিহ্নিত করেছে।', 20),
('2','m',
 'serious adverse drug reactions are reported yearly in the USA from correctly prescribed conventional drugs.',
 'যুক্তরাষ্ট্রে প্রতি বছর এত গুরুতর ওষুধজনিত পার্শ্বপ্রতিক্রিয়ার খবর পাওয়া যায়, সঠিকভাবে প্রেসক্রাইব করা প্রচলিত ওষুধ থেকেই।', 30);

-- The page wording is no longer in this file. It lives in
-- private_app/copy_defaults.php and the installer loads it from there.

-- ---------------- the three video slots ----------------

INSERT INTO videos (slot, label, file_mp4, poster, is_enabled) VALUES
('hero','Opening screen. The camera moves into the forest behind the headline.','hero.mp4','hero.jpg',1),
('why','Why natural medicine. Looking up through the trees behind the three figures.','why.mp4','why.jpg',1),
('leaf','Leaf to bottle. A close view of one leaf before the pack appears.','leaf.mp4','leaf.jpg',1),
('export','Export section. The picture or video inside the curved frame.',NULL,'export-facility.jpg',1),
('close','Closing screen. The view over the forest behind the last headline.','close.mp4','close.jpg',1);

-- Each plant starts with a photograph and a short clip of the same name.
UPDATE ingredients SET image = CONCAT('plant-', slug, '.jpg'), video = CONCAT('plant-', slug, '.mp4');
