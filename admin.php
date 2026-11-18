<?php
require 'config.php';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Hapus data jika diperlukan
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM encrypted_data WHERE id = $id");
    header("Location: admin.php");
    exit;
}

// Proses Simpan & Enkripsi Data Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasil = enkripsi($_POST['isi'], $_SESSION['kunci_simetris']);
    $stmt = $conn->prepare("INSERT INTO encrypted_data (title, ciphertext, iv) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['judul'], $hasil['cipher'], $hasil['iv']);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

$data = $conn->query("SELECT * FROM encrypted_data ORDER BY id DESC");
$total_data = $data->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — DiaryBook Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .topbar {
            background: #0f172a;
            color: white;
        }
        .card-panel {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
        }
        .cipher-pill {
            font-family: 'Consolas', monospace;
            font-size: 11.5px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px;
            border-radius: 6px;
            word-break: break-all;
            color: #475569;
            max-height: 85px;
            overflow-y: auto;
        }
        .iv-pill {
            font-family: 'Consolas', monospace;
            font-size: 11.5px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 6px 8px;
            border-radius: 6px;
            word-break: break-all;
            color: #1d4ed8;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark topbar py-3 shadow-sm mb-4">
    <div class="container">
        <span class="navbar-brand fw-bold d-flex align-items-center">
            <i class="bi bi-shield-lock-fill text-info fs-4 me-2"></i>
            DiaryBook <span class="badge bg-info text-dark ms-2 fs-6">Admin Vault</span>
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-light small d-none d-md-inline">
                <i class="bi bi-check-circle-fill text-success me-1"></i> Sesi Kunci Aktif
            </span>
            <a href="?logout=1" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right me-1"></i> Keluar & Kunci Sesi
            </a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <!-- Info Ringkas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-panel p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                        <i class="bi bi-archive-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Data Terenkripsi</div>
                        <h4 class="fw-bold mb-0"><?= $total_data ?> Catatan</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-panel p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Algoritma Penyimpanan</div>
                        <h6 class="fw-bold mb-0">AES-256-CBC (Simetris)</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-panel p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                        <i class="bi bi-key-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Autentikasi Akses</div>
                        <h6 class="fw-bold mb-0">RSA Signature (Asimetris)</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Tambah Catatan -->
    <div class="card card-panel p-4 bg-white mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Catatan Baru (Auto-Encrypt)</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Judul Catatan</label>
                <input type="text" name="judul" class="form-control" placeholder="Contoh: PIN ATM / Catatan Rapat" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Konten Rahasia (Plaintext)</label>
                <input type="text" name="isi" class="form-control" placeholder="Teks ini akan langsung dienkripsi sebelum masuk database..." required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-lock-fill me-1"></i> Enkripsi & Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Data Terenkripsi -->
    <div class="card card-panel p-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0"><i class="bi bi-table text-dark me-2"></i>Data Tersimpan di Database MySQL</h5>
                <small class="text-muted">Kolom Ciphertext & IV adalah bentuk asli yang tersimpan di dalam tabel <code>encrypted_data</code>.</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-dark">
                    <tr>
                        <th width="4%">No</th>
                        <th width="16%">Judul</th>
                        <th width="32%">Ciphertext (Hasil Enkripsi DB)</th>
                        <th width="18%">Initialization Vector (IV)</th>
                        <th width="24%">Hasil Dekripsi (Live)</th>
                        <th width="6%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_data === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                Belum ada catatan terenkripsi. Silakan tambahkan data melalui form di atas.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php $no = 1; while ($row = $data->fetch_assoc()): 
                        $plain = dekripsi($row['ciphertext'], $_SESSION['kunci_simetris'], $row['iv']);
                    ?>
                    <tr>
                        <td class="text-center fw-semibold"><?= $no++ ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                            <small class="text-muted" style="font-size: 11px;"><?= $row['created_at'] ?></small>
                        </td>
                        <td><div class="cipher-pill"><?= htmlspecialchars($row['ciphertext']) ?></div></td>
                        <td><div class="iv-pill"><?= htmlspecialchars($row['iv']) ?></div></td>
                        <td>
                            <?php if ($plain !== false): ?>
                                <div class="p-2 rounded bg-success bg-opacity-10 border border-success border-opacity-25 text-success fw-semibold small">
                                    <i class="bi bi-unlock me-1"></i> <?= htmlspecialchars($plain) ?>
                                </div>
                            <?php else: ?>
                                <div class="p-2 rounded bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger small">
                                    <i class="bi bi-exclamation-octagon me-1"></i> Gagal Dekripsi (Kunci Simetris Salah)
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus catatan terenkripsi ini?')" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>