# Installing on Hostinger, step by step

Written for someone who has never set up a website before. Every click
is spelled out. Nothing is assumed.

Set aside about **30 minutes**. You can stop between any two parts and
come back later.

**What you need open:**

- This project on your computer. On the GitHub page press the green **Code** button, then **Download ZIP**, and unzip it
- Hostinger, signed in, at hpanel.hostinger.com
- Notepad, or any plain text editor

---

## Part 0: Decide on the address (5 minutes)

Skip this if you already know where the site is going.

The site can live either on a main domain or on a subdomain, which is a
prefix on a domain you already own, like
`naturalmedicine.maybegetthis.com`. A subdomain is free and is the
right choice for a first version.

**To make one:**

1. In hPanel, open the website you want the subdomain under
2. In the left menu find **Domains**, then **Subdomains**
3. In the "Create a new subdomain" box, type `naturalmedicine`
4. Leave "Custom folder for subdomain" alone
5. Press **Create**

Hostinger makes a folder for it, usually at
`domains/maybegetthis.com/public_html/naturalmedicine`. Note that path
down. Wherever this guide says **public_html**, you use that folder
instead.

Give it a few minutes. A new subdomain can take up to an hour to start
answering, though it is usually much faster.

---

## Part 1: Check the PHP version (2 minutes)

The site needs PHP 8.0 or newer. Almost certainly fine, but worth
thirty seconds now rather than confusion later.

1. In hPanel, open your website
2. Left menu, **Advanced**, then **PHP Configuration**
3. Look at the version selected at the top

If it says 8.0 or higher, close the page and move on.

If it says 7.something, click the newest 8.x version in the list and
press **Update**. Nothing else on your hosting will mind.

---

## Part 2: Create the database (5 minutes)

This is where all your products, prices and text will live.

1. In hPanel, left menu, **Databases**, then **MySQL Databases**
2. Find the box headed "Create a New MySQL Database and Database User"
3. Fill in three fields:

   | Field | Type this |
   |---|---|
   | Database name | `naturalmed` |
   | Database username | `naturalmed` |
   | Password | Press **Generate**, then copy it |

4. Press **Create**

Hostinger adds an account prefix to both names, so they come out looking
like `u123456789_naturalmed`. **This is normal.** You must use the full
prefixed version, not what you typed.

5. Scroll down to "List of Current MySQL Databases and Users" and you
   will see your new database listed

**Write these three down now, exactly as shown on screen:**

```
Database name:      u_________naturalmed
Database username:  u_________naturalmed
Database password:  ____________________
```

Keep that somewhere safe. You need it in the next part, and you cannot
see the password again later.

---

## Part 3: Write your settings file (5 minutes)

This is the only file you ever edit by hand.

1. On your computer, open the unzipped project folder
2. Open the `private_app` folder inside it
3. Find the file **config.sample.php**
4. Right-click it, choose **Copy**, then right-click in empty space and
   choose **Paste**. You now have a second copy
5. Rename that copy to exactly **config.php**

   If Windows warns about changing the file extension, say yes. If you
   cannot see the `.php` on the end, open the View menu in File Explorer
   and tick **File name extensions**.

6. Right-click **config.php**, choose **Open with**, then **Notepad**
7. Near the top you will see this:

```php
  'db_host' => 'localhost',
  'db_name' => 'PUT_YOUR_DATABASE_NAME_HERE',
  'db_user' => 'PUT_YOUR_DATABASE_USER_HERE',
  'db_pass' => 'PUT_YOUR_DATABASE_PASSWORD_HERE',
```

8. Replace the three placeholders with the values from Part 2. Keep the
   straight quote marks exactly where they are:

```php
  'db_host' => 'localhost',
  'db_name' => 'u123456789_naturalmed',
  'db_user' => 'u123456789_naturalmed',
  'db_pass' => 'YourGeneratedPassword',
```

9. Further down find this line:

```php
  'session_secret' => 'change-this-to-a-long-random-string',
```

   Replace the middle part with any long jumble of letters and numbers.
   Mash the keyboard for thirty characters. You never type it again.

10. Save the file and close Notepad

**Three things that break this file:**

- Curly quotes instead of straight ones. Notepad is safe. Microsoft
  Word is not. Do not use Word.
- Deleting a comma at the end of a line
- Leaving the `u123456789_` prefix off the name or username

---

## Part 4: Upload the files (10 minutes)

1. In hPanel, left menu, **Files**, then **File Manager**
2. You will see a folder list. Double-click into **domains**, then your
   domain name

You should now see a folder called **public_html**. Stay at this level,
the one where you can see public_html sitting in the list.

### 4a. Upload private_app and seed

1. Press the **upload** icon in the toolbar, the arrow pointing up
2. Choose **Upload folder** if offered, otherwise **Upload files**
3. Select the **private_app** folder from the project on your computer
4. Wait for it to finish
5. Do the same for the **seed** folder. It holds the 75 starter product
   photographs and the factory picture. The installer copies them into
   place for you

**private_app must sit beside public_html, not inside it.** It holds
your database password. If it ends up inside public_html, move it out.

Your folder list should now read:

```
domains/yourdomain/
  private_app/
  public_html/
  seed/
```

### 4b. Upload the site itself

1. Double-click into **public_html**
2. Upload the **contents** of the project's public_html folder, so:
   - `index.php`
   - the `admin` folder
   - the `assets` folder
   - the `uploads` folder
   - the `.htaccess` file

The contents go inside, not the public_html folder itself. You do not
want `public_html/public_html`.

If `.htaccess` does not appear in your file picker, it is because
Windows hides files starting with a dot. In File Explorer, View menu,
tick **Hidden items**, then upload it.

When finished, inside public_html you should see:

```
index.php
.htaccess
admin/
assets/
uploads/
```

---

## Part 5: Run the installer (2 minutes)

This builds the database tables, loads all 71 products and every line of
page text, and copies the starter pictures into place.

1. In your browser go to:

   `yourdomain.com/admin/install.php`

2. Fill in three boxes:
   - Your name
   - Your email
   - A password, at least 10 characters. This is your admin login,
     so choose something you will remember
3. Press **Build the site**

Wait a few seconds. It should say **All set**.

### Then delete the installer. This is not optional.

1. Back in File Manager, go into `public_html/admin`
2. Find **install.php**
3. Right-click it, choose **Delete**

Leaving that file there lets any stranger who finds it rebuild your
database from scratch.

---

## Part 6: Check the pictures arrived

The installer's last line tells you how many starter pictures it copied
and how many product images it registered. It should say 75 registered.

If it says 0, the `seed` folder was not beside `private_app`. Fix it by
hand:

1. File Manager, go to `public_html/uploads/products`
2. Upload everything from the project's `seed/product-images` folder
3. Go to `public_html/uploads/video` and upload `seed/media/export-facility.jpg`
4. Sign in at `yourdomain.com/admin/`, click **Images**, and press
   **Scan the uploads folder**

Once the pictures are in, the `seed` folder on the server is no longer
needed. You can delete it or leave it.

---

## Part 7: Check it all works (3 minutes)

Visit each of these:

| Address | What you should see |
|---|---|
| `yourdomain.com` | The website, in English, with painted leaves behind the headline |
| `yourdomain.com/?lang=bn` | The same site in Bangla |
| `yourdomain.com/admin/` | The sign-in screen |
| `yourdomain.com/private_app/config.php` | **403 Forbidden** |

That last one matters most. If it shows your database password instead
of an error, stop and fix it before going further. It means private_app ended up in the
wrong place.

Then scroll the whole site once, top to bottom, and check:

- The plant cards draw themselves in as they appear
- On a computer, the row of plants slides sideways while the screen holds still. On a phone you swipe it
- The product cards show photographs, and clicking one opens its details
- Clicking a health area filters the product list

---

## Part 8: Turn on HTTPS (2 minutes)

The padlock in the address bar. Free, and browsers now warn visitors
without it.

1. hPanel, left menu, **Security**, then **SSL**
2. If it is not already active, press **Install SSL**
3. Once it turns green, turn on **Force HTTPS**

---

## When something goes wrong

| What you see | What it means | What to do |
|---|---|---|
| "Setup is not finished: config.php is missing" | The file is in the wrong folder or still named config.sample.php | It belongs in `private_app`, beside public_html, named exactly `config.php` |
| "Could not connect to the database" | One of the three values is wrong | Copy them again from hPanel. Both name and username carry the `u123456789_` prefix |
| A completely blank white page | PHP hit an error | Check the PHP version is 8.0 or newer, Part 1 |
| The page loads but looks like plain text | The assets folder did not upload | Check `public_html/assets/css/site.css` exists |
| Products show but no images | The scan has not run | Admin, Images, Scan the uploads folder |
| "403 Forbidden" on the main site | Permissions | Right-click public_html, Permissions, set folders to 755 |
| The site shows a Hostinger holding page | The files went into the wrong folder | They must be inside `public_html`, or your subdomain folder |
| Setup says it has already been run | You ran install.php twice | Delete install.php and go to `/admin/` instead |

If none of those fit, copy exactly what the screen says and pass it to
whoever is helping you with the site.

---

## After it is live

Everything is editable at `yourdomain.com/admin/`.

| Screen | Controls |
|---|---|
| Dashboard | What is still missing, and a record of changes |
| Products | All 71. Price, pack, ingredient, description, image, order |
| Plants | The eight on the home page, and what each becomes |
| Health areas | The filter names and their order |
| Page text | Every headline and paragraph on the site |
| Images | Upload new photographs |
| Video | Three slots that take a video or a still picture |

Every text box is a pair, English left and Bangla right. Leave the
Bangla empty and the site shows English instead, so nothing ever appears
blank while translation is still happening.

The dashboard tells you what still needs doing.

---
