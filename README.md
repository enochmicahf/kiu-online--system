# KIU Online Student Complaint System

This system is designed for Kampala International University (KIU) to manage student complaints.

## Live Server Setup (aaPanel / VPS)

Since you are hosting this on a live server (like aaPanel), please follow these exact steps:

### Step 1: Create the Database in aaPanel
1. Go to your **aaPanel** dashboard -> **Databases**.
2. Click **Add Database**.
3. Use `kiu_complaints` as the name, or choose your preferred name.
4. Note down the database username and password provided by aaPanel.
5. Click **Import** next to your new database and upload the `database.sql` file.

### Step 2: Configure Connection and Setup Lock
1. Open `includes/config.php` in the aaPanel File Manager.
2. Update these values with the ones you noted in Step 1:
   - `DB_HOST`: Keep as `localhost` in most aaPanel installs.
   - `DB_NAME`: Use your aaPanel database name.
   - `DB_USER`: Use your aaPanel database username.
   - `DB_PASS`: Use your aaPanel database password.
3. Set `ADMIN_SETUP_TOKEN` to a long random string before using any setup or maintenance page.

### Step 3: Run Setup Tools
Open these pages in your browser using your domain name and enter the setup token when prompted:
1. `http://your-domain.com/fix_database.php`
2. `http://your-domain.com/setup_admin.php`

### Step 4: Login as Admin
1. Visit `http://your-domain.com/login.php`.
2. Use the administrator username and password you created in `setup_admin.php`.

### Step 5: Lock Setup Back Down
1. Clear `ADMIN_SETUP_TOKEN` in `includes/config.php` after setup is complete.
2. Keep `setup_admin.php`, `fix_database.php`, and `debug_connection.php` disabled unless you are actively using them.

## Still having issues?
Visit `http://your-domain.com/debug_connection.php` and enter the setup token. It will tell you whether the database connection and administrator account are configured correctly.

Developed for KIU Support.
