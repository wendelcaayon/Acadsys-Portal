<?php
include "db.php";
require "auth.php";

$error = "";

function typeError(mysqli_sql_exception $e) {
    switch ($e->getCode()) {
        case 1062: return "That user type already exists.";
        case 1451: return "Cannot delete this user type because users are still assigned to it.";
        default:   return "Database error. Please check your input.";
    }
}

if (isset($_POST['save'])) {
    $usertype = trim($_POST['usertype']);

    if ($usertype != "") {
        try {
            $stmt = $conn->prepare("INSERT INTO tbl_usertype (USERTYPE) VALUES (?)");
            $stmt->bind_param("s", $usertype);
            $stmt->execute();
            header("Location: usertype.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            $error = typeError($e);
        }
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $usertype = trim($_POST['usertype']);

    try {
        $stmt = $conn->prepare("UPDATE tbl_usertype SET USERTYPE=? WHERE ID=?");
        $stmt->bind_param("si", $usertype, $id);
        $stmt->execute();
        header("Location: usertype.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        $error = typeError($e);
    }
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM tbl_usertype WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: usertype.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        $error = typeError($e);
    }
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM tbl_usertype WHERE USERTYPE LIKE ? ORDER BY ID DESC");
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_usertype ORDER BY ID DESC");
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_usertype WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Type Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>User Type Management</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <h2><?php echo $editData ? "Update User Type" : "Add User Type"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>User Type</label>
                <input type="text" name="usertype" placeholder="Example: Admin"
                    value="<?php echo htmlspecialchars($editData['USERTYPE'] ?? ''); ?>" required>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update User Type</button>
                    <a href="usertype.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save User Type</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search User Type</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search user type..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="usertype.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>User Type Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>User Type</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo htmlspecialchars($row['USERTYPE']); ?></td>
            <td>
                <div class="actions">
                    <a href="usertype.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this user type?');">
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