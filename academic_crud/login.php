<?php
require "db.php";
require "session.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = isset($_GET['registered']) ? "Registration successful. You can now log in." : "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password']; // do not trim passwords

    $stmt = $conn->prepare(
        "SELECT u.*, t.USERTYPE
         FROM tbl_users u
         INNER JOIN tbl_usertype t ON u.USERTYPE_ID = t.ID
         WHERE u.USERNAME=?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['PASSWORD'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']  = $user['ID'];
        $_SESSION['username'] = $user['USERNAME'];
        $_SESSION['fullname'] = $user['FULLNAME'];
        $_SESSION['usertype'] = $user['USERTYPE'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h2>Academic Management System</h2>
        <p class="login-sub">Sign in to continue</p>

        <?php if ($success): ?>
            <p class="error" style="background:#e6f4ea;color:#1e7e34;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>

        <p class="login-sub" style="margin-top:20px;">No account yet? <a href="register.php">Register here</a></p>
    </div>
</div>
</body>
</html>