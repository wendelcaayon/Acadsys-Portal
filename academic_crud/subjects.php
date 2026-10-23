<?php
include "db.php";
require "auth.php";

// Students may view subjects but not change them.
$canEdit = ($_SESSION['usertype'] ?? '') !== 'Student';

if ($canEdit && isset($_POST['save'])) {
    $course = trim($_POST['course']);
    $yearlevel = trim($_POST['yearlevel']);
    $term = trim($_POST['term']);
    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $lecture = (float)$_POST['lecture'];
    $laboratory = (float)$_POST['laboratory'];
    $credit = (float)$_POST['credit'];
    $grade = (float)$_POST['grade'];
    $pre_requisite = trim($_POST['pre_requisite']);

    $stmt = $conn->prepare(
        "INSERT INTO tbl_subjects (COURSE, YEARLEVEL, TERM, CODE, TITLE, LECTURE, LABORATORY, CREDIT, GRADE, PRE_REQUISITE)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssdddds", $course, $yearlevel, $term, $code, $title, $lecture, $laboratory, $credit, $grade, $pre_requisite);
    $stmt->execute();
    header("Location: subjects.php");
    exit();
}

if ($canEdit && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $course = trim($_POST['course']);
    $yearlevel = trim($_POST['yearlevel']);
    $term = trim($_POST['term']);
    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $lecture = (float)$_POST['lecture'];
    $laboratory = (float)$_POST['laboratory'];
    $credit = (float)$_POST['credit'];
    $grade = (float)$_POST['grade'];
    $pre_requisite = trim($_POST['pre_requisite']);

    $stmt = $conn->prepare(
        "UPDATE tbl_subjects SET
            COURSE=?, YEARLEVEL=?, TERM=?, CODE=?, TITLE=?,
            LECTURE=?, LABORATORY=?, CREDIT=?, GRADE=?, PRE_REQUISITE=?
         WHERE ID=?"
    );
    $stmt->bind_param("sssssddddsi", $course, $yearlevel, $term, $code, $title, $lecture, $laboratory, $credit, $grade, $pre_requisite, $id);
    $stmt->execute();
    header("Location: subjects.php");
    exit();
}

if ($canEdit && isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM tbl_subjects WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: subjects.php");
    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare(
        "SELECT * FROM tbl_subjects
         WHERE COURSE LIKE ? OR YEARLEVEL LIKE ? OR TERM LIKE ? OR CODE LIKE ? OR TITLE LIKE ? OR PRE_REQUISITE LIKE ?
         ORDER BY ID DESC"
    );
    $stmt->bind_param("ssssss", $keyword, $keyword, $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_subjects ORDER BY ID DESC");
}

$editData = null;

if ($canEdit && isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_subjects WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

if ($canEdit) {
    $courses = $conn->query("SELECT * FROM tbl_course ORDER BY COURSE ASC");
    $yearlevels = $conn->query("SELECT * FROM tbl_yearlevel ORDER BY ID ASC");
    $terms = $conn->query("SELECT * FROM tbl_term ORDER BY ID ASC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subjects Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1><?php echo $canEdit ? "Subjects Management" : "Subjects"; ?></h1>

    <?php if ($canEdit): ?>
    <h2><?php echo $editData ? "Update Subject" : "Add Subject"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editData['ID'] ?? ''; ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Course</label>
                <select name="course" required>
                    <option value="">-- Select Course --</option>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($course['COURSE']); ?>"
                            <?php if (isset($editData['COURSE']) && $editData['COURSE'] == $course['COURSE']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($course['COURSE']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select name="yearlevel" required>
                    <option value="">-- Select Year Level --</option>
                    <?php while ($year = $yearlevels->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($year['YEARLEVEL']); ?>"
                            <?php if (isset($editData['YEARLEVEL']) && $editData['YEARLEVEL'] == $year['YEARLEVEL']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($year['YEARLEVEL']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Term</label>
                <select name="term" required>
                    <option value="">-- Select Term --</option>
                    <?php while ($term = $terms->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($term['TERM']); ?>"
                            <?php if (isset($editData['TERM']) && $editData['TERM'] == $term['TERM']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($term['TERM']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" name="code" placeholder="Example: IT101"
                    value="<?php echo htmlspecialchars($editData['CODE'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Subject Title</label>
                <input type="text" name="title" placeholder="Subject Title"
                    value="<?php echo htmlspecialchars($editData['TITLE'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Lecture</label>
                <input type="number" step="0.01" name="lecture" placeholder="0.00"
                    value="<?php echo htmlspecialchars($editData['LECTURE'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Laboratory</label>
                <input type="number" step="0.01" name="laboratory" placeholder="0.00"
                    value="<?php echo htmlspecialchars($editData['LABORATORY'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Credit</label>
                <input type="number" step="0.01" name="credit" placeholder="0.00"
                    value="<?php echo htmlspecialchars($editData['CREDIT'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Grade</label>
                <input type="number" step="0.01" name="grade" placeholder="0.00"
                    value="<?php echo htmlspecialchars($editData['GRADE'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Pre-Requisite</label>
                <input type="text" name="pre_requisite" placeholder="Example: NONE"
                    value="<?php echo htmlspecialchars($editData['PRE_REQUISITE'] ?? ''); ?>" required>
            </div>
            <div class="full actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update">Update Subject</button>
                    <a href="subjects.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save Subject</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>
    <?php endif; ?>

    <h2>Search Subjects</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search code, title, course..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="subjects.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>Subject Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Course</th>
            <th>Year Level</th>
            <th>Term</th>
            <th>Code</th>
            <th>Title</th>
            <th>Lecture</th>
            <th>Laboratory</th>
            <th>Credit</th>
            <th>Grade</th>
            <th>Pre-Requisite</th>
            <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo htmlspecialchars($row['COURSE']); ?></td>
            <td><?php echo htmlspecialchars($row['YEARLEVEL']); ?></td>
            <td><?php echo htmlspecialchars($row['TERM']); ?></td>
            <td><?php echo htmlspecialchars($row['CODE']); ?></td>
            <td><?php echo htmlspecialchars($row['TITLE']); ?></td>
            <td><?php echo $row['LECTURE']; ?></td>
            <td><?php echo $row['LABORATORY']; ?></td>
            <td><?php echo $row['CREDIT']; ?></td>
            <td><?php echo $row['GRADE']; ?></td>
            <td><?php echo htmlspecialchars($row['PRE_REQUISITE']); ?></td>
            <?php if ($canEdit): ?>
            <td>
                <div class="actions">
                    <a href="subjects.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this subject?');">
                        <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                        <button type="submit" name="delete" class="btn-danger">Delete</button>
                    </form>
                </div>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php include "bottom_bar.php"; ?>
</div>
</div>
</body>
</html>