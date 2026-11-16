<?php
/* =====================================================================
   Settings for this website.

   COPY THIS FILE, rename the copy to  config.php , and fill in the
   four database values Hostinger gives you. Keep config.php out of
   the public_html folder so nobody can read it from the internet.
   ===================================================================== */

return [

  // ---- The database, from hPanel > Databases > Manage ----
  'db_host' => 'localhost',
  'db_name' => 'PUT_YOUR_DATABASE_NAME_HERE',
  'db_user' => 'PUT_YOUR_DATABASE_USER_HERE',
  'db_pass' => 'PUT_YOUR_DATABASE_PASSWORD_HERE',

  // ---- The website ----
  'site_name'      => 'Square Naturals',
  'default_lang'   => 'en',          // 'en' or 'bn'
  'base_url'       => '',            // leave empty unless in a subfolder

  // ---- Where uploads are written, relative to public_html ----
  'upload_dir'     => 'uploads',
  'max_upload_mb'  => 12,

  // ---- Change this to any long random string before going live ----
  'session_secret' => 'change-this-to-a-long-random-string',
];
