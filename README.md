# KIU Online Student Complaint System

This system is designed for Kampala International University (KIU) to manage student complaints.

## 🛠 HOW TO START (FOLLOW THESE STEPS)

If you are having trouble logging in, please follow these exact steps:

### Step 1: Create the Database
- Open your database tool (like **phpMyAdmin**).
- Create a new database named `kiu_complaints`.
- Import the file `database.sql` into that database.

### Step 2: Configure Connection
- Open the file `includes/config.php` in a text editor.
- Make sure `DB_USER` (usually `root`) and `DB_PASS` (usually empty `''`) match your computer's settings.

### Step 3: Run the Setup Scripts (IMPORTANT)
Open your web browser and visit these two links one by one:
1. `http://localhost/fix_database.php` (Fixes the tables)
2. `http://localhost/setup_admin.php` (Creates the **admin** user)

### Step 4: Login as Admin
- Visit: `http://localhost/login.php`
- **Username:** `admin`
- **Password:** `admin`

---

## 🔍 Still having issues?
Visit `http://localhost/debug_connection.php` in your browser. It will tell you exactly what is wrong (e.g., if the database is missing or the admin account wasn't created).

---
Developed for KIU Support.
