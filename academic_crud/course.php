<?php
include "db.php";
require "auth.php";

if (isset($_POST['save'])) {
    $course = trim($_POST['course']);
    $description = trim($_POST['description']);

    if ($course != "" && $description != "") {
        $stmt = $conn->prepare("INSERT INTO tbl_course (COURSE, DESCRIPTION) VALUES (?, ?)");
        $stmt->bind_param("ss", $course, $description);
        $stmt->execute();
        header("Location: course.php");
        exit();
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $course = trim($_POST['course']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE tbl_course SET COURSE=?, DESCRIPTION=? WHERE ID=?");
    $stmt->bind_param("ssi", $course, $description, $id);
    $stmt->execute();
    header("Location: course.php");
    exit();
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tbl_course WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: course.php");
    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM tbl_course WHERE COURSE LIKE ? OR DESCRIPTION LIKE ? ORDER BY ID DESC");
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_course ORDER BY ID DESC");
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_course WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>Course Management</h1>

    <h2><?php echo $editData ? "Update Course" : "Add Course"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Course</label>
                <input type="text" name="course" placeholder="Example: BSIS"
                    value="<?php echo htmlspecialchars($editData['COURSE'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="Course Description"
                    value="<?php echo htmlspecialchars($editData['DESCRIPTION'] ?? ''); ?>" required>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update Course</button>
                    <a href="course.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save Course</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search Course</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search course..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="course.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>Course Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Course</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo htmlspecialchars($row['COURSE']); ?></td>
            <td><?php echo htmlspecialchars($row['DESCRIPTION']); ?></td>
            <td>
                <div class="actions">
                    <a href="course.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this course?');">
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