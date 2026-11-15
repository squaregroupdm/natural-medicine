<?php
/* =====================================================================
   copy_defaults.php: every piece of wording on the public site.

   Each row is:  [section, key, label shown in the admin, English, Bangla]

   This file is the starting text. On install it is copied into the
   `copy` table, and from then on the admin (Page text) is the place to
   edit it. If a row is ever missing from the table, the site falls back
   to the text here, so the page can never show a blank.

   Adding a new piece of text:
     1. add a row here
     2. use copyText('section', 'key') in index.php
     3. open Admin > Page text once; the new row appears there by itself
   ===================================================================== */

return [

  // ---- top bar ----
  ['nav', 'brand',    'Wordmark',                 'SQUARE', 'SQUARE'],
  ['nav', 'division', 'Division line',            'Herbal & Ayurvedic', 'হারবাল ও আয়ুর্বেদিক'],
  ['nav', 'plants',   'Menu: plants',             'Plants', 'উদ্ভিদ'],
  ['nav', 'areas',    'Menu: health areas',       'Health areas', 'স্বাস্থ্য ক্ষেত্র'],
  ['nav', 'products', 'Menu: products',           'Products', 'পণ্য'],
  ['nav', 'export',   'Menu: export',             'Export', 'রপ্তানি'],
  ['nav', 'menu',     'Phone menu button',        'Menu', 'মেনু'],
  ['nav', 'close',    'Phone menu close button',  'Close', 'বন্ধ'],
  ['nav', 'skip',     'Skip link for keyboards',  'Skip to content', 'মূল অংশে যান'],

  // ---- opening screen ----
  ['hero', 'eyebrow', 'Small line above the headline', 'Natural medicine, the long story in brief', 'ন্যাচারাল মেডিসিন, দীর্ঘ গল্পের সংক্ষিপ্ত রূপ'],
  ['hero', 'title',   'Headline',                      'The plant comes first.', 'শুরুটা গাছ থেকেই।'],
  ['hero', 'sub',     'Paragraph under the headline',
      'Every product in this range begins as something that grows. We start with the plant, then show you what it becomes.',
      'এই রেঞ্জের প্রতিটি পণ্যের শুরু একটি জীবন্ত গাছ থেকে। আমরা গাছ দিয়ে শুরু করি, তারপর দেখাই সেটি কী হয়ে ওঠে।'],
  ['hero', 'cta1',    'Main button',                   'Meet the plants', 'উদ্ভিদগুলো দেখুন'],
  ['hero', 'cta2',    'Second link',                   'Browse all products', 'সব পণ্য দেখুন'],
  ['hero', 'cue',     'Scroll prompt',                 'Scroll', 'স্ক্রল করুন'],

  // ---- the plant plates ----
  ['plate', 'word',    'The word before the plate number', 'Plate', 'প্লেট'],
  ['plate', 'part',    'Label for the part of the plant',  'Part used', 'ব্যবহৃত অংশ'],
  ['plate', 'becomes', 'Label above the product name',     'Becomes', 'যা হয়ে ওঠে'],
  ['plate', 'from',    'Starting price. Keep {price}',     'from {price}', '{price} থেকে'],

  // ---- why natural medicine ----
  ['why', 'label',   'Section label', 'Why natural medicine', 'কেন ন্যাচারাল মেডিসিন'],
  ['why', 'title',   'Headline',      'Most of the world already treats this way.', 'পৃথিবীর বেশির ভাগ মানুষ এভাবেই চিকিৎসা নেন।'],
  ['why', 'sources', 'Sources line',
      'Sources: World Health Organization, US Food and Drug Administration, National Institutes of Health',
      'সূত্র: বিশ্ব স্বাস্থ্য সংস্থা, ইউএস ফুড অ্যান্ড ড্রাগ অ্যাডমিনিস্ট্রেশন, ন্যাশনাল ইনস্টিটিউটস অব হেলথ'],

  // ---- the plant rail ----
  ['materia', 'label', 'Section label', 'The plants we work with', 'যেসব উদ্ভিদ নিয়ে আমাদের কাজ'],
  ['materia', 'title', 'Headline',      'Known first. Studied second.', 'আগে পরিচিত। পরে প্রমাণিত।'],
  ['materia', 'end',   'Last card in the row', 'And many more in the full range.', 'সম্পূর্ণ রেঞ্জে আছে আরও অনেক।'],

  // ---- leaf to bottle ----
  ['topack', 'label', 'Section label', 'From leaf to bottle', 'পাতা থেকে বোতল'],
  ['topack', 'title', 'Headline',      'The same leaf. Measured.', 'একই পাতা। এবার মাপা।'],
  ['topack', 'body',  'Paragraph',
      'Basak has been boiled for a cough in this country for as long as anyone can remember. The work is turning that into a dose you can count on.',
      'এ দেশে কাশির জন্য বাসক পাতা জ্বাল দেওয়ার চল বহুকালের। আমাদের কাজ হলো সেটিকে নির্ভরযোগ্য একটি মাত্রায় রূপ দেওয়া।'],

  // ---- health areas ----
  ['areas', 'label',     'Section label', 'Where they work', 'কোন কোন ক্ষেত্রে'],
  ['areas', 'title',     'Headline',      'Find it by what it helps.', 'প্রয়োজন ধরে খুঁজুন।'],
  ['areas', 'body',      'Paragraph',     'Pick an area and the product list filters to it.', 'একটি ক্ষেত্র বেছে নিন, পণ্যের তালিকা সেই অনুযায়ী সাজবে।'],
  ['areas', 'unit_one',  'Word after the number 1',       'product', 'পণ্য'],
  ['areas', 'unit_many', 'Word after any other number',   'products', 'পণ্য'],

  // ---- product list ----
  ['catalogue', 'label',   'Section label',                'The full range', 'সম্পূর্ণ রেঞ্জ'],
  ['catalogue', 'title',   'Headline',                     'Every product, by what it is made from.', 'প্রতিটি পণ্য, উপাদান অনুযায়ী।'],
  ['catalogue', 'search',  'Search box placeholder',       'Search plant or product', 'উদ্ভিদ বা পণ্য খুঁজুন'],
  ['catalogue', 'all',     'First filter button',          'All', 'সব'],
  ['catalogue', 'sort',    'Sort menu label',              'Sort', 'সাজান'],
  ['catalogue', 'sort_az', 'Sort choice 1',                'Plant, A to Z', 'উপাদান অনুসারে'],
  ['catalogue', 'sort_lo', 'Sort choice 2',                'Price, low to high', 'দাম: কম থেকে বেশি'],
  ['catalogue', 'sort_hi', 'Sort choice 3',                'Price, high to low', 'দাম: বেশি থেকে কম'],
  ['catalogue', 'count',   'Result count. Keep {n} and {total}', '{n} of {total} products', '{total}টির মধ্যে {n}টি পণ্য'],
  ['catalogue', 'details', 'Button on each product',       'Details', 'বিস্তারিত'],
  ['catalogue', 'empty',   'Message when nothing matches', 'Nothing matches that. Try another plant or clear the filters.', 'কিছু পাওয়া যায়নি। অন্য কিছু খুঁজুন বা ফিল্টার মুছে দিন।'],
  ['catalogue', 'clear',   'Clear filters button',         'Clear filters', 'ফিল্টার মুছুন'],

  // ---- product detail window ----
  ['product', 'made',   'Label: ingredient',   'Made from', 'যা থেকে তৈরি'],
  ['product', 'uses',   'Label: indications',  'Used for', 'যে কাজে লাগে'],
  ['product', 'area',   'Label: health area',  'Health area', 'স্বাস্থ্য ক্ষেত্র'],
  ['product', 'packs',  'Label: packs',        'Pack and price', 'প্যাক ও মূল্য'],
  ['product', 'mrp',    'Note under the price','Maximum retail price in Bangladesh.', 'বাংলাদেশে সর্বোচ্চ খুচরা মূল্য।'],
  ['product', 'advice', 'Health note',         'Use as directed by a registered physician.', 'নিবন্ধিত চিকিৎসকের পরামর্শ অনুযায়ী ব্যবহার করুন।'],
  ['product', 'close',  'Close button',        'Close', 'বন্ধ করুন'],

  // ---- export ----
  ['export', 'label',       'Section label', 'Export operations', 'রপ্তানি কার্যক্রম'],
  ['export', 'title',       'Headline',      'Shipping natural products since 2005.', '২০০৫ সাল থেকে প্রাকৃতিক পণ্য রপ্তানি।'],
  ['export', 'body',        'Paragraph',
      'The division exports finished products, manufactures under contract, and develops formulations with technology transfer to the partner.',
      'এই বিভাগ প্রস্তুত পণ্য রপ্তানি করে, চুক্তিভিত্তিক উৎপাদন করে এবং প্রযুক্তি হস্তান্তরসহ নতুন ফর্মুলেশন তৈরি করে।'],
  ['export', 'panel',       'List heading',  'What we offer partners', 'অংশীদারদের জন্য যা আছে'],
  ['export', 'i1',          'Point 1', 'Quality products at competitive price', 'প্রতিযোগিতামূলক দামে মানসম্পন্ন পণ্য'],
  ['export', 'i2',          'Point 2', 'Facilities for contract manufacturing', 'চুক্তিভিত্তিক উৎপাদনের সুবিধা'],
  ['export', 'i3',          'Point 3', 'Product development with subsequent technology transfer', 'প্রযুক্তি হস্তান্তরসহ পণ্য উন্নয়ন'],
  ['export', 'i4',          'Point 4', 'Assistance in product promotion and training in overseas markets', 'বিদেশি বাজারে পণ্য প্রচার ও প্রশিক্ষণে সহায়তা'],
  ['export', 'i5',          'Point 5 (optional)', '', ''],
  ['export', 'photo_alt',   'Description of the photo, for screen readers', 'The Herbal & Ayurvedic Division facility of SQUARE Pharmaceuticals PLC', 'স্কয়ার ফার্মাসিউটিক্যালস পিএলসির হারবাল ও আয়ুর্বেদিক বিভাগের কারখানা'],

  // ---- closing screen ----
  ['close', 'title', 'Closing headline', 'Find the plant behind your medicine.', 'আপনার ওষুধের পেছনের গাছটি খুঁজে নিন।'],
  ['close', 'cta',   'Button text',      'Browse all products', 'সব পণ্য দেখুন'],

  // ---- footer ----
  ['footer', 'wordmark',   'Large wordmark',     'Natural medicine', 'ন্যাচারাল মেডিসিন'],
  ['footer', 'tagline',    'Line under it',      'the long story in brief', 'দীর্ঘ গল্পের সংক্ষিপ্ত রূপ'],
  ['footer', 'org',        'Organisation line',  'SQUARE Pharmaceuticals PLC, Herbal & Ayurvedic Division', 'স্কয়ার ফার্মাসিউটিক্যালস পিএলসি, হারবাল ও আয়ুর্বেদিক বিভাগ'],
  ['footer', 'disclaimer', 'Health notice',
      'This site is for general information. It is not medical advice. Speak to a registered physician before starting any product.',
      'এই ওয়েবসাইটটি সাধারণ তথ্যের জন্য। এটি চিকিৎসা পরামর্শ নয়। যেকোনো পণ্য শুরুর আগে নিবন্ধিত চিকিৎসকের পরামর্শ নিন।'],
  ['footer', 'contact',    'Contact line (optional, hidden when empty)', '', ''],
  ['footer', 'top',        'Back to top link',   'Back to top', 'উপরে যান'],
  ['footer', 'rights',     'Rights line',        'All rights reserved.', 'সর্বস্বত্ব সংরক্ষিত।'],
];
