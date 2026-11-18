<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'diarybook');
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Enkripsi Simetris (AES-256-CBC)
function enkripsi($teks, $kunci) {$iv = openssl_random_pseudo_bytes(16);
    $cipher = openssl_encrypt($teks, 'aes-256-cbc', hash('sha256', $kunci, true), 0,$iv);
    return [
        'cipher' => $cipher,
        'iv' => base64_encode($iv)
    ];
}

// Dekripsi Simetris (AES-256-CBC)
function dekripsi($cipher, $kunci,$ivBase64) {
    return openssl_decrypt($cipher, 'aes-256-cbc', hash('sha256', $kunci, true), 0, base64_decode($ivBase64));
}
?>