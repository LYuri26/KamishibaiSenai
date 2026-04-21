<?php
// utils/encryption.php
// Chave fixa (não use esta mesma em produção – altere para uma string única e guarde com segurança)
define('CRYPTO_KEY', 'k4m1sh1b41_s3cr3t_k3y_2025');
define('CRYPTO_METHOD', 'AES-256-CBC');

function encryptEmail($email)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CRYPTO_METHOD));
    $encrypted = openssl_encrypt($email, CRYPTO_METHOD, CRYPTO_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptEmail($ciphertext)
{
    $data = base64_decode($ciphertext);
    $iv_length = openssl_cipher_iv_length(CRYPTO_METHOD);
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, CRYPTO_METHOD, CRYPTO_KEY, 0, $iv);
}
?>