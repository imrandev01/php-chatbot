Deployment guide — free PHP + MySQL hosts (quick)

Goal: get this PHP/MySQL app online at zero cost.

Recommended free hosts (easy, low friction)
- 000webhost (by Hostinger) — supports PHP + MySQL, phpMyAdmin, FTP. Good for quick demos.
- InfinityFree — unlimited bandwidth/space limits; supports PHP & MySQL via control panel.

Alternative (more stable, not strictly 0-cost forever but free tiers)
- Oracle Cloud Always Free (VM + managed DB) — more setup but production-capable.

Quick deploy to 000webhost (fastest path)
1) Create account
   - Go to https://www.000webhost.com/ and sign up.
2) Create a new site
   - Use the control panel to create a new website (choose a free subdomain or point a domain).
3) Upload files
   - In the control panel open "File Manager" or use FTP (FTP credentials in control panel).
   - Upload the entire project folder contents into the `public_html` (or the app root) on the host.
   - Ensure `dbconfig/config.php` is in `dbconfig/` and readable.
4) Create the MySQL database
   - In 000webhost control panel open "Manage Databases" -> "Create new database".
   - Note the host (often `localhost`), database name, username and password.
5) Import SQL
   - Open phpMyAdmin from the control panel and select the database you created.
   - Import `database.sql` and `authentication.sql` (and `youtube.sql` if you want extra data).
6) Update configuration
   - Edit `dbconfig/config.php` on the host to set DB credentials — or set environment variables if the host supports them.
   - For 000webhost you will likely have to edit the file and replace the defaults or set:
     - DB_HOST -> hostname given by control panel
     - DB_NAME -> database name
     - DB_USER -> username
     - DB_PASS -> password
   - Alternatively, in the file `dbconfig/config.php` the code will pick up environment variables if the host provides them.

   Optional: enable AI replies for unmatched queries
   - If you'd like the site to fall back to a real AI when no canned reply matches, obtain an OpenAI API key at https://platform.openai.com/.
   - Do NOT commit this key to GitHub. Instead, open the host's File Manager, edit `dbconfig/config.php` and add near the top (after the DB settings):

```php
// Optional: OpenAI API key for AI fallback
putenv('OPENAI_API_KEY=sk-...'); // or set via host env if supported
// or define('OPENAI_API_KEY', 'sk-...'); but prefer env vars
```

   - After adding the key, the chatbot will call the OpenAI Chat Completions API when it can't find a matching reply in the database.
7) Test
   - Browse your site URL (the free subdomain). If you see DB errors, double-check credentials and that the import succeeded.

Notes for production
- Free hosts often disable some PHP features and have limited resources. Use them for demos or lightweight apps only.
- If you plan to scale or want better reliability, consider paid hosting or the Oracle Cloud always-free instances (requires more configuration).

If you want, I can:
- Prepare a zip of the project (not possible from here without your confirmation) or help generate a minimal `.htaccess` for friendly URLs.
- Walk you step-by-step while you create the 000webhost account and upload files (I can provide exact text to paste into the control panel and the exact edits for `dbconfig/config.php`).

Tell me which host you prefer and whether you want me to create a production-friendly `.htaccess` and disable debug output. I'll then (a) update any remaining local config, (b) give the exact quick-edit content for `dbconfig/config.php` you can paste on the server, and (c) walk you through phpMyAdmin import commands.