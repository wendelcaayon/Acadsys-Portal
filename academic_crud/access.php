<?php
// Single source of truth for who may open which page.
// '*' means every page. Page names are the script file names.
const PAGE_ACCESS = [
    'Admin'      => ['*'],
    'Registrar'  => ['subjects.php', 'term.php', 'instructors.php', 'schoolyear.php'],
    'Dean'       => ['subjects.php', 'section.php', 'instructors.php', 'year_level.php'],
    'Instructor' => ['subjects.php', 'section.php'],
    'Student'    => ['subjects.php'],
];

function canAccess(string $usertype, string $page): bool {
    $allowed = PAGE_ACCESS[$usertype] ?? [];
    return in_array('*', $allowed, true) || in_array($page, $allowed, true);
}