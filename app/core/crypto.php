<?php
// Encrypt sensitive data
function encryptData(string $data): string {
    $key = 'your_secret_key'; // change this to a proper secret
    $iv = substr(hash('sha256', 'your_iv'), 0, 16);
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

// Decrypt sensitive data
function decryptData(string $encryptedData): string {
    $key = 'your_secret_key';
    $iv = substr(hash('sha256', 'your_iv'), 0, 16);
    return openssl_decrypt($encryptedData, 'AES-256-CBC', $key, 0, $iv);
}
