<?php
require "db.php";
require "session.php";
require_once "upload_image.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$username = $fullname = $email = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($username === "" || $fullname === "" || $email === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif (!validUsername($username)) {
        $error = "Username must be 3 to 50 characters: letters, numbers, underscore or hyphen only.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Public sign-ups are always "Student"; nobody can choose Admin here.
        $stmt = $conn->prepare("SELECT ID FROM tbl_usertype WHERE USERTYPE='Student'");
        $stmt->execute();
        $type = $stmt->get_result()->fetch_assoc();

        if (!$type) {
            $error = "Registration is unavailable: the 'Student' user type does not exist.";
        } else {
            $usertype_id = (int)$type['ID'];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $conn->prepare("INSERT INTO tbl_users (USERNAME, PASSWORD, FULLNAME, EMAIL, USERTYPE_ID) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $username, $passwordHash, $fullname, $email, $usertype_id);
                $stmt->execute();

                // Photo is optional; saved only after the account was created.
                uploadUserImage($username);

                header("Location: login.php?registered=1");
                exit();
            } catch (mysqli_sql_exception $e) {
                $error = ($e->getCode() == 1062) ? "That username is already taken." : "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h2>Create Account</h2>
        <p class="login-sub">Register to use the Academic Management System</p>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Letters, numbers, _ or -" maxlength="50"
                    value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Full Name" maxlength="150"
                    value="<?php echo htmlspecialchars($fullname); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="example@email.com" maxlength="150"
                    value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="At least 8 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Re-enter password" required>
            </div>
            <div class="form-group">
                <label>Profile Picture (optional)</label>
                <input type="file" name="imageUpload" accept="image/jpeg,image/png,image/gif,image/webp">
            </div>
            <button type="submit" name="register">Register</button>
        </form>

        <p class="login-sub" style="margin-top:20px;">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>