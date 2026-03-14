<?php
require_once 'includes/security.php';

if (!current_user_is_logged_in()) {
    redirect_to('login.php');
}

if (current_user_is_admin()) {
    redirect_to('admin_users.php');
}

redirect_to('dashboard.php');
?>
