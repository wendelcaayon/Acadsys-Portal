<?php
include "db.php";
require "auth.php";

if (isset($_POST['save'])) {
    $yearlevel = trim($_POST['yearlevel']);
    $description = trim($_POST['description']);

    if ($yearlevel != "" && $description != "") {
        $stmt = $conn->prepare("INSERT INTO tbl_yearlevel (YEARLEVEL, DESCRIPTION) VALUES (?, ?)");
        $stmt->bind_param("ss", $yearlevel, $description);
        $stmt->execute();
        header("Location: year_level.php");
        exit();
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $yearlevel = trim($_POST['yearlevel']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE tbl_yearlevel SET YEARLEVEL=?, DESCRIPTION=? WHERE ID=?");
    $stmt->bind_param("ssi", $yearlevel, $description, $id);
    $stmt->execute();
    header("Location: year_level.php");
    exit();
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tbl_yearlevel WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: year_level.php");
    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM tbl_yearlevel WHERE YEARLEVEL LIKE ? OR DESCRIPTION LIKE ? ORDER BY ID DESC");
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_yearlevel ORDER BY ID DESC");
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_yearlevel WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Year Level Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>Year Level Management</h1>

    <h2><?php echo $editData ? "Update Year Level" : "Add Year Level"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Year Level</label>
                <input type="text" name="yearlevel" placeholder="Example: 1ST"
                    value="<?php echo htmlspecialchars($editData['YEARLEVEL'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="Year Level Description"
                    value="<?php echo htmlspecialchars($editData['DESCRIPTION'] ?? ''); ?>" required>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update Year Level</button>
                    <a href="year_level.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save Year Level</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search Year Level</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search year level..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="year_level.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>Year Level Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Year Level</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo htmlspecialchars($row['YEARLEVEL']); ?></td>
            <td><?php echo htmlspecialchars($row['DESCRIPTION']); ?></td>
            <td>
                <div class="actions">
                    <a href="year_level.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this year level?');">
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