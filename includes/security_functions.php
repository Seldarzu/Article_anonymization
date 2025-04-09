<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_file($file) {
    $allowed_types = ['application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        return true;
    }
    return false;
}
?>