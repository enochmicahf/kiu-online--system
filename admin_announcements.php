<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/security.php';

if (!current_user_is_admin()) {
    redirect_to('dashboard.php');
}

$message = "";
$message_type = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf_token();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $priority = $_POST['priority'] ?? 'info';
    $allowed_priorities = ['info', 'warning', 'danger'];

    if ($title !== '' && $content !== '' && in_array($priority, $allowed_priorities, true)) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, priority) VALUES (?, ?, ?)");
        if ($stmt->execute([$title, $content, $priority])) {
            $message = "Announcement posted successfully!";
        }
    } else {
        $message = "Please provide a valid title, message, and priority.";
        $message_type = "danger";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement'])) {
    $announcement_id = (int) ($_POST['announcement_id'] ?? 0);
    if ($announcement_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$announcement_id]);
        redirect_to('admin_announcements.php?msg=deleted');
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    if ($message === '') {
        $message = "Announcement deleted successfully!";
    }
}

$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();

$page_title = "Manage Announcements - KIU Support";
$current_page = 'announcements';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent border-secondary p-3">
                <h5 class="mb-0 text-white fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Post New</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> border-0 small py-2"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <?php echo csrf_input(); ?>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Announcement Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Priority Level</label>
                        <select name="priority" class="form-select">
                            <option value="info">Information (Blue)</option>
                            <option value="warning">Important (Yellow)</option>
                            <option value="danger">Urgent (Red)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Content Details</label>
                        <textarea name="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="add_announcement" class="btn btn-primary w-100 fw-bold">Post Announcement</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent border-secondary p-3">
                <h5 class="mb-0 text-white fw-bold"><i class="fas fa-bullhorn me-2 text-primary"></i> Current Announcements</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Announcement</th>
                                <th>Level</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($announcements as $a): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($a['title']); ?></div>
                                    <small class="text-secondary opacity-75 d-block text-truncate" style="max-width: 300px;">
                                        <?php echo htmlspecialchars($a['content']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $a['priority']; ?> bg-opacity-10 text-<?php echo $a['priority']; ?> border border-<?php echo $a['priority']; ?> border-opacity-25 px-2 py-1">
                                        <?php echo strtoupper($a['priority']); ?>
                                    </span>
                                </td>
                                <td><small class="text-secondary"><?php echo date('M d, Y', strtotime($a['created_at'])); ?></small></td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this announcement?')">
                                        <input type="hidden" name="announcement_id" value="<?php echo $a['id']; ?>">
                                        <input type="hidden" name="delete_announcement" value="1">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" class="btn btn-link text-danger opacity-50 hover-opacity-100 p-0 border-0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; if(empty($announcements)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-secondary">No active announcements.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
