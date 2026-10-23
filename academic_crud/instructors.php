<?php
include "db.php";
require "auth.php";

$error = "";

// One-time message carried across the redirect (e.g. photo problem after saving)
$flash = $_SESSION['flash'] ?? "";
unset($_SESSION['flash']);

$uploadDir = __DIR__ . "/uploads/instructors";
$uploadUrl = "uploads/instructors";
$salutations = ["Mr.", "Ms.", "Mrs.", "Dr.", "Engr.", "Prof."];

// Make sure the upload folder exists and can never run scripts.
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$htaccess = $uploadDir . "/.htaccess";
if (!file_exists($htaccess)) {
    file_put_contents($htaccess,
        "Options -ExecCGI\n" .
        "<FilesMatch \"\\.(php|phtml|phar|php[0-9])$\">\n    Require all denied\n</FilesMatch>\n");
}

// Step 1: checks an uploaded photo. Returns the file extension to use,
// or null when nothing was uploaded / the upload was rejected ($err is set).
function checkPhoto(array $file, string &$err): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $err = "Photo upload failed. Please try again.";
        return null;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $err = "Photo must be 2 MB or smaller.";
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        $err = "Photo must be a JPG, PNG, or WEBP image.";
        return null;
    }
    return $allowed[$mime];
}

// Step 2: stores the photo as instructor_<ID>.<ext> so you can tell whose photo it is.
function storePhoto(array $file, string $ext, int $id, string $uploadDir): ?string {
    $name = "instructor_" . $id . "." . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . "/" . $name)) {
        return null;
    }
    return $name;
}

function deletePhotoFile(?string $name, string $uploadDir): void {
    if ($name) {
        $path = $uploadDir . "/" . basename($name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}

// URL of a stored photo, or null when there is no photo or the file is missing.
// The ?v= part makes the browser reload the picture after it is replaced.
function photoUrl(?string $name, string $uploadDir, string $uploadUrl): ?string {
    if (!$name) {
        return null;
    }
    $path = $uploadDir . "/" . basename($name);
    if (!is_file($path)) {
        return null;
    }
    return $uploadUrl . "/" . rawurlencode(basename($name)) . "?v=" . filemtime($path);
}

if (isset($_POST['save'])) {
    $salutation = trim($_POST['salutation']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);

    if (!in_array($salutation, $salutations, true) || $firstname === "" || $lastname === "") {
        $error = "Please fill in the salutation, first name, and last name.";
    } else {
        $ext = checkPhoto($_FILES['photo'] ?? [], $error);
        if ($error === "") {
            $stmt = $conn->prepare("INSERT INTO tbl_instructors (SALUTATION, FIRSTNAME, MIDDLENAME, LASTNAME) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $salutation, $firstname, $middlename, $lastname);
            $stmt->execute();
            $newId = (int)$conn->insert_id;

            if ($ext !== null) {
                $photo = storePhoto($_FILES['photo'], $ext, $newId, $uploadDir);
                if ($photo !== null) {
                    $stmt = $conn->prepare("UPDATE tbl_instructors SET PHOTO=? WHERE ID=?");
                    $stmt->bind_param("si", $photo, $newId);
                    $stmt->execute();
                } else {
                    $_SESSION['flash'] = "Instructor saved, but the photo could not be saved.";
                }
            }
            header("Location: instructors.php");
            exit();
        }
    }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $salutation = trim($_POST['salutation']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);

    if (!in_array($salutation, $salutations, true) || $firstname === "" || $lastname === "") {
        $error = "Please fill in the salutation, first name, and last name.";
    } else {
        $stmt = $conn->prepare("SELECT PHOTO FROM tbl_instructors WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();

        if ($current) {
            $ext = checkPhoto($_FILES['photo'] ?? [], $error);
            if ($error === "") {
                if ($ext !== null) {
                    $newPhoto = storePhoto($_FILES['photo'], $ext, $id, $uploadDir);
                    if ($newPhoto === null) {
                        $error = "Could not save the photo.";
                    } else {
                        $stmt = $conn->prepare("UPDATE tbl_instructors SET SALUTATION=?, FIRSTNAME=?, MIDDLENAME=?, LASTNAME=?, PHOTO=? WHERE ID=?");
                        $stmt->bind_param("sssssi", $salutation, $firstname, $middlename, $lastname, $newPhoto, $id);
                        $stmt->execute();
                        // Remove the previous file unless it was just overwritten (same name).
                        if ($current['PHOTO'] && $current['PHOTO'] !== $newPhoto) {
                            deletePhotoFile($current['PHOTO'], $uploadDir);
                        }
                        header("Location: instructors.php");
                        exit();
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE tbl_instructors SET SALUTATION=?, FIRSTNAME=?, MIDDLENAME=?, LASTNAME=? WHERE ID=?");
                    $stmt->bind_param("ssssi", $salutation, $firstname, $middlename, $lastname, $id);
                    $stmt->execute();
                    header("Location: instructors.php");
                    exit();
                }
            }
        }
    }
}

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("SELECT PHOTO FROM tbl_instructors WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM tbl_instructors WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($current) {
        deletePhotoFile($current['PHOTO'], $uploadDir);
    }
    header("Location: instructors.php");
    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt = $conn->prepare(
        "SELECT * FROM tbl_instructors
         WHERE SALUTATION LIKE ? OR FIRSTNAME LIKE ? OR MIDDLENAME LIKE ? OR LASTNAME LIKE ?
         ORDER BY ID DESC"
    );
    $stmt->bind_param("ssss", $keyword, $keyword, $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM tbl_instructors ORDER BY ID DESC");
}

$editData = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tbl_instructors WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

// Keep what was typed when validation fails.
$form = [
    'SALUTATION' => $_POST['salutation'] ?? ($editData['SALUTATION'] ?? ''),
    'FIRSTNAME'  => $_POST['firstname']  ?? ($editData['FIRSTNAME'] ?? ''),
    'MIDDLENAME' => $_POST['middlename'] ?? ($editData['MIDDLENAME'] ?? ''),
    'LASTNAME'   => $_POST['lastname']   ?? ($editData['LASTNAME'] ?? ''),
];
$isUpdate = $editData || isset($_POST['update']);
$formId = $_POST['id'] ?? ($editData['ID'] ?? '');
$editPhotoUrl = $editData ? photoUrl($editData['PHOTO'] ?? null, $uploadDir, $uploadUrl) : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .photo-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 50%; border: 1px solid var(--border); }
        .photo-none { color: var(--muted); font-size: 12px; }
    </style>
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>Instructor Management</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($flash): ?>
        <p class="error"><?php echo htmlspecialchars($flash); ?></p>
    <?php endif; ?>

    <h2><?php echo $isUpdate ? "Update Instructor" : "Add Instructor"; ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$formId); ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Salutation</label>
                <select name="salutation" required>
                    <option value="">-- Select Salutation --</option>
                    <?php foreach ($salutations as $s): ?>
                        <option value="<?php echo $s; ?>" <?php if ($form['SALUTATION'] === $s) echo "selected"; ?>>
                            <?php echo $s; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="firstname" maxlength="100" placeholder="First Name"
                    value="<?php echo htmlspecialchars($form['FIRSTNAME']); ?>" required>
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="middlename" maxlength="100" placeholder="Middle Name (optional)"
                    value="<?php echo htmlspecialchars($form['MIDDLENAME']); ?>">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lastname" maxlength="100" placeholder="Last Name"
                    value="<?php echo htmlspecialchars($form['LASTNAME']); ?>" required>
            </div>
            <div class="form-group full">
                <label>Photo (JPG, PNG or WEBP, max 2 MB)</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                <?php if ($editPhotoUrl): ?>
                    <p class="login-sub" style="text-align:left;margin:8px 0 0;">
                        Current photo:
                        <img class="photo-thumb" src="<?php echo htmlspecialchars($editPhotoUrl); ?>" alt="Current photo">
                        &mdash; choose a new file only if you want to replace it.
                    </p>
                <?php endif; ?>
            </div>
            <div class="full actions">
                <?php if ($isUpdate): ?>
                    <button type="submit" name="update">Update Instructor</button>
                    <a href="instructors.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="save">Save Instructor</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <hr>

    <h2>Search Instructors</h2>
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search name..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <a href="instructors.php" class="btn btn-secondary">Clear</a>
    </form>

    <h2>Instructor Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Salutation</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <?php $thumb = photoUrl($row['PHOTO'] ?? null, $uploadDir, $uploadUrl); ?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td>
                <?php if ($thumb): ?>
                    <img class="photo-thumb" src="<?php echo htmlspecialchars($thumb); ?>" alt="">
                <?php else: ?>
                    <span class="photo-none">No photo</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['SALUTATION']); ?></td>
            <td><?php echo htmlspecialchars($row['FIRSTNAME']); ?></td>
            <td><?php echo htmlspecialchars($row['MIDDLENAME']); ?></td>
            <td><?php echo htmlspecialchars($row['LASTNAME']); ?></td>
            <td>
                <div class="actions">
                    <a href="instructors.php?edit=<?php echo $row['ID']; ?>" class="btn">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this instructor?');">
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