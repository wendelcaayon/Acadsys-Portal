<?php
const USER_IMG_DIR = __DIR__ . "/images/users/";
const USER_IMG_URL = "images/users/";

// Only letters, numbers, underscore and hyphen are allowed in a photo file name.
function cleanUsername($username) {
    return preg_replace('/[^A-Za-z0-9_-]/', '', (string)$username);
}

// Makes sure the folder exists and that scripts can never run inside it.
function ensureUserImageDir() {
    if (!is_dir(USER_IMG_DIR)) {
        mkdir(USER_IMG_DIR, 0755, true);
    }
    $ht = USER_IMG_DIR . ".htaccess";
    if (!file_exists($ht)) {
        file_put_contents($ht,
            "Options -ExecCGI\n" .
            "<FilesMatch \"\\.(php|phtml|phar|php[0-9])$\">\n    Require all denied\n</FilesMatch>\n");
    }
}

function uploadUserImage($username, $fileInput = 'imageUpload') {
    $result = ['success' => false, 'msg' => '', 'path' => ''];

    // Image is optional
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] === UPLOAD_ERR_NO_FILE) {
        $result['msg'] = 'No image uploaded.';
        return $result;
    }
    if ($_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
        $result['msg'] = 'Upload error (file may be larger than the server limit).';
        return $result;
    }

    $safeName = cleanUsername($username);
    if ($safeName === '') {
        $result['msg'] = 'Invalid username for a photo file name.';
        return $result;
    }

    // Check it's really an image
    $tmp  = $_FILES[$fileInput]['tmp_name'];
    $info = @getimagesize($tmp);
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if ($info === false || !in_array($info['mime'], $allowedMime, true)) {
        $result['msg'] = 'Invalid file type. Use JPG, PNG, GIF or WEBP.';
        return $result;
    }
    if ($_FILES[$fileInput]['size'] > 2 * 1024 * 1024) {
        $result['msg'] = 'Image is too large (max 2MB).';
        return $result;
    }

    ensureUserImageDir();

    if (move_uploaded_file($tmp, USER_IMG_DIR . $safeName . ".JPG")) {
        $result['success'] = true;
        $result['path']    = USER_IMG_URL . $safeName . ".JPG";
        $result['msg']     = 'The image was uploaded successfully.';
    } else {
        $result['msg'] = 'Sorry, there was an error uploading your file.';
    }
    return $result;
}

// URL of a user's photo. Falls back to default.JPG, then to a built-in placeholder.
function userImage($username) {
    $safeName = cleanUsername($username);
    $file = USER_IMG_DIR . $safeName . ".JPG";
    if ($safeName !== '' && is_file($file)) {
        return USER_IMG_URL . $safeName . ".JPG?v=" . filemtime($file);
    }
    if (is_file(USER_IMG_DIR . "default.JPG")) {
        return USER_IMG_URL . "default.JPG";
    }
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'>"
         . "<rect width='40' height='40' fill='#6c757d'/>"
         . "<circle cx='20' cy='15' r='7' fill='#fff'/>"
         . "<path d='M6 40c0-9 6-14 14-14s14 5 14 14z' fill='#fff'/></svg>";
    return "data:image/svg+xml;utf8," . rawurlencode($svg);
}

function deleteUserImage($username) {
    $safeName = cleanUsername($username);
    $file = USER_IMG_DIR . $safeName . ".JPG";
    if ($safeName !== '' && is_file($file)) {
        unlink($file);
    }
}

// Moves the photo when a username is changed.
function renameUserImage($oldUsername, $newUsername) {
    $old = cleanUsername($oldUsername);
    $new = cleanUsername($newUsername);
    if ($old === '' || $new === '' || $old === $new) return;
    if (is_file(USER_IMG_DIR . $old . ".JPG")) {
        rename(USER_IMG_DIR . $old . ".JPG", USER_IMG_DIR . $new . ".JPG");
    }
}

function validUsername($username) {
    return (bool)preg_match('/^[A-Za-z0-9_-]{3,50}$/', $username);
}