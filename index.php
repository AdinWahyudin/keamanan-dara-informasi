<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiaryBook — Secure Cryptographic Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a 60%);
            min-height: 100vh;
            color: #f8fafc;
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
        }
        .navbar-custom {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hero-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }
        .feature-box {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .feature-box:hover {
            transform: translateY(-4px);
            border-color: rgba(56, 189, 248, 0.4);
        }
        .btn-glow {
            background: linear-gradient(90deg, #2563eb, #06b6d4);
            border: none;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.4);
        }
        .btn-glow:hover {
            background: linear-gradient(90deg, #1d4ed8, #0891b2);
            color: #fff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-custom py-3">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <i class="bi bi-shield-lock-fill text-info fs-4 me-2"></i>
            <span>Diary<span class="text-info">Book</span></span>
        </a>
        <a href="setup_kunci.php" class="btn btn-sm btn-outline-secondary text-light">
            <i class="bi bi-gear me-1"></i> Reset Kunci RSA
        </a>
    </div>
</nav>

<main class="container my-auto py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="hero-card p-4 p-md-5 text-center shadow-lg">
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
                    <i class="bi bi-cpu me-1"></i> Hybrid Cryptography Architecture
                </span>
                <h1 class="display-5 fw-bold mb-3">Sistem Informasi <span class="text-info">DiaryBook</span></h1>
                <p class="lead text-secondary mb-4 mx-auto" style="max-width: 650px;">
                    Platform penyimpanan catatan rahasia dengan paradigma autentikasi modern berbasis <strong>Kunci Simetris (AES-256)</strong> dan <strong>Kunci Asimetris (RSA)</strong> tanpa penyimpanan plaintext di database.
                </p>

                <div class="d-flex justify-content-center gap-3 mb-5">
                    <a href="login.php" class="btn btn-glow text-white btn-lg px-4 py-3 fw-semibold rounded-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Halaman Admin
                    </a>
                </div>

                <div class="row g-3 text-start">
                    <div class="col-md-4">
                        <div class="feature-box p-3 h-100">
                            <i class="bi bi-key text-info fs-4 mb-2 d-block"></i>
                            <h6 class="fw-bold text-white">Autentikasi Asimetris</h6>
                            <p class="small text-secondary mb-0">Verifikasi akses admin menggunakan tanda tangan digital RSA Private Key & Public Key.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box p-3 h-100">
                            <i class="bi bi-lock text-success fs-4 mb-2 d-block"></i>
                            <h6 class="fw-bold text-white">Enkripsi AES-256-CBC</h6>
                            <p class="small text-secondary mb-0">Seluruh konten catatan dienkripsi dengan Kunci Simetris sebelum disimpan ke database.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box p-3 h-100">
                            <i class="bi bi-database-lock text-warning fs-4 mb-2 d-block"></i>
                            <h6 class="fw-bold text-white">Zero-Plaintext Storage</h6>
                            <p class="small text-secondary mb-0">Database hanya menyimpan Ciphertext acak dan Initialization Vector (IV) unik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="text-center py-3 text-secondary small">
    Sistem Informasi Kriptografi &bull; DiaryBook PHP & MySQL
</footer>

</body>
</html>