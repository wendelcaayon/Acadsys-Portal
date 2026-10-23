<?php
include "db.php";
require "auth.php";
require_once "upload_image.php";

$error = "";

// One-time message (e.g. photo problem) carried across the redirect
$flash = $_SESSION['flash'] ?? "";
unset($_SESSION['flash']);

function flashUpload(array $upload) {
    if (!$upload['success'] && $upload['msg'] !== 'No image uploaded.') {
        $_SESSION['flash'] = "User saved, but the photo failed: " . $upload['msg'];
    }
}

function userError(mysqli_sql_exception $e) {
    return ($e->getCode() == 1062) ? "That username already exists." : "Database error. Please check your input.";
}

const USERNAME_RULE = "Username must be 3 to 50 characters: letters, numbers, underscore or hyphen only.";

if (isset($_POST['save'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $usertype_id = (int)$_POST['usertype_id'];

    if (!validUsername($username)) {
        $error = USERNAME_RULE;
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO tbl_users (USERNAME, PASSWORD, FULLNAME, EMAIL, USERTYPE_ID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $passwordHash, $fullname, $email, $usertype_id);
            $stmt->execute();

            // Upload only after the insert succeeds, so a duplicate username
            // can never overwrite someone else's photo.
            flashUpload(uploadUserImage($username));

            header("Location: users.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            $error = userError($e);
        }
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $usertype_id = (int)$_POST['usertype_id'];

    if (!validUsername($username)) {
        $error = USERNAME_RULE;
    } else {
        // Remember the old username so the photo can follow a rename
        $stmt = $conn->prepare("SELECT USERNAME FROM tbl_users WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $oldUsername = $old['USERNAME'] ?? $username;

        try {
            if ($password != "") {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE tbl_users SET USERNAME=?, PASSWORD=?, FULLNAME=?, EMAIL=?, USERTYPE_ID=? WHERE ID=?");
                $stmt->bind_param("ssssii", $username, $passwordHash, $fullname, $email, $usertype_id, $id);
            } else {
                $stmt = $conn->prepare("UPDATE tbl_users SET USERNAME=?, FULLNAME=?, EMAIL=?, USERTYPE_ID=? WHERE ID=?");
                $stmt->bind_param("sssii", $username, $fullname, $email, $usertype_id, $id);
            }
            $stmt->execute();

            $upload = uploadUserImage($username);
            if ($upload['success']) {
                if ($oldUsername !== $username) deleteUserImage($oldUsername);
            } else {
                renameUserImage($oldUsername, $username); // no new photo: keep the old one
                flashUpload($upload);
            }

            // Keep the logged-in user's session in sync if they edited themselves
            if ($id === (int)$_SESSION['user_id']) {
                $_SESSION['username'] = $username;
                $_SESSION['fullname'] = $fullname;
            }

            header("Location: users.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            $error = userError($e);
        }
    }
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];

    if ($id === (int)$_SESSION['user_id']) {
        $error = "You cannot delete your own account while logged in.";
    } else {
        $stmt = $conn->prepare("SELECT USERNAME FROM tbl_users WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $gone = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("DELETE FROM tbl_users WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($gone) deleteUserImage($gone['USERNAME']);

        header("Location: users.php");
        exit();
    }
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare(
        "SELECT u.ID, u.USERNAME, u.FULLNAME, u.EMAIL, u.USERTYPE_ID, t.USERTYPE
         FROM tbl_users u
         INNER JOIN tbl_usertype t ON u.USERTYPE_ID = t.ID
         WHERE u.USERNAME LIKE ? OR u.FULLNAME LIKE ? OR u.EMAIL LIKE ? OR t.USERTYPE LIKE ?
         ORDER BY u.ID DESC"
    );
    $stmt->bind_param("ssss", $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT u.ID, u.USERNAME, u.FULLNAME, u.EMAIL, u.USERTYPE_ID, t.USERTYPE
         FROM tbl_users u
         INNER JOIN tbl_usertype t ON u.USERTYPE_ID = t.ID
         ORDER BY u.ID DESC"
    );
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

$usertypes = $conn->query("SELECT * FROM tbl_usertype ORDER BY ID ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>Users Management</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($flash): ?>
        <p class="error"><?php echo htmlspecialchars($flash); ?></p>
    <?php endif; ?>

    <h2><?php echo $editData ? "Update User" : "Add User"; ?></h2>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Username" maxlength="50"
                    value="<?php echo htmlspecialchars($editData['USERNAME'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                    placeholder="<?php echo $editData ? 'Leave blank to keep current password' : 'Enter password'; ?>"
                    <?php echo $editData ? '' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Full Name"
                    value="<?php echo htmlspecialchars($editData['FULLNAME'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="example@email.com"
                    value="<?php echo htmlspecialchars($editData['EMAIL'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>User Type</label>
                <select name="usertype_id" required>
                    <option value="">-- Select User Type --</option>
                    <?php while ($type = $usertypes->fetch_assoc()): ?>
                        <option value="<?php echo $type['ID']; ?>"
                            <?php if (isset($editData['USERTYPE_ID']) && $editData['USERTYPE_ID'] == $type['ID']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($type['USERTYPE']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Profile Picture (JPG, PNG, GIF or WEBP, max 2 MB)</label>
                <input type="file" name="imageUpload" accept="image/jpeg,image/png,image/gif,image/webp">
                <?php if ($editData): ?>
                    <p class="login-sub" style="text-align:left;margin:8px 0 0;">
                        Current photo:
                        <img class="avatar" src="<?php echo htmlspecialchars(userImage($editData['USERNAME'])); ?>" alt="">
                        &mdash; choose a file only to replace it.
                    </p>
                <?php endif; ?>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update User</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save User</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search Users</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search username, name, email, user type..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="users.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>User Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><img class="avatar" src="<?php echo htmlspecialchars(userImage($row['USERNAME'])); ?>" alt=""></td>
            <td><?php echo htmlspecialchars($row['USERNAME']); ?></td>
            <td><?php echo htmlspecialchars($row['FULLNAME']); ?></td>
            <td><?php echo htmlspecialchars($row['EMAIL']); ?></td>
            <td><?php echo htmlspecialchars($row['USERTYPE']); ?></td>
            <td>
                <div class="actions">
                    <a href="users.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                        <button type="submit" name="delete" class="btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php include "bottom_bar.php"; ?>
</div>
</div>
</body>
</html>