# Qamarshan (microNation) — Setup Guide (Windows + XAMPP)

This repository contains a small PHP site intended to run under XAMPP (Apache + MySQL) on Windows. Below are concise, step-by-step instructions to get the site up and running locally.

## Requirements

- Windows (you are on Windows)
- XAMPP (Apache + MySQL + PHP). Default installation path assumed: `E:\xampp` (adjust if different).
- A modern browser (Chrome, Edge, Firefox)
- Optional: a code editor (VS Code)

## Quick checklist (high level)

1. Start Apache and MySQL in the XAMPP control panel.
2. Import the database schema `db.sql` into MySQL (via phpMyAdmin or CLI).
3. Ensure `config.php` database credentials match your MySQL user.
4. (Optional) Create a real admin password hash and update the `users` table.
5. Open `http://localhost/microNation/index.php` in your browser and test.

## Detailed steps

1) Start services

- Open XAMPP Control Panel and click `Start` for both Apache and MySQL.
- Verify `http://localhost/` loads the XAMPP dashboard.

2) Import database

Option A — phpMyAdmin (GUI):
- Visit: http://localhost/phpmyadmin
- Click `Import` -> Choose file -> select `e:\xampp\htdocs\microNation\db.sql` -> Go

Option B — MySQL CLI (faster for power users):
- Open PowerShell and run (adjust paths if your XAMPP is elsewhere):

```powershell
# If your MySQL root has no password
E:\xampp\mysql\bin\mysql.exe -u root < E:\xampp\htdocs\microNation\db.sql

# If root has a password, you'll be prompted
E:\xampp\mysql\bin\mysql.exe -u root -p < E:\xampp\htdocs\microNation\db.sql
```

Notes:
- The SQL file creates the database `qamarshan_cms` and tables.
- There's a placeholder bcrypt hash in the seed; replace it with a real hash before using the admin account.

3) Configure database credentials

- Open `e:\xampp\htdocs\microNation\config.php` and confirm the `$QAMAR_DB` settings (host, port, name, user, pass).
- Defaults are set to `127.0.0.1`, `3306`, database `qamarshan_cms`, user `root`, empty password.
- If you change credentials in MySQL, update this file accordingly.

4) Generate a secure admin password hash

- You can generate a bcrypt hash using the small helper script included in `tools/make_hash.php` or directly with PHP CLI.

Using the helper script (recommended):

```powershell
# Run from anywhere; path to PHP might be required if not in PATH
E:\xampp\php\php.exe E:\xampp\htdocs\microNation\tools\make_hash.php "YourSuperSecretPassword"
```

Using PHP CLI directly:

```powershell
E:\xampp\php\php.exe -r "echo password_hash('YourSuperSecretPassword', PASSWORD_BCRYPT) . PHP_EOL;"
```

- Take the resulting string and run an SQL update in phpMyAdmin or CLI to set the admin password_hash. Example SQL:

```sql
USE qamarshan_cms;
UPDATE users
SET password_hash = '<PASTE_HASH_HERE>'
WHERE username = 'admin';
```

5) Ensure uploads directory writable

- `config.php` attempts to create `uploads/` automatically. On Windows this usually works. If file uploads fail, ensure the `e:\xampp\htdocs\microNation\uploads` directory exists and is writable by Apache (right-click -> Properties -> Security).

6) Try the site

- Visit: http://localhost/microNation/index.php
- Click `Apply for Citizenship` or `Log In` to exercise the forms. After logging in, you'll land on your profile page which includes activity widgets (applications and messages).

## Common troubleshooting

- Apache won't start: another service (IIS, Skype) likely using port 80. Change Apache ports via XAMPP config or stop the other service.
- Database import fails: open the SQL file and look for the error line; try importing smaller parts or using phpMyAdmin.
- Blank pages / PHP errors: enable error display temporarily in `php.ini` or check Apache error logs (`E:\xampp\apache\logs\error.log`).

## Security notes (local dev -> production)

- Do not use the `root` MySQL user in production. Create a dedicated DB user with limited privileges.
- Replace placeholder password hashes and do not commit real secrets to source control.
- Consider setting HTTPS and stronger session cookie flags in production.

## Next steps I can help with

- Run the SQL import for you (I can produce exact commands you can paste into PowerShell).
- Create or update an admin account with a password you provide (or help you generate a secure one).
- Add basic unit tests or a small healthcheck endpoint.

If you'd like, tell me which step you'd like me to help execute next (import DB, create admin hash and SQL, or verify config.php).