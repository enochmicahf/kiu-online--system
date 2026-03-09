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
        <h3 class="text-white fw-bold mb-4">Notifications</h3>
        <ul class="list-group list-group-flush bg-dark border border-secondary rounded-3">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $notifications = $stmt->fetchAll();

            foreach ($notifications as $note):
            ?>
                <li class="list-group-item bg-transparent border-secondary py-3 <?php echo $note['is_read'] ? '' : 'bg-primary bg-opacity-10'; ?>">
                    <p class="mb-1 text-white"><?php echo htmlspecialchars($note['message']); ?></p>
                    <small class="text-secondary small opacity-75"><?php echo date('M d, Y H:i', strtotime($note['created_at'])); ?></small>
                </li>
            <?php endforeach; if (empty($notifications)): ?>
                <li class="list-group-item bg-transparent border-secondary text-secondary py-5 text-center">
                    <i class="fas fa-bell-slash fs-2 mb-3 d-block opacity-25"></i>
                    No notifications found.
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
