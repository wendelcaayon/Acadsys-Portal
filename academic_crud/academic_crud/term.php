<?php
include "db.php";
require "auth.php";

if (isset($_POST['save'])) {
    $term = trim($_POST['term']);
    $description = trim($_POST['description']);

    if ($term != "" && $description != "") {
        $stmt = $conn->prepare("INSERT INTO tbl_term (TERM, DESCRIPTION) VALUES (?, ?)");
        $stmt->bind_param("ss", $term, $description);
        $stmt->execute();
        header("Location: term.php");
        exit();
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $term = trim($_POST['term']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE tbl_term SET TERM=?, DESCRIPTION=? WHERE ID=?");
    $stmt->bind_param("ssi", $term, $description, $id);
    $stmt->execute();
    header("Location: term.php");
    exit();
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tbl_term WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: term.php");
    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM tbl_term WHERE TERM LIKE ? OR DESCRIPTION LIKE ? ORDER BY ID DESC");
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_term ORDER BY ID DESC");
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_term WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Term Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>Term Management</h1>

    <h2><?php echo $editData ? "Update Term" : "Add Term"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Term</label>
                <input type="text" name="term" placeholder="Example: 1ST SEM"
                    value="<?php echo htmlspecialchars($editData['TERM'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="Term Description"
                    value="<?php echo htmlspecialchars($editData['DESCRIPTION'] ?? ''); ?>" required>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update Term</button>
                    <a href="term.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save Term</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search Term</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search term..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="term.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>Term Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Term</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo htmlspecialchars($row['TERM']); ?></td>
            <td><?php echo htmlspecialchars($row['DESCRIPTION']); ?></td>
            <td>
                <div class="actions">
                    <a href="term.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this term?');">
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