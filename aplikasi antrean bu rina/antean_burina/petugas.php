<?php
require_once __DIR__ . '/config/koneksi.php';
$today = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $pdo->prepare("UPDATE antrean SET status = ? WHERE id = ?")->execute([$_POST['status'], (int)$_POST['id']]);
    header("Location: petugas.php?updated=1");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM antrean WHERE tanggal = ? ORDER BY id ASC");
$stmt->execute([$today]);
$daftar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Panel Petugas Kantin</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="topbar">
    <div class="brand"><div class="brand-mark">K</div><div><strong>PANEL PETUGAS KANTIN</strong><br><small>Pengelolaan Pesanan Masuk</small></div></div>
    <a href="index.php" style="text-decoration:none;color:#1d4ed8;font-weight:bold;">&larr; Halaman Antrean Siswa</a>
</header>
<div class="container">
    <?php if (isset($_GET['updated'])): ?>
        <div style="background:#dcfce7;color:#15803d;padding:12px;border-radius:10px;margin-bottom:16px;">Status pesanan berhasil diperbarui!</div>
    <?php endif; ?>
    <div class="card">
        <h2>Daftar Antrean Masuk Hari Ini</h2>
        <table>
            <thead><tr><th>No.</th><th>Pemesan & Kelas</th><th>Rincian Pesanan</th><th>Total & Bayar</th><th>Catatan</th><th>Ubah Status</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="6">Belum ada pesanan masuk hari ini.</td></tr>
            <?php else: foreach ($daftar as $d): ?>
                <tr>
                    <td><strong style="color:#1d4ed8;font-size:16px;"><?= htmlspecialchars($d['nomor_antrean']) ?></strong></td>
                    <td><strong><?= htmlspecialchars($d['nama_pemesan']) ?></strong><br><small><?= htmlspecialchars($d['kelas']) ?></small></td>
                    <td><?= htmlspecialchars($d['daftar_pesanan']) ?></td>
                    <td><strong>Rp <?= number_format($d['total_harga'],0,',','.') ?></strong><br><small><?= htmlspecialchars($d['metode_bayar']) ?></small></td>
                    <td><?= htmlspecialchars($d['catatan'] ?: '-') ?></td>
                    <td>
                        <form method="POST" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                            <select name="status" style="margin:0;width:130px;">
                                <?php foreach (['Menunggu','Diproses','Selesai','Dibatalkan'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $d['status']===$st?'selected':'' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-add" style="padding:10px 14px;">Simpan</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>