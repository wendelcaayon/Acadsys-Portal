<?php
require "db.php";
$publicPage = true; // any logged-in user may open the home page
require "auth.php";

$usertype = $_SESSION['usertype'] ?? '';

$modules = [
    "course.php"      => "Course",
    "year_level.php"  => "Year Level",
    "term.php"        => "Term",
    "section.php"     => "Section",
    "schoolyear.php"  => "School Year",
    "subjects.php"    => "Subjects",
    "instructors.php" => "Instructors",
    "users.php"       => "Users",
    "usertype.php"    => "User Type",
];
$allowedModules = array_filter(
    $modules,
    fn($page) => canAccess($usertype, $page),
    ARRAY_FILTER_USE_KEY
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "topnav.php"; ?>
<div class="page-wrap">
<div class="container">
    <h1>Academic Management System</h1>

    <p>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?> (<?php echo htmlspecialchars($usertype); ?>).</p>

    <?php if ($allowedModules): ?>
        <p>Select a module below:</p>
        <div class="menu">
            <?php foreach ($allowedModules as $page => $label): ?>
                <a href="<?php echo $page; ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Your account does not have access to any modules.</p>
    <?php endif; ?>

    <?php include "bottom_bar.php"; ?>
</div>
</div>
</body>
</html>