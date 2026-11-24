CREATE DATABASE IF NOT EXISTS db_antrean_kantin;
USE db_antrean_kantin;

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    harga INT NOT NULL,
    stok INT NOT NULL,
    aktif TINYINT(1) DEFAULT 1
);

CREATE TABLE antrean (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_antrean VARCHAR(10) NOT NULL,
    nama_pemesan VARCHAR(100) NOT NULL,
    kelas VARCHAR(30) NOT NULL,
    metode_bayar VARCHAR(30) NOT NULL,
    daftar_pesanan TEXT NOT NULL,
    total_harga INT NOT NULL,
    catatan VARCHAR(255) DEFAULT '',
    status ENUM('Menunggu','Diproses','Selesai','Dibatalkan') DEFAULT 'Menunggu',
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO menu (nama_menu, kategori, harga, stok) VALUES
('Nasi Goreng Spesial', 'Makanan', 12000, 30),
('Mie Ayam Bakso', 'Makanan', 10000, 25),
('Ayam Geprek Sambal', 'Makanan', 13000, 20),
('Es Teh Manis', 'Minuman', 4000, 50),
('Es Jeruk Segar', 'Minuman', 5000, 40),
('Roti Bakar Cokelat', 'Camilan', 8000, 25);