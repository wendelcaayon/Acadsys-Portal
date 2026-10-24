<?php
require_once "access.php";
require_once "upload_image.php";

$currentPage = basename($_SERVER['PHP_SELF']);
$navUserType = $_SESSION['usertype'] ?? '';

// Version number for style.css so the browser never shows an old cached copy.
$cssVersion = @filemtime(__DIR__ . '/style.css') ?: time();

// Only draws links the current user type is allowed to open.
function navLink($href, $label, $currentPage, $navUserType) {
    if ($href !== "index.php" && !canAccess($navUserType, $href)) {
        return;
    }
    $activeClass = ($href === $currentPage) ? ' class="active"' : '';
    echo '<a href="' . $href . '"' . $activeClass . '>' . $label . '</a>';
}
?>
<link rel="stylesheet" href="style.css?v=<?php echo $cssVersion; ?>">

<nav class="topnav">
    <a href="index.php" class="brand">AcadSys <span>Student Information</span></a>

    <div class="topnav-links">
        <?php
        navLink("index.php", "Home", $currentPage, $navUserType);
        navLink("course.php", "Course", $currentPage, $navUserType);
        navLink("year_level.php", "Year Level", $currentPage, $navUserType);
        navLink("term.php", "Term", $currentPage, $navUserType);
        navLink("section.php", "Section", $currentPage, $navUserType);
        navLink("schoolyear.php", "School Year", $currentPage, $navUserType);
        navLink("subjects.php", "Subjects", $currentPage, $navUserType);
        navLink("instructors.php", "Instructors", $currentPage, $navUserType);
        navLink("users.php", "Users", $currentPage, $navUserType);
        navLink("usertype.php", "User Type", $currentPage, $navUserType);
        ?>
    </div>

    <?php if (!empty($_SESSION['username'])): ?>
    <div class="nav-user">
        <img class="avatar" width="36" height="36"
             src="<?php echo htmlspecialchars(userImage($_SESSION['username'])); ?>" alt="Profile photo">
        <div class="nav-user-text">
            <span class="nav-user-name"><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></span>
            <span class="nav-user-role"><?php echo htmlspecialchars($navUserType); ?></span>
        </div>
    </div>
    <?php endif; ?>
</nav>