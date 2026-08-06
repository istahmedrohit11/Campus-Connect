# Campus Connect — Complete PHP/MySQL Web App

Campus Connect is a single, complete student skill-exchange platform built from the supplied **21-screen Figma design**. It uses framework-free PHP, MySQL, HTML, CSS, and JavaScript and is prepared for XAMPP.

## Included flows

### Part 1 — Accounts, profiles, and skills

- Figma landing, login, registration, profile, dashboard, edit-profile, and manage-skills screens
- Student and teacher registration
- Secure login/logout and blocked-account checking
- Role-selection gateway with separate Student, Teacher, and Admin login paths
- Profile editing and validated profile-photo uploads
- Teaching and learning skills
- Live dashboard statistics and recent learning requests

### Part 2 — Posts, search, comments, and notifications

- Create learning-request posts with optional URL and file
- Complete post feed and owner/non-owner detail states
- Comments with optional URL and file
- Mentor search by teaching skill
- Post search by title or description
- Empty and populated search states
- Empty and populated notifications, unread counter, New badge, open target, and mark-all-read

### Part 3 — Sessions, ratings, reports, and admin

- Session requests from profiles, post owners, and comment authors
- Pending, accepted, rejected, completed, and cancelled states
- Mentor accept/reject, learner cancellation, and participant completion
- One rating per participant per completed session
- Live average ratings on profiles
- User, post, and comment reports
- Admin overview, user blocking, post deletion, skill management, and report resolution


## Role-based login flow

Selecting **LOGIN** in the public navigation now opens `login-options.php`. The user chooses one of three account types:

1. Student
2. Teacher
3. Admin

The selected role is securely carried into the existing login form. Authentication checks both the email address and the selected role. Student and teacher accounts continue to the normal dashboard, while an administrator continues to the admin dashboard.

The role-selection page uses a dedicated Figma-matched stylesheet (`assets/css/login-options.css`). CSS and JavaScript URLs include automatic file-version query strings, preventing an older browser-cached stylesheet from showing the unformatted page after an update.

## Installation with XAMPP

1. Extract the folder as:

   ```text
   C:\xampp\htdocs\campus-connect
   ```

2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open:

   ```text
   http://localhost/campus-connect/setup.php
   ```

4. Select **Run Full Setup** once.
5. Open:

   ```text
   http://localhost/campus-connect/
   ```

The setup creates or upgrades the full nine-table `campus_connect` database. Existing Part 1 users and skills are preserved.

## Default admin account

```text
Email: admin@campusconnect.local
Password: Admin123!
```

Change this password directly in the database or replace the generated hash after the first installation before using the project publicly.

## Configuration

Default local settings are in `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'campus_connect');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/campus-connect');
```

Update these values when your MySQL credentials or installation folder differ.

## Database tables

1. `users`
2. `skills`
3. `user_skills`
4. `posts`
5. `comments`
6. `sessions`
7. `ratings`
8. `notifications`
9. `reports`

The importable SQL file is `database/campus_connect.sql`.

## Upload security

- Maximum file size: 5 MB
- Profile images: PNG, JPG/JPEG, GIF, WEBP
- Post/comment files: approved image, office-document, PDF, text, and ZIP extensions
- Randomized server filenames
- Script execution disabled inside `uploads/` through `.htaccess`
- Output escaping, prepared SQL statements, CSRF tokens, password hashing, role guards, and login guards are included

## Full completion test

Use two normal accounts plus the admin account:

1. Register and log in with both normal accounts.
2. Add a teaching skill to one account and search for it from the other.
3. Create a learning post and add a comment with a resource.
4. Confirm the post owner receives a notification.
5. Request a session, accept it as the mentor, and mark it completed.
6. Submit a rating and confirm the receiver’s profile average changes.
7. Submit a report and resolve it from the admin panel.

## Important folders

```text
assets/              CSS, JavaScript, fonts, Figma images, and icons
includes/            Shared configuration, database, layout, and helpers
admin/               Moderation pages
uploads/profiles/    Profile images
uploads/posts/       Post attachments
uploads/comments/    Comment attachments
database/             Full SQL schema
```

## Database import fix

The database installer now creates and selects `campus_connect` before any table statement runs. This fixes the common XAMPP/phpMyAdmin **#1046 – No database selected** error. The schema also uses `CREATE TABLE IF NOT EXISTS` and `INSERT IGNORE`, so running `setup.php` again does not fail because the tables or bundled rows already exist.

For manual import, open phpMyAdmin and import `database/campus_connect.sql` directly from the home page. You do not need to create the database first.
