# KIU Online Student Complaint System

This system is designed for Kampala International University (KIU) to manage student complaints.

## 🚀 LIVE SERVER SETUP (aaPanel / VPS)

Since you are hosting this on a live server (like aaPanel), please follow these exact steps:

### Step 1: Create the Database in aaPanel
1. Go to your **aaPanel** dashboard -> **Databases**.
2. Click **Add Database**.
3. Use `kiu_complaints` as the name (or any name you prefer).
4. **Note down** the Database Username and Password provided by aaPanel.
5. Click **Import** next to your new database and upload the `database.sql` file.

### Step 2: Configure Connection
1. Open the file `includes/config.php` in the aaPanel File Manager.
2. Update the details with the ones you noted in Step 1:
   - `DB_HOST`: Keep as `localhost` (usually works on aaPanel).
   - `DB_NAME`: Use your aaPanel database name.
   - `DB_USER`: Use your aaPanel database username.
   - `DB_PASS`: Use your aaPanel database password.

### Step 3: Run Setup Scripts (IMPORTANT)
Open your web browser and visit these links using **your domain name**:
1. `http://your-domain.com/fix_database.php` (Fixes the tables)
2. `http://your-domain.com/setup_admin.php` (Creates the **admin** user)

### Step 4: Login as Admin
- Visit: `http://your-domain.com/login.php`
- **Username:** `admin`
- **Password:** `admin`

---

## 🔍 Still having issues?
Visit `http://your-domain.com/debug_connection.php` in your browser. It will tell you exactly what is wrong (e.g., if the database username/password in `config.php` is wrong).

---
Developed for KIU Support.
