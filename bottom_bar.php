<?php require_once "upload_image.php"; ?>
<div class="bottom-bar">
    <span class="signed-in">
        <?php if (!empty($_SESSION['username'])): ?>
            <img class="avatar" width="32" height="32"
                 src="<?php echo htmlspecialchars(userImage($_SESSION['username'])); ?>" alt="">
        <?php endif; ?>
        <span>Signed in as <strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? ''); ?></strong></span>
    </span>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>