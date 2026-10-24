<?php
require_once "session.php";
require_once "access.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Each page is checked against PAGE_ACCESS (access.php) using the logged-in
// user type. Pages that set $publicPage = true (before requiring this file),
// like index.php, are open to any logged-in user.
$currentScript = basename($_SERVER['SCRIPT_NAME']);

if (empty($publicPage) && !canAccess($_SESSION['usertype'] ?? '', $currentScript)) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Access Denied</title><link rel="stylesheet" href="style.css"></head>
    <body>
    <div class="login-wrap"><div class="login-box">
        <h2>Access Denied</h2>
        <p class="login-sub">Your account does not have permission to open this page.</p>
        <a href="index.php" class="btn">Back to Home</a>
        <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div></div>
    </body>
    </html>
    <?php
    exit();
}