<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect_to(string $path): void
{
    header("Location: {$path}");
    exit();
}

function current_user_is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_is_admin(): bool
{
    return current_user_is_logged_in() && (($_SESSION['role'] ?? '') === 'admin');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function has_valid_csrf_token(?string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_valid_csrf_token(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!has_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }
}

function setup_token_is_configured(): bool
{
    return defined('ADMIN_SETUP_TOKEN') && trim((string) ADMIN_SETUP_TOKEN) !== '';
}

function has_valid_setup_token(?string $token = null): bool
{
    if (!setup_token_is_configured()) {
        return false;
    }

    $candidate = $token;
    if ($candidate === null) {
        $candidate = $_POST['setup_token'] ?? $_GET['setup_token'] ?? '';
    }

    return is_string($candidate)
        && $candidate !== ''
        && hash_equals((string) ADMIN_SETUP_TOKEN, $candidate);
}

function ensure_admin_or_setup_token(string $title, string $message): string
{
    if (current_user_is_admin()) {
        return '';
    }

    $submittedToken = $_POST['setup_token'] ?? $_GET['setup_token'] ?? '';
    if (has_valid_setup_token($submittedToken)) {
        return $submittedToken;
    }

    $titleText = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $messageText = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $tokenConfigured = setup_token_is_configured();
    $errorText = '';

    if ($submittedToken !== '' && $tokenConfigured) {
        $errorText = '<div style="background:#fee2e2;color:#991b1b;padding:12px 14px;border-radius:10px;margin-bottom:16px;">The supplied setup token was not valid.</div>';
    }

    if ($tokenConfigured) {
        $helperText = 'Enter the setup token configured in includes/config.php to continue.';
        $tokenForm = '
            <form method="POST" style="margin-top: 20px;">
                <label for="setup_token" style="display:block;font-weight:600;margin-bottom:8px;">Setup Token</label>
                <input id="setup_token" type="password" name="setup_token" required style="width:100%;padding:12px 14px;border:1px solid #cbd5e1;border-radius:10px;box-sizing:border-box;">
                <button type="submit" style="margin-top:16px;background:#2563eb;color:#fff;border:none;border-radius:10px;padding:12px 18px;font-weight:600;cursor:pointer;">Continue</button>
            </form>';
    } else {
        $helperText = 'This tool is disabled until ADMIN_SETUP_TOKEN is set in includes/config.php.';
        $tokenForm = '';
    }

    http_response_code(403);
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $titleText . '</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 40px 20px;">
    <div style="max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 28px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);">
        <h1 style="margin-top:0;color:#0f172a;font-size:28px;">' . $titleText . '</h1>
        <p style="color:#475569;line-height:1.6;">' . $messageText . '</p>
        ' . $errorText . '
        <p style="color:#334155;line-height:1.6;margin-bottom:0;">' . htmlspecialchars($helperText, ENT_QUOTES, 'UTF-8') . '</p>
        ' . $tokenForm . '
    </div>
</body>
</html>';
    exit();
}
?>
