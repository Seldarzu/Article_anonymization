<?php
define("ENCRYPTION_KEY", "This_is_a_32_byte_key_AES256__!!");
define("ENCRYPTION_METHOD", "AES-256-CBC");

function encrypt_yorum($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . '::' . $encrypted);
}

function decrypt_yorum($data) {
    $decoded = base64_decode($data);
    if (!$decoded || strpos($decoded, '::') === false) return null;
    list($iv, $encrypted_data) = explode('::', $decoded, 2);
    return openssl_decrypt($encrypted_data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}
?>