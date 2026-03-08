<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark all as read when visiting
$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$user_id]);

$page_title = "Notifications - KIU Complaints";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h3>Notifications</h3>
        <hr>
        <ul class="list-group">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $notifications = $stmt->fetchAll();

            foreach ($notifications as $note):
            ?>
                <li class="list-group-item <?php echo $note['is_read'] ? '' : 'list-group-item-info'; ?>">
                    <p class="mb-1"><?php echo htmlspecialchars($note['message']); ?></p>
                    <small class="text-muted"><?php echo $note['created_at']; ?></small>
                </li>
            <?php endforeach; if (empty($notifications)): ?>
                <li class="list-group-item">No notifications found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
