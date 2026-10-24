<?php
include "db.php";
require "auth.php";

if (isset($_POST['save'])) {
    $schoolyear = trim($_POST['schoolyear']);
    $description = trim($_POST['description']);

    if ($schoolyear != "" && $description != "") {
        $stmt = $conn->prepare("INSERT INTO tbl_schoolyear (SCHOOLYEAR, DESCRIPTION) VALUES (?, ?)");
        $stmt->bind_param("ss", $schoolyear, $description);
        $stmt->execute();
        header("Location: schoolyear.php");
        exit();
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $schoolyear = trim($_POST['schoolyear']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE tbl_schoolyear SET SCHOOLYEAR=?, DESCRIPTION=? WHERE ID=?");
    $stmt->bind_param("ssi", $schoolyear, $description, $id);
    $stmt->execute();
    header("Location: schoolyear.php");
    exit();
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tbl_schoolyear WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: schoolyear.php");
    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM tbl_schoolyear WHERE SCHOOLYEAR LIKE ? OR DESCRIPTION LIKE ? ORDER BY ID DESC");
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_schoolyear ORDER BY ID DESC");
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_schoolyear WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>School Year Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>School Year Management</h1>

    <h2><?php echo $editData ? "Update School Year" : "Add School Year"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>School Year</label>
                <input type="text" name="schoolyear" placeholder="Example: 2026-2027"
                    value="<?php echo htmlspecialchars($editData['SCHOOLYEAR'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="School Year Description"
                    value="<?php echo htmlspecialchars($editData['DESCRIPTION'] ?? ''); ?>" required>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update School Year</button>
                    <a href="schoolyear.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save School Year</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search School Year</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search school year..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="schoolyear.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>School Year Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>School Year</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo htmlspecialchars($row['SCHOOLYEAR']); ?></td>
            <td><?php echo htmlspecialchars($row['DESCRIPTION']); ?></td>
            <td>
                <div class="actions">
                    <a href="schoolyear.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this school year?');">
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