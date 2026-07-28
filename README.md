# FlareWise

## Project structure

- `index.php` — public landing page and signed-in redirect
- `pages/` — browser-facing PHP and HTML screens
- `api/` — authentication and JSON/form handlers
- `assets/css/` — stylesheets
- `assets/js/` — client-side JavaScript and Firebase configuration
- `config/` — shared PHP configuration; `database.php` is the single database connection
- `database/` — SQL schema/import file
- `uploads/` — user-uploaded images (created automatically and excluded from source control)

## Run locally

Import `database/flarewise.sql`, configure credentials in `config/database.php`, then serve this folder through PHP/Apache (for example, XAMPP).

## Local server note

XAMPP serves `C:\xampp\htdocs\FlareWise`, while this project is edited in OneDrive. The current project has been copied into XAMPP's folder, so browse to `http://localhost/FlareWise/` after starting **Apache** and **MySQL** in the XAMPP Control Panel. Do not use VS Code Live Server for this project: it does not execute PHP or connect to MySQL.

This app uses Firebase authentication with a local MySQL user record. Import `database/flarewise.sql` into a fresh database before creating accounts. The nullable `password` field is retained only for compatibility with older data; current authentication uses `firebase_uid`. If you already have the older schema without `firebase_uid`, run `database/migrate-firebase.sql` once instead.

Another commit in the works 
