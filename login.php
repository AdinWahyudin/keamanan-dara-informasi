<?php
require 'config.php';
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kunci_simetris = trim($_POST['simetris']);
    $raw_priv_key   = trim($_POST['asimetris']);

    // Bersihkan header/footer & rapikan ulang format PEM 64 karakter per baris
    $cleanBody = preg_replace('/-----BEGIN.*?-----|-----END.*?-----|\s+/', '', $raw_priv_key);
    $formattedPrivKey = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($cleanBody, 64, "\n") . "-----END PRIVATE KEY-----\n";

    $res = $conn->query("SELECT public_key FROM users WHERE username='admin'");
    $row = $res ? $res->fetch_assoc() : null;

    if (!$row) {
        $pesan = "Public Key admin belum ada di database. Buka setup_kunci.php terlebih dahulu!";
    } else {
        $pubKey  = openssl_pkey_get_public($row['public_key']);
        $privKey = openssl_pkey_get_private($formattedPrivKey);

        // Jika gagal sebagai PKCS#8 (BEGIN PRIVATE KEY), coba sebagai PKCS#1 (BEGIN RSA PRIVATE KEY)
        if (!$privKey) {
            $altPrivKey = "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split($cleanBody, 64, "\n") . "-----END RSA PRIVATE KEY-----\n";
            $privKey = openssl_pkey_get_private($altPrivKey);
        }

        if (!$privKey) {
            $pesan = "Private Key tetap gagal dibaca oleh PHP OpenSSL (" . openssl_error_string() . "). Pastikan PHP OpenSSL aktif.";
        } else {
            // Uji kecocokan pasangan kunci: Enkripsi pakai Public Key DB -> Dekripsi pakai Private Key input
            $teksUji = "VERIFIKASI_ADMIN_DIARYBOOK";
            $encryptedTest = '';
            $decryptedTest = '';

            $encOk = openssl_public_encrypt($teksUji, $encryptedTest, $pubKey);
            $decOk = $encOk && openssl_private_decrypt($encryptedTest, $decryptedTest, $privKey);

            if ($decOk && $decryptedTest === $teksUji) {
                $_SESSION['login'] = true;
                $_SESSION['kunci_simetris'] = $kunci_simetris;
                header("Location: admin.php");
                exit;
            } else {
                $pesan = "Kunci Asimetris (Private Key) tidak cocok dengan Public Key di database! Jalankan ulang setup_kunci.php.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Key-Based Login — DiaryBook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: #f8fafc;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
        }
        .form-control-dark {
            background: #0f172a;
            border: 1px solid #334155;
            color: #f8fafc;
        }
        .form-control-dark:focus {
            background: #0f172a;
            border-color: #38bdf8;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.15);
        }
        textarea.key-input {
            font-family: 'Consolas', monospace;
            font-size: 12px;
            color: #38bdf8;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="login-card p-4 p-md-5 shadow-lg">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-circle p-3 mb-3">
                        <i class="bi bi-shield-key fs-2"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Autentikasi Key-Based Admin</h3>
                    <p class="text-secondary small">Masukkan kombinasi Kunci Simetris & Kunci Asimetris untuk membuka brankas data</p>
                </div>

                <?php if ($pesan): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger small">
                        <i class="bi bi-x-circle-fill me-1"></i> <?= htmlspecialchars($pesan) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-light">
                            <i class="bi bi-lock-fill text-warning me-1"></i> 1. Kunci Simetris (AES-256 Secret Key)
                        </label>
                        <input type="text" name="simetris" class="form-control form-control-dark py-2" required placeholder="Tempel Kunci Simetris Anda di sini...">
                        <div class="form-text text-secondary" style="font-size: 11px;">Digunakan untuk mengenkripsi catatan baru & mendekripsi catatan yang tersimpan.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-light">
                            <i class="bi bi-file-earmark-code-fill text-info me-1"></i> 2. Kunci Asimetris (RSA Private Key PEM)
                        </label>
                        <textarea name="asimetris" rows="8" class="form-control form-control-dark key-input" required placeholder="-----BEGIN PRIVATE KEY-----&#10;Tempel kunci privat RSA Anda di sini...&#10;-----END PRIVATE KEY-----"></textarea>
                    </div>

                    <button type="submit" class="btn btn-info w-100 py-2 fw-bold text-dark">
                        <i class="bi bi-unlock-fill me-1"></i> Verifikasi Kunci & Masuk Admin
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <a href="index.php" class="text-secondary text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Landing Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>