<?php
include "db.php";
require "auth.php";

if (isset($_POST['save'])) {
    $section = trim($_POST['section']);
    $description = trim($_POST['description']);

    if ($section != "" && $description != "") {
        $stmt = $conn->prepare("INSERT INTO tbl_section (SECTION, DESCRIPTION) VALUES (?, ?)");
        $stmt->bind_param("ss", $section, $description);
        $stmt->execute();
        header("Location: section.php");
        exit();
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $section = trim($_POST['section']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE tbl_section SET SECTION=?, DESCRIPTION=? WHERE ID=?");
    $stmt->bind_param("ssi", $section, $description, $id);
    $stmt->execute();
    header("Location: section.php");
    exit();
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tbl_section WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: section.php");
    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM tbl_section WHERE SECTION LIKE ? OR DESCRIPTION LIKE ? ORDER BY ID DESC");
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_section ORDER BY ID DESC");
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_section WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Section Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>Section Management</h1>

    <h2><?php echo $editData ? "Update Section" : "Add Section"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Section</label>
                <input type="text" name="section" placeholder="Example: A"
                    value="<?php echo htmlspecialchars($editData['SECTION'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="Section Description"
                    value="<?php echo htmlspecialchars($editData['DESCRIPTION'] ?? ''); ?>" required>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update Section</button>
                    <a href="section.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save Section</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search Section</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search section..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="section.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>Section Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Section</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo htmlspecialchars($row['SECTION']); ?></td>
            <td><?php echo htmlspecialchars($row['DESCRIPTION']); ?></td>
            <td>
                <div class="actions">
                    <a href="section.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this section?');">
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