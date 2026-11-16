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
('why','Why natural. Looking up through the trees behind the three answers.','why.mp4','why.jpg',1),
('leaf','Trusted by Square. A close view of one leaf behind the trust points.','leaf.mp4','leaf.jpg',1),
('export','Export section. The picture or video inside the curved frame.',NULL,'export-facility.jpg',1),
('close','Closing screen. The view over the forest behind the last headline.','close.mp4','close.jpg',1);

-- Each plant starts with a photograph and a short clip of the same name.
UPDATE ingredients SET image = CONCAT('plant-', slug, '.jpg'), video = CONCAT('plant-', slug, '.mp4');

-- The alarming third figure is kept but hidden: the new story leads with benefit, not fear.
UPDATE figures SET is_visible = 0 WHERE sort_order = (SELECT m FROM (SELECT MAX(sort_order) m FROM figures) t);

-- ---------------- the five everyday needs (from the launch plan) ----------------
INSERT INTO needs (slug, name_en, name_bn, line_en, line_bn, body_en, body_bn, plant_key, sort_order) VALUES
('daily','Daily wellness','প্রতিদিনের সুস্থতা','Wellness starts before illness.','অসুখের আগেই শুরু হোক যত্ন।','The small things you do every day add up. These are for the ordinary days, not the sick ones.','প্রতিদিনের ছোট ছোট যত্নই বড় হয়ে ওঠে। এগুলো অসুখের দিনের জন্য নয়, সাধারণ দিনের জন্য।','amloki',10),
('digestive','Digestive comfort','হজমে স্বস্তি','Comfort that starts with better habits.','ভালো অভ্যাস থেকেই আসে স্বস্তি।','Heavy meals, late dinners, too little fibre. A calmer stomach usually begins with routine.','ভারী খাবার, দেরিতে রাতের খাওয়া, কম আঁশ। পেটের স্বস্তি সাধারণত শুরু হয় নিয়ম থেকে।','ispaghula',20),
('mind-women','Mind and women''s wellness','মন ও নারীর সুস্থতা','Memory, balance and daily nutrition.','স্মৃতি, ভারসাম্য আর প্রতিদিনের পুষ্টি।','Busy days ask a lot of the mind and the body. Support for focus, for the monthly cycle, and for everyday nourishment.','ব্যস্ত দিন মন আর শরীর দুটোর কাছেই অনেক কিছু চায়। মনোযোগ, মাসিক চক্র আর প্রতিদিনের পুষ্টির জন্য সহায়তা।','brahmi',30),
('beauty-liver','Beauty and liver care','সৌন্দর্য ও লিভারের যত্ন','Glow begins on the inside.','উজ্জ্বলতার শুরু ভেতর থেকে।','Skin, hair and nails show what is happening within. So does the liver, quietly.','ত্বক, চুল আর নখ ভেতরের অবস্থাই জানান দেয়। লিভারও দেয়, নীরবে।','kalojira',40),
('active','Breathing and active living','শ্বাস ও সক্রিয় জীবন','Breathe easy. Keep moving.','স্বস্তিতে শ্বাস নিন। সচল থাকুন।','Changing seasons, dust and long days on your feet. Care for the chest and the heart that keep you going.','ঋতু বদল, ধুলো আর সারাদিনের ছোটাছুটি। যে বুক আর হৃদয় আপনাকে সচল রাখে, তাদের যত্ন।','basak',50);

-- which products answer each need. A product is matched by the start of its brand name.
INSERT INTO need_products (need_id, product_id, for_en, for_bn, sort_order)
SELECT n.id, (SELECT MIN(p.id) FROM products p WHERE p.brand LIKE CONCAT(x.brand, '%')), x.for_en, x.for_bn, x.ord
FROM (
  SELECT 'daily' need, 'Toco' brand, 'Vitamin E for skin, hair and everyday antioxidant care.' for_en, 'ত্বক, চুল ও প্রতিদিনের অ্যান্টিঅক্সিডেন্ট যত্নে ভিটামিন ই।' for_bn, 10 ord
  UNION ALL SELECT 'daily','Trumega','Omega-3 for heart health.','হৃদয়ের সুস্থতায় ওমেগা-৩।',20
  UNION ALL SELECT 'daily','AmCivit','Amla-based vitamin C for the family.','পরিবারের জন্য আমলকীর ভিটামিন সি।',30
  UNION ALL SELECT 'digestive','Ispergul','Fibre husk for regular digestion.','নিয়মিত হজমে ইসবগুলের ভুসি।',10
  UNION ALL SELECT 'digestive','Pepnor','Cumin-based syrup for bloating and heaviness after meals.','খাওয়ার পর পেট ফাঁপা ও অস্বস্তিতে জিরার সিরাপ।',20
  UNION ALL SELECT 'mind-women','Monera','Brahmi extract to support memory.','স্মৃতিশক্তির সহায়তায় ব্রাহ্মী।',10
  UNION ALL SELECT 'mind-women','Femony','Five herbs for monthly cycle comfort.','মাসিক চক্রের স্বস্তিতে পাঁচ ভেষজ।',20
  UNION ALL SELECT 'mind-women','Navit','Spirulina for daily nutrition.','প্রতিদিনের পুষ্টিতে স্পিরুলিনা।',30
  UNION ALL SELECT 'beauty-liver','Bioprem','Biotin for hair and nails.','চুল ও নখের যত্নে বায়োটিন।',10
  UNION ALL SELECT 'beauty-liver','Toco','Vitamin E for skin and hair.','ত্বক ও চুলের জন্য ভিটামিন ই।',20
  UNION ALL SELECT 'beauty-liver','Silybin','Milk thistle for liver care.','লিভারের যত্নে মিল্ক থিসল।',30
  UNION ALL SELECT 'active','Adovas','Basak syrup for cough and cold.','সর্দি-কাশিতে বাসক সিরাপ।',10
  UNION ALL SELECT 'active','Trumega','Omega-3 for heart health.','হৃদয়ের সুস্থতায় ওমেগা-৩।',20
) x JOIN needs n ON n.slug = x.need
WHERE EXISTS (SELECT 1 FROM products p WHERE p.brand LIKE CONCAT(x.brand, '%'));

-- the focus brands are shown first
UPDATE products SET is_featured = 1 WHERE brand REGEXP '^(Toco|Trumega|Monera|Ispergul|Pepnor|Femony|AmCivit|Silybin|Adovas|Bioprem|Navit)';

-- ---------------- common questions ----------------
INSERT INTO faqs (q_en, q_bn, a_en, a_bn, sort_order) VALUES
('What is Square Naturals?','স্কয়ার ন্যাচারালস কী?','Square Naturals brings together the herbal, vitamin, mineral and natural products made by SQUARE Pharmaceuticals PLC. The pack and leaflet of each product say exactly what it is and how to use it.','স্কয়ার ফার্মাসিউটিক্যালস পিএলসির তৈরি ভেষজ, ভিটামিন, মিনারেল ও প্রাকৃতিক পণ্যগুলো একসঙ্গে নিয়েই স্কয়ার ন্যাচারালস। প্রতিটি পণ্য কী এবং কীভাবে ব্যবহার করতে হয়, তা প্যাক ও লিফলেটে লেখা আছে।',10),
('Do these replace my regular medicine?','এগুলো কি আমার নিয়মিত ওষুধের বিকল্প?','No. They are meant to support everyday wellness. If you are unwell or on treatment, keep following your physician.','না। এগুলো প্রতিদিনের সুস্থতায় সহায়তার জন্য। অসুস্থ হলে বা চিকিৎসাধীন থাকলে চিকিৎসকের পরামর্শই মেনে চলুন।',20),
('Can I take them with other medicines?','অন্য ওষুধের সঙ্গে কি এগুলো খাওয়া যায়?','Ask your physician or pharmacist first. Natural products can still interact with other medicines.','আগে চিকিৎসক বা ফার্মাসিস্টকে জিজ্ঞেস করুন। প্রাকৃতিক পণ্যও অন্য ওষুধের সঙ্গে বিক্রিয়া করতে পারে।',30),
('Are natural products right for everyone?','প্রাকৃতিক পণ্য কি সবার জন্য উপযুক্ত?','Natural does not mean right for everyone. During pregnancy, for children, or with a long-term condition, speak to a physician before starting.','প্রাকৃতিক মানেই সবার জন্য উপযুক্ত নয়। গর্ভাবস্থায়, শিশুদের ক্ষেত্রে বা দীর্ঘমেয়াদি অসুস্থতায় শুরু করার আগে চিকিৎসকের সঙ্গে কথা বলুন।',40),
('Are the prices on this site current?','এই সাইটের দাম কি হালনাগাদ?','They are the maximum retail prices in Bangladesh when this page was last updated. The price printed on the pack is the one that applies.','পাতাটি শেষবার হালনাগাদের সময় বাংলাদেশে সর্বোচ্চ খুচরা মূল্য এগুলোই ছিল। প্যাকের গায়ে লেখা দামই চূড়ান্ত।',50);
