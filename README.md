# KIU Online Student Complaint Registration and Management System

A professional, high-fidelity web application developed for Kampala International University (KIU), Uganda. This system allows students to submit complaints and university staff/administrators to manage, resolve, and communicate with students effectively.

## 🚀 Getting Started

To access the system and the Admin Dashboard, follow these setup steps:

### 1. Database Setup
- Create a MySQL database named `kiu_complaints`.
- Import the provided `database.sql` file into your database.

### 2. Configuration
- Open `includes/config.php` and update the database credentials (`DB_USER`, `DB_PASS`) to match your local server environment.

### 3. Initialize the System
Run these scripts in your browser once to ensure everything is set up correctly:
- `http://your-site.com/fix_database.php` (Updates the schema and adds missing columns)
- `http://your-site.com/setup_admin.php` (Creates the default Administrator account)

---

## 🔑 Accessing the Admin Dashboard

The system uses a unified dashboard that changes features based on your login role.

1. **Go to the Login Page:** Navigate to `login.php`.
2. **Use Admin Credentials:**
   - **Username:** `admin`
   - **Password:** `admin`
3. **Admin Dashboard Features:**
   - Once logged in as 'admin', your sidebar will automatically show administrative tools.
   - **User Management:** Verify student details and academic info.
   - **Manage Announcements:** Post system-wide updates to students.
   - **Complaints Management:** View, "Approve", and Resolve student complaints.

## 📁 System Features
- **Modern Dark Theme:** Professional "VJProne" aesthetic with Inter font.
- **Role-Based Access:** Distinct interfaces for Students, Staff, and Admins.
- **Announcement System:** Global university notifications with priority levels.
- **Attachment Support:** Students can upload JPG, PNG, PDF, or DOC files with their complaints.
- **Student Verification:** Admins can verify registration numbers and academic details.

---

## 🛠 Troubleshooting

### "Invalid username or password"
If you cannot log in with the default admin credentials:
1. Ensure you have run `setup_admin.php` in your browser.
2. If you still have issues, running `setup_admin.php` again will **reset** the 'admin' password to `admin`.
3. Check that your database connection is correct in `includes/config.php`.

Developed for KIU Support System.
