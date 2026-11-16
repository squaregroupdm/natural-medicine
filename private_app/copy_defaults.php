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
     2. use copyText('section', 'key') in a page
     3. open Admin > Page text once; the new row appears there by itself
   ===================================================================== */

return [

  // ---- the brand (used in the top bar, the opening, the footer and page titles) ----
  ['brand', 'name',    'Brand name',                    'Square Naturals', 'স্কয়ার ন্যাচারালস'],
  ['brand', 'tagline', 'Tagline',                       'Natural Wellness. Trusted by Square.', 'প্রাকৃতিক সুস্থতা। স্কয়ারের আস্থায়।'],
  ['brand', 'theme',   'Theme line',                    'From nature\'s roots to everyday wellness.', 'প্রকৃতির শিকড় থেকে প্রতিদিনের সুস্থতায়'],
  ['brand', 'org',     'Organisation line',             'SQUARE Pharmaceuticals PLC', 'স্কয়ার ফার্মাসিউটিক্যালস পিএলসি'],

  // ---- top bar ----
  ['nav', 'plants',   'Menu: plants',             'Plants', 'উদ্ভিদ'],
  ['nav', 'finder',   'Menu: wellness finder',    'Wellness Finder', 'ওয়েলনেস ফাইন্ডার'],
  ['nav', 'products', 'Menu: products',           'Products', 'পণ্য'],
  ['nav', 'videos',   'Menu: videos',             'Videos', 'ভিডিও'],
  ['nav', 'menu',     'Phone menu button',        'Menu', 'মেনু'],
  ['nav', 'close',    'Phone menu close button',  'Close', 'বন্ধ'],
  ['nav', 'skip',     'Skip link for keyboards',  'Skip to content', 'মূল অংশে যান'],

  // ---- opening screen ----
  ['hero', 'sub',  'Paragraph under the headline',
      'Herbal, vitamin and natural products for ordinary days. Made by SQUARE, so you know what is inside.',
      'সাধারণ দিনগুলোর জন্য ভেষজ, ভিটামিন ও প্রাকৃতিক পণ্য। স্কয়ারের তৈরি, তাই ভেতরে কী আছে আপনি জানেন।'],
  ['hero', 'cta1', 'Main button',   'Find what fits you', 'আপনার জন্য কোনটি'],
  ['hero', 'cta2', 'Second link',   'Meet the plants', 'উদ্ভিদগুলো দেখুন'],
  ['hero', 'cue',  'Scroll prompt', 'Scroll', 'স্ক্রল করুন'],

  // ---- why natural ----
  ['why', 'label',   'Section label', 'Why natural', 'কেন প্রাকৃতিক'],
  ['why', 'title',   'Headline',      'Care that starts before you need a cure.', 'অসুখের আগেই যে যত্নের শুরু।'],
  ['why', 'a1_t',    'Answer 1: heading', 'It supports. It does not replace.', 'এটি সহায়তা করে। বিকল্প নয়।'],
  ['why', 'a1_b',    'Answer 1: text',    'Natural products help the body with its everyday work. When you are ill, a physician still comes first.', 'প্রাকৃতিক পণ্য শরীরের প্রতিদিনের কাজে সহায়তা করে। অসুস্থ হলে আগে চিকিৎসকই।'],
  ['why', 'a2_t',    'Answer 2: heading', 'Before, not after.', 'পরে নয়, আগে।'],
  ['why', 'a2_b',    'Answer 2: text',    'Most of wellness is small and daily: what you eat, how you sleep, what you add.', 'সুস্থতার বেশির ভাগটাই ছোট আর প্রতিদিনের: কী খান, কেমন ঘুমান, সঙ্গে কী যোগ করেন।'],
  ['why', 'a3_t',    'Answer 3: heading', 'From plants you already know.', 'চেনা গাছ থেকেই।'],
  ['why', 'a3_b',    'Answer 3: text',    'Basak, amloki, kalojira. The same plants your family has trusted, measured and tested.', 'বাসক, আমলকী, কালোজিরা। যে গাছে আপনার পরিবারের আস্থা, সেগুলোই মাপা ও পরীক্ষিত রূপে।'],
  ['why', 'sources', 'Sources line for the figures', 'Sources: World Health Organization, National Institutes of Health', 'সূত্র: বিশ্ব স্বাস্থ্য সংস্থা, ন্যাশনাল ইনস্টিটিউটস অব হেলথ'],
  ['why', 'watch',   'Heading above the short videos', 'Why natural, in a minute', 'কেন প্রাকৃতিক, এক মিনিটে'],

  // ---- from the root (the plants) ----
  ['materia', 'label',   'Section label', 'Back to the root', 'ফিরে যাই শিকড়ে'],
  ['materia', 'title',   'Headline',      'Known first. Studied second.', 'আগে পরিচিত। পরে প্রমাণিত।'],
  ['materia', 'hint',    'Small hint under the headline', 'Each of these products began as something that grows. Meet the plant, then the medicine it becomes.', 'এই প্রতিটি পণ্যের শুরু কোনো না কোনো গাছ থেকে। আগে গাছটিকে চিনুন, তারপর যে ওষুধ সে হয়ে ওঠে।'],
  ['plate',   'becomes', 'Label above the product name',  'Becomes', 'যা হয়ে ওঠে'],
  ['plate',   'from',    'Starting price. Keep {price}',  'from {price}', '{price} থেকে'],

  // ---- into everyday life (the Wellness Finder) ----
  ['finder', 'label',   'Section label', 'Into everyday life', 'প্রতিদিনের জীবনে'],
  ['finder', 'title',   'Headline',      'What would you like to support?', 'আপনি কোন দিকটির যত্ন নিতে চান?'],
  ['finder', 'body',    'Paragraph',     'Pick what matters to you today. We will show what fits.', 'আজ আপনার কাছে যা জরুরি, সেটি বেছে নিন। মানানসই পণ্য আমরা দেখাব।'],
  ['finder', 'for',     'Small label above each product', 'Good for', 'যার জন্য'],
  ['finder', 'videos',  'Heading above videos for a need', 'People talking about this', 'এ নিয়ে যাঁরা বলছেন'],
  ['finder', 'all',     'Link under the products', 'See all products', 'সব পণ্য দেখুন'],
  ['finder', 'advice',  'Health note', 'This is a guide, not a diagnosis. Speak to a registered physician before starting any product.', 'এটি একটি নির্দেশিকা, রোগ নির্ণয় নয়। যেকোনো পণ্য শুরুর আগে নিবন্ধিত চিকিৎসকের পরামর্শ নিন।'],

  // ---- trusted by Square ----
  ['trust', 'label', 'Section label', 'Trusted by Square', 'স্কয়ারের আস্থায়'],
  ['trust', 'title', 'Headline',      'Nature, with someone answerable for it.', 'প্রকৃতি, সঙ্গে দায়িত্ব নেওয়ার কেউ।'],
  ['trust', 'p1_t',  'Point 1: heading', 'Made by SQUARE', 'স্কয়ারের তৈরি'],
  ['trust', 'p1_b',  'Point 1: text',    'Produced by SQUARE Pharmaceuticals PLC in its own Herbal & Ayurvedic facility.', 'স্কয়ার ফার্মাসিউটিক্যালস পিএলসির নিজস্ব হারবাল ও আয়ুর্বেদিক কারখানায় উৎপাদিত।'],
  ['trust', 'p2_t',  'Point 2: heading', 'Said plainly', 'সোজাসুজি বলা'],
  ['trust', 'p2_b',  'Point 2: text',    'Every product names its ingredient and what it is used for. No vague promises.', 'প্রতিটি পণ্যে লেখা আছে উপাদান কী এবং কী কাজে লাগে। অস্পষ্ট প্রতিশ্রুতি নয়।'],
  ['trust', 'p3_t',  'Point 3: heading', 'Exported since 2005', '২০০৫ সাল থেকে রপ্তানি'],
  ['trust', 'p3_b',  'Point 3: text',    'The same products are made to the standard overseas partners ask for.', 'বিদেশি অংশীদারদের চাওয়া মান অনুযায়ীই তৈরি হয় একই পণ্য।'],
  ['trust', 'p4_t',  'Point 4: heading (optional)', '', ''],
  ['trust', 'p4_b',  'Point 4: text (optional)',    '', ''],
  ['trust', 'doctors', 'Heading above the doctor videos', 'Doctors explain', 'চিকিৎসকেরা বলছেন'],

  // ---- videos ----
  ['videos', 'label',     'Page label',                 'Videos', 'ভিডিও'],
  ['videos', 'title',     'Page headline',              'Hear it from people who use it, and people who study it.', 'শুনুন যাঁরা ব্যবহার করেন, আর যাঁরা এ নিয়ে পড়াশোনা করেন।'],
  ['videos', 'creators',  'Shelf 1 heading',            'Real routines', 'সত্যিকারের রুটিন'],
  ['videos', 'creators_b','Shelf 1 line',               'People sharing how natural products fit into their day.', 'প্রাকৃতিক পণ্য কীভাবে তাঁদের দিনে জায়গা পেয়েছে, জানাচ্ছেন তাঁরা।'],
  ['videos', 'doctors',   'Shelf 2 heading',            'Doctors explain', 'চিকিৎসকেরা বলছেন'],
  ['videos', 'doctors_b', 'Shelf 2 line',               'Conversations with physicians about natural wellness.', 'প্রাকৃতিক সুস্থতা নিয়ে চিকিৎসকদের সঙ্গে আলাপ।'],
  ['videos', 'why',       'Shelf 3 heading',            'Why natural', 'কেন প্রাকৃতিক'],
  ['videos', 'why_b',     'Shelf 3 line',               'Short answers to common questions.', 'সাধারণ প্রশ্নের ছোট উত্তর।'],
  ['videos', 'teaser',    'Home page heading for creator videos', 'Real routines', 'সত্যিকারের রুটিন'],
  ['videos', 'all',       'Link to the videos page',    'All videos', 'সব ভিডিও'],
  ['videos', 'play',      'Play button label',          'Play video', 'ভিডিও চালান'],
  ['videos', 'empty',     'Message when there are no videos yet', 'Videos are on their way. Please check back soon.', 'ভিডিও আসছে শিগগিরই। আবার দেখে যান।'],
  ['videos', 'sample',    'Shown when a placeholder card is opened', 'This is a sample. The real video will play here.', 'এটি একটি নমুনা। আসল ভিডিওটি এখানেই চলবে।'],
  ['videos', 'opinion',   'Note under the videos',      'Views shared in these videos are personal experiences, not medical advice.', 'এসব ভিডিওতে বলা কথা ব্যক্তিগত অভিজ্ঞতা, চিকিৎসা পরামর্শ নয়।'],

  // ---- products ----
  ['catalogue', 'label',    'Section label',                'The range', 'সম্পূর্ণ রেঞ্জ'],
  ['catalogue', 'title',    'Headline',                     'Every product, and what it is made from.', 'প্রতিটি পণ্য, আর যা থেকে তা তৈরি।'],
  ['catalogue', 'search',   'Search box placeholder',       'Search plant or product', 'উদ্ভিদ বা পণ্য খুঁজুন'],
  ['catalogue', 'all',      'First filter button',          'All', 'সব'],
  ['catalogue', 'areas',    'Name of the filter row, for screen readers', 'Health areas', 'স্বাস্থ্য ক্ষেত্র'],
  ['catalogue', 'sort',     'Sort menu label',              'Sort', 'সাজান'],
  ['catalogue', 'sort_az',  'Sort choice 1',                'Featured first', 'প্রধান পণ্য আগে'],
  ['catalogue', 'sort_lo',  'Sort choice 2',                'Price, low to high', 'দাম: কম থেকে বেশি'],
  ['catalogue', 'sort_hi',  'Sort choice 3',                'Price, high to low', 'দাম: বেশি থেকে কম'],
  ['catalogue', 'count',    'Result count. Keep {n} and {total}', '{n} of {total} products', '{total}টির মধ্যে {n}টি পণ্য'],
  ['catalogue', 'empty',    'Message when nothing matches', 'Nothing matches that. Try another plant or clear the filters.', 'কিছু পাওয়া যায়নি। অন্য কিছু খুঁজুন বা ফিল্টার মুছে দিন।'],
  ['catalogue', 'clear',    'Clear filters button',         'Clear filters', 'ফিল্টার মুছুন'],
  ['catalogue', 'browse',   'Button to the products page',  'Browse all products', 'সব পণ্য দেখুন'],

  // ---- product detail window ----
  ['product', 'made',   'Label: ingredient',   'Made from', 'যা থেকে তৈরি'],
  ['product', 'uses',   'Label: indications',  'Used for', 'যে কাজে লাগে'],
  ['product', 'area',   'Label: health area',  'Health area', 'স্বাস্থ্য ক্ষেত্র'],
  ['product', 'packs',  'Label: packs',        'Pack and price', 'প্যাক ও মূল্য'],
  ['product', 'watch',  'Label: videos',       'Watch', 'ভিডিও'],
  ['product', 'mrp',    'Note under the price','Maximum retail price in Bangladesh.', 'বাংলাদেশে সর্বোচ্চ খুচরা মূল্য।'],
  ['product', 'advice', 'Health note',         'Use as directed by a registered physician.', 'নিবন্ধিত চিকিৎসকের পরামর্শ অনুযায়ী ব্যবহার করুন।'],
  ['product', 'close',  'Close button',        'Close', 'বন্ধ করুন'],

  // ---- common questions ----
  ['faq', 'label', 'Section label', 'Good to know', 'জেনে রাখা ভালো'],
  ['faq', 'title', 'Headline',      'Questions people ask first.', 'মানুষ আগে যা জানতে চান।'],

  // ---- closing screen ----
  ['close', 'title', 'Closing headline', 'Start with one small thing.', 'একটি ছোট যত্ন দিয়েই শুরু হোক।'],
  ['close', 'cta',   'Button text',      'Find what fits you', 'আপনার জন্য কোনটি'],

  // ---- footer ----
  ['footer', 'org',        'Organisation line',  'SQUARE Pharmaceuticals PLC, Herbal & Ayurvedic Division', 'স্কয়ার ফার্মাসিউটিক্যালস পিএলসি, হারবাল ও আয়ুর্বেদিক বিভাগ'],
  ['footer', 'disclaimer', 'Health notice',
      'This site is for general information. It is not medical advice. Speak to a registered physician before starting any product.',
      'এই ওয়েবসাইটটি সাধারণ তথ্যের জন্য। এটি চিকিৎসা পরামর্শ নয়। যেকোনো পণ্য শুরুর আগে নিবন্ধিত চিকিৎসকের পরামর্শ নিন।'],
  ['footer', 'contact',    'Contact line (optional, hidden when empty)', '', ''],
  ['footer', 'top',        'Back to top link',   'Back to top', 'উপরে যান'],
  ['footer', 'rights',     'Rights line',        'All rights reserved.', 'সর্বস্বত্ব সংরক্ষিত।'],
];
