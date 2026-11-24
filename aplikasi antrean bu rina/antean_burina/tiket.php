<?php
require_once __DIR__ . '/config/koneksi.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM antrean WHERE id = ?");
$stmt->execute([$id]);
$tiket = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Tiket Antrean</title><link rel="stylesheet" href="style.css"></head>
<body style="display:flex;justify-content:center;padding:40px;">
<div class="card" style="width:420px;text-align:center;">
    <div class="brand-mark" style="margin:0 auto 12px;">K</div>
    <small style="color:#2563eb;font-weight:bold;">TIKET ANTREAN KANTIN</small>
    <h2>Pesanan Berhasil!</h2>
    <div style="font-size:48px;font-weight:900;color:#1d4ed8;margin:16px 0;"><?= htmlspecialchars($tiket['nomor_antrean']) ?></div>
    <div style="text-align:left;background:#f8fafc;padding:16px;border-radius:12px;font-size:14px;line-height:1.8;">
        Nama: <strong><?= htmlspecialchars($tiket['nama_pemesan']) ?></strong><br>
        Kelas: <strong><?= htmlspecialchars($tiket['kelas']) ?></strong><br>
        Metode Bayar: <strong><?= htmlspecialchars($tiket['metode_bayar']) ?></strong><br>
        Pesanan: <strong><?= htmlspecialchars($tiket['daftar_pesanan']) ?></strong><br>
        Total: <strong>Rp <?= number_format($tiket['total_harga'],0,',','.') ?></strong><br>
        Status: <span class="badge st-<?= $tiket['status'] ?>"><?= $tiket['status'] ?></span>
    </div>
    <div class="no-print" style="margin-top:18px;display:flex;flex-direction:column;gap:8px;">
        <button onclick="window.print()" class="btn-submit">Cetak Tiket</button>
        <a href="index.php" style="color:#1d4ed8;font-weight:600;text-decoration:none;margin-top:6px;">&larr; Kembali ke Halaman Antrean</a>
    </div>
</div>
</body>
</html>