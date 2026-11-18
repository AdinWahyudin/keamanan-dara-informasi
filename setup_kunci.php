<?php
require 'config.php';

$resetToken = $_SESSION['setup_reset_token'] ?? bin2hex(random_bytes(32));
$_SESSION['setup_reset_token'] = $resetToken;
$resetSubmitted = $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'reset';
$resetRequested = $resetSubmitted
    && isset($_POST['setup_reset_token'])
    && is_string($_POST['setup_reset_token'])
    && hash_equals($resetToken, $_POST['setup_reset_token']);

$stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
if (!$stmt || !$stmt->execute()) {
    die("Gagal memeriksa kunci admin di database: " . htmlspecialchars($conn->error));
}
$adminExists = (bool)$stmt->get_result()->fetch_assoc();
$stmt->close();

$buatKunciBaru = !$adminExists
    || $resetRequested;
$kunci_simetris = '';
$privKey = '';
$pesanError = $resetSubmitted && !$resetRequested
    ? "Permintaan reset tidak valid. Muat ulang halaman lalu coba lagi."
    : '';

if ($buatKunciBaru) {
    $config = [
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    $opensslCnf = str_replace(
        DIRECTORY_SEPARATOR,
        '/',
        dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'extras'
            . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf'
    );
    if (!file_exists($opensslCnf)) {
        $cnfPaths = glob('C:/laragon/bin/php/php-' . PHP_VERSION . '*/extras/ssl/openssl.cnf');
        $opensslCnf = $cnfPaths ? $cnfPaths[0] : '';
    }
    if (file_exists($opensslCnf)) {
        $config["config"] = $opensslCnf;
        putenv('OPENSSL_CONF=' . $opensslCnf);
    }

    $res = openssl_pkey_new($config);
    $details = $res ? openssl_pkey_get_details($res) : false;
    if (!$res || !openssl_pkey_export($res, $privKey, null, $config) || !$details || empty($details['key'])) {
        $opensslError = openssl_error_string();
        $pesanError = "Kunci gagal dibuat. Pastikan ekstensi php_openssl aktif di Laragon."
            . ($opensslError ? " OpenSSL: " . $opensslError : "");
    } else {
        $kunciSimetrisBaru = bin2hex(random_bytes(32));
        $resetTokenBerikutnya = bin2hex(random_bytes(32));
        $conn->begin_transaction();
        $delete = $conn->prepare("DELETE FROM users WHERE username = 'admin'");
        $insert = $conn->prepare("INSERT INTO users (username, public_key) VALUES ('admin', ?)");
        if (!$delete || !$delete->execute() || !$insert) {
            $conn->rollback();
            $pesanError = "Kunci gagal disimpan ke database: " . $conn->error;
        } else {
            $pubKey = $details['key'];
            $insert->bind_param("s", $pubKey);
            if (!$insert->execute() || !$conn->commit()) {
                $conn->rollback();
                $pesanError = "Kunci gagal disimpan ke database: " . $conn->error;
                $privKey = '';
            } else {
                $kunci_simetris = $kunciSimetrisBaru;
                $adminExists = true;
                $_SESSION['setup_reset_token'] = $resetTokenBerikutnya;
            }
        }
        if ($delete) {
            $delete->close();
        }
        if ($insert) {
            $insert->close();
        }
    }
}
$kunciBaruTersedia = $kunci_simetris !== '' && $privKey !== '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Kunci Kriptografi - DiaryBook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: #e2e8f0;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .card-custom {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
        }
        .key-box {
            background: #0f172a;
            color: #38bdf8;
            border: 1px solid #334155;
            font-family: 'Consolas', monospace;
            font-size: 12px;
        }
        .sym-box {
            background: #0f172a;
            color: #facc15;
            border: 1px solid #334155;
            font-family: 'Consolas', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-md-5 shadow-lg">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-25 text-success p-3 rounded-circle me-3">
                        <i class="bi bi-key-fill fs-3"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-white"><?= $kunciBaruTersedia ? 'Kunci Baru Siap!' : 'Pengaturan Kunci Admin' ?></h3>
                        <small class="text-secondary">Public Key disimpan di database <code>diarybook</code></small>
                    </div>
                </div>

                <?php if ($pesanError): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($pesanError) ?></div>
                <?php endif; ?>

                <?php if ($kunciBaruTersedia): ?>
                <div class="alert alert-warning bg-warning bg-opacity-10 border-warning text-warning small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Simpan kedua kunci ini sekarang.</strong> Private Key tidak disimpan di server dan tidak bisa ditampilkan kembali. Refresh halaman tidak akan mengganti kunci.
                </div>

                <!-- 1. KUNCI SIMETRIS -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold text-warning mb-0">
                            <i class="bi bi-lock-fill me-1"></i> 1. Kunci Simetris (AES-256 Secret Key)
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="copyText('symKeyBox', 'Status Kunci Simetris disalin!')">
                            <i class="bi bi-clipboard me-1"></i> Salin Kunci Simetris
                        </button>
                    </div>
                    <input type="text" id="symKeyBox" class="form-control sym-box py-2" value="<?= htmlspecialchars($kunci_simetris) ?>" readonly>
                </div>

                <!-- 2. KUNCI ASIMETRIS -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold text-info mb-0">
                            <i class="bi bi-file-earmark-code-fill me-1"></i> 2. Kunci Asimetris (RSA Private Key PEM)
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="copyText('privKeyBox', 'Status Kunci Asimetris disalin!')">
                            <i class="bi bi-clipboard me-1"></i> Salin Kunci Asimetris
                        </button>
                    </div>
                    <textarea id="privKeyBox" class="form-control key-box" rows="11" readonly><?= htmlspecialchars($privKey) ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span id="copyStatus" class="text-success small fw-semibold"></span>
                    <a href="index.php" class="btn btn-primary px-4 py-2 fw-semibold">
                        Lanjut ke Landing Page <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <?php elseif ($adminExists): ?>
                <div class="alert alert-info bg-info bg-opacity-10 border-info text-info small">
                    Kunci admin sudah terdaftar. Membuka atau me-refresh halaman ini tidak mengubah kunci. Gunakan private key yang disimpan sebelumnya untuk login.
                </div>
                <div class="alert alert-warning bg-warning bg-opacity-10 border-warning text-warning small">
                    Reset hanya jika private key lama hilang atau tidak cocok. Reset mengganti kunci RSA; catatan lama tetap ada, tetapi untuk membacanya Anda tetap memerlukan kunci simetris lama.
                </div>
                <form method="POST" onsubmit="return confirm('Buat pasangan kunci baru? Private key lama tidak akan bisa digunakan lagi.');">
                    <input type="hidden" name="action" value="reset">
                    <input type="hidden" name="setup_reset_token" value="<?= htmlspecialchars($resetToken) ?>">
                    <button type="submit" class="btn btn-warning fw-semibold">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset dan Buat Kunci Baru
                    </button>
                    <a href="login.php" class="btn btn-outline-light ms-2">Kembali ke Login</a>
                </form>
                <?php else: ?>
                <div class="alert alert-danger">
                    Setup kunci belum berhasil. Periksa database dan ekstensi OpenSSL, lalu muat ulang halaman ini untuk mencoba lagi.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function copyText(elementId, msg) {
    const el = document.getElementById(elementId);
    el.select();
    navigator.clipboard.writeText(el.value);
    document.getElementById('copyStatus').innerText = '✓ ' + msg;
}
</script>
</body>
</html>