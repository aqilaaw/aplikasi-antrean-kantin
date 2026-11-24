<?php
require_once __DIR__ . '/config/koneksi.php';

$today = date('Y-m-d');
$menuList = $pdo->query("SELECT * FROM menu WHERE aktif = 1 AND stok > 0 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$stmtDiproses = $pdo->prepare("SELECT nomor_antrean FROM antrean WHERE tanggal = ? AND status = 'Diproses' ORDER BY id ASC LIMIT 1");
$stmtDiproses->execute([$today]);
$sedangDiproses = $stmtDiproses->fetchColumn() ?: '-';

$stmtAktif = $pdo->prepare("SELECT COUNT(*) FROM antrean WHERE tanggal = ? AND status IN ('Menunggu','Diproses')");
$stmtAktif->execute([$today]);
$antreanAktif = (int)$stmtAktif->fetchColumn();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_pemesan'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $metode = trim($_POST['metode_bayar'] ?? 'Tunai');
    $catatan = trim($_POST['catatan'] ?? '');
    $cartJson = $_POST['cart_data'] ?? '[]';
    $cart = json_decode($cartJson, true);

    if ($nama === '' || $kelas === '' || empty($cart)) {
        $error = "Lengkapi nama, kelas, dan pilih minimal 1 menu ke keranjang!";
    } else {
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM antrean WHERE tanggal = ?");
        $stmtCount->execute([$today]);
        $nextNum = (int)$stmtCount->fetchColumn() + 1;
        $nomorAntrean = 'A' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

        $rincian = [];
        $totalHarga = 0;

        foreach ($cart as $item) {
            $mId = (int)$item['id'];
            $qty = (int)$item['qty'];
            $stmtM = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
            $stmtM->execute([$mId]);
            $m = $stmtM->fetch(PDO::FETCH_ASSOC);
            if ($m && $m['stok'] >= $qty) {
                $rincian[] = $m['nama_menu'] . " x" . $qty;
                $totalHarga += ($m['harga'] * $qty);
                $pdo->prepare("UPDATE menu SET stok = stok - ? WHERE id = ?")->execute([$qty, $mId]);
            }
        }

        $daftarText = implode(", ", $rincian);
        $ins = $pdo->prepare("INSERT INTO antrean (nomor_antrean, nama_pemesan, kelas, metode_bayar, daftar_pesanan, total_harga, catatan, status, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu', ?)");
        $ins->execute([$nomorAntrean, $nama, $kelas, $metode, $daftarText, $totalHarga, $catatan, $today]);
        $lastId = $pdo->lastInsertId();

        header("Location: tiket.php?id=" . $lastId);
        exit;
    }
}

$stmtList = $pdo->prepare("SELECT * FROM antrean WHERE tanggal = ? ORDER BY id DESC");
$stmtList->execute([$today]);
$antreanHariIni = $stmtList->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KantinKu Express - Antrean Kantin Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">
        <div class="brand-mark">K</div>
        <div><strong>KANTIN SEKOLAH</strong><br><small>Layanan Pemesanan & Antrean Digital</small></div>
    </div>
    <nav class="nav-links">
        <a href="#katalog">Menu Kantin</a>
        <a href="#antrean">Pantau Antrean</a>
        <a href="petugas.php" class="btn-petugas">Panel Petugas</a>
    </nav>
</header>

<div class="container">
    <div class="hero">
        <small>PESAN DARI KELAS, AMBIL TANPA ANTRE</small>
        <h1>Pesan makanan, tanpa perlu berdesakan.</h1>
        <p>Pilih menu ke keranjang, isi nama & kelas, lalu pantau nomor antreanmu secara langsung.</p>
    </div>

    <div class="stats">
        <div class="stat-card"><span>Sedang Diproses</span><strong><?= htmlspecialchars($sedangDiproses) ?></strong></div>
        <div class="stat-card"><span>Antrean Aktif</span><strong><?= $antreanAktif ?></strong></div>
        <div class="stat-card"><span>Menu Tersedia</span><strong><?= count($menuList) ?></strong></div>
    </div>

    <?php if ($error): ?>
        <div style="background:#fee2e2;color:#b91c1c;padding:12px;border-radius:10px;margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid-main" id="katalog">
        <div>
            <div class="card">
                <h2>Pilih Menu Kantin</h2>
                <div class="menu-grid">
                    <?php foreach ($menuList as $m): ?>
                    <div class="menu-item">
                        <div>
                            <small style="color:#2563eb;font-weight:bold;"><?= htmlspecialchars($m['kategori']) ?></small><br>
                            <strong><?= htmlspecialchars($m['nama_menu']) ?></strong><br>
                            <span style="color:#1e40af;font-weight:bold;">Rp <?= number_format($m['harga'],0,',','.') ?></span>
                            <small style="color:#64748b;">(Stok: <?= $m['stok'] ?>)</small>
                        </div>
                        <button type="button" class="btn-add" onclick='addToCart(<?= json_encode($m) ?>)'>+ Tambah</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card" id="antrean">
                <h2>Pemantauan Antrean Hari Ini (LIVE)</h2>
                <table>
                    <thead><tr><th>No.</th><th>Pemesan & Kelas</th><th>Pesanan</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (!$antreanHariIni): ?>
                        <tr><td colspan="4">Belum ada antrean hari ini.</td></tr>
                    <?php else: foreach ($antreanHariIni as $a): ?>
                        <tr>
                            <td><strong style="color:#1d4ed8;"><?= htmlspecialchars($a['nomor_antrean']) ?></strong></td>
                            <td><strong><?= htmlspecialchars($a['nama_pemesan']) ?></strong><br><small><?= htmlspecialchars($a['kelas']) ?> &bull; <?= htmlspecialchars($a['metode_bayar']) ?></small></td>
                            <td><?= htmlspecialchars($a['daftar_pesanan']) ?></td>
                            <td><span class="badge st-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="position:sticky;top:85px;">
            <h2>Keranjang & Checkout</h2>
            <div id="cartContainer" style="margin:12px 0;min-height:60px;color:#64748b;font-size:14px;">Keranjang masih kosong.</div>
            <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:16px;padding:12px 0;border-top:1px solid #e2e8f0;">
                <span>Total Bayar</span>
                <span id="cartTotal" style="color:#1d4ed8;">Rp 0</span>
            </div>

            <form method="POST" onsubmit="return submitOrder()">
                <input type="hidden" name="cart_data" id="cartDataInput">
                <label>Nama Lengkap Siswa</label>
                <input type="text" name="nama_pemesan" placeholder="Contoh: Budi Santoso" required>

                <label>Kelas</label>
                <select name="kelas" required>
                    <option value="">-- Pilih Kelas --</option>
                    <option value="XII RPL 1">XII RPL 1</option>
                    <option value="XII RPL 2">XII RPL 2</option>
                    <option value="XI RPL 1">XI RPL 1</option>
                    <option value="XI RPL 2">XI RPL 2</option>
                    <option value="X RPL 1">X RPL 1</option>
                </select>

                <label>Metode Pembayaran</label>
                <select name="metode_bayar" required>
                    <option value="Tunai di Kantin">Tunai di Kantin</option>
                    <option value="QRIS Kantin">QRIS Kantin</option>
                    <option value="E-Wallet (DANA/OVO/GoPay)">E-Wallet (DANA/OVO/GoPay)</option>
                </select>

                <label>Catatan (Opsional)</label>
                <input type="text" name="catatan" placeholder="Contoh: Tidak pedas, es sedikit">

                <button type="submit" class="btn-submit">Buat Pesanan & Ambil Antrean</button>
            </form>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(item) {
    let found = cart.find(c => c.id == item.id);
    if (found) {
        if (found.qty < item.stok) found.qty++;
    } else {
        cart.push({ id: item.id, nama: item.nama_menu, harga: parseInt(item.harga), stok: parseInt(item.stok), qty: 1 });
    }
    renderCart();
}

function updateQty(id, delta) {
    let idx = cart.findIndex(c => c.id == id);
    if (idx !== -1) {
        cart[idx].qty += delta;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
    }
    renderCart();
}

function renderCart() {
    const box = document.getElementById('cartContainer');
    const totalEl = document.getElementById('cartTotal');
    if (cart.length === 0) {
        box.innerHTML = 'Keranjang masih kosong.';
        totalEl.innerText = 'Rp 0';
        return;
    }
    let html = '';
    let total = 0;
    cart.forEach(c => {
        let sub = c.harga * c.qty;
        total += sub;
        html += `<div class="cart-item">
            <div><strong>${c.nama}</strong><br><small>Rp ${c.harga.toLocaleString('id-ID')} x ${c.qty}</small></div>
            <div style="display:flex;align-items:center;gap:6px;">
                <button type="button" class="qty-btn" onclick="updateQty(${c.id}, -1)">-</button>
                <strong>${c.qty}</strong>
                <button type="button" class="qty-btn" onclick="updateQty(${c.id}, 1)">+</button>
            </div>
        </div>`;
    });
    box.innerHTML = html;
    totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
}

function submitOrder() {
    if (cart.length === 0) {
        alert('Pilih minimal 1 menu ke keranjang terlebih dahulu!');
        return false;
    }
    document.getElementById('cartDataInput').value = JSON.stringify(cart);
    return true;
}
</script>
</body>
</html>