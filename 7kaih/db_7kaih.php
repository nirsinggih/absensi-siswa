<?php

// include config.php yang ada di folder absensi-qr
include __DIR__ . "/../config.php";

// 1️⃣ Tambahkan dulu ENUM baru + izinkan NULL
$conn->query("
ALTER TABLE jurnal_kebiasaan 
MODIFY makanan_sehat 
ENUM('Makanan Asli','Makanan Instan/Pabrik','Makanan Asli/Masak','Makanan Asli dan Instan') 
NULL COMMENT 'Jenis makanan sehat';
");

// 2️⃣ Update data lama
$conn->query("
UPDATE jurnal_kebiasaan 
SET makanan_sehat = 'Makanan Asli/Masak'
WHERE TRIM(makanan_sehat) = 'Makanan Asli'
");

// 3️⃣ Final ENUM (tanpa nilai lama)
$sql = "
ALTER TABLE jurnal_kebiasaan 
MODIFY makanan_sehat 
ENUM('Makanan Asli/Masak','Makanan Instan/Pabrik','Makanan Asli dan Instan') 
NULL COMMENT 'Jenis makanan sehat';
";

if ($conn->query($sql)) {
    echo "✅ ENUM berhasil diperbarui dan data kosong diabaikan.";
} else {
    echo "❌ Error: " . $conn->error;
}

// Buat tabel jika belum ada
$sql = "
CREATE TABLE IF NOT EXISTS jurnal_kebiasaan (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Primary Key, Auto Increment',
    siswa_id INT NOT NULL COMMENT 'Relasi ke tabel siswa',
    jam_bangun TIME NOT NULL COMMENT 'Jam bangun tidur',
    beribadah ENUM('Di Rumah','Di Tempat Ibadah') NOT NULL COMMENT 'Tempat beribadah',
    jam_olahraga TIME COMMENT 'Jam melakukan olahraga',
    makanan_sehat ENUM('Makanan Asli/Masak','Makanan Instan/Pabrik','Makanan Asli dan Instan') NOT NULL COMMENT 'Jenis makanan sehat',
    jam_belajar TIME COMMENT 'Jam belajar',
    bermasyarakat TEXT COMMENT 'Aktivitas bermasyarakat',
    jam_tidur TIME COMMENT 'Jam tidur',
    keterangan TEXT COMMENT 'Catatan tambahan',
    foto VARCHAR(255) COMMENT 'Foto dokumentasi',
    tanggal_input TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal input data'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabel jurnal_kebiasaan siap digunakan dengan kolom:\n";
    echo "- id (Primary Key, Auto Increment)\n";
    echo "- id_siswa (Relasi ke siswa)\n";
    echo "- jam_bangun (Jam bangun tidur)\n";
    echo "- beribadah (Tempat beribadah)\n";
    echo "- jam_olahraga (Jam olahraga)\n";
    echo "- makanan_sehat (Jenis makanan sehat)\n";
    echo "- jam_belajar (Jam belajar)\n";
    echo "- bermasyarakat (Aktivitas bermasyarakat)\n";
    echo "- jam_tidur (Jam tidur)\n";
    echo "- keterangan (Catatan tambahan)\n";
    echo "- foto (Foto dokumentasi)\n";
    echo "- tanggal_input (Tanggal input data)\n";
} else {
    echo "❌ Error buat tabel: " . $conn->error;
}
?>