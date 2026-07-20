<?php
include "../config.php";

// Daftar pelanggaran & poin
$pelanggaran = [
    ["Terlambat masuk sekolah", 5],
    ["Tidak memakai seragam rapi", 5],
    ["Tidak membawa buku pelajaran", 3],
    ["Membolos tanpa izin", 20],
    ["Merokok di lingkungan sekolah", 50],
    ["Berkelahi di sekolah", 75],
    ["Menggunakan HP saat pelajaran", 15],
    ["Tidak mengikuti upacara", 10],
    ["Mengotori lingkungan sekolah", 10],
    ["Bersikap tidak sopan pada guru", 30]
];

// Insert ke database
foreach ($pelanggaran as $p) {
    $nama = $conn->real_escape_string($p[0]);
    $poin = $p[1];
    $sql = "INSERT INTO pelanggaran (nama_pelanggaran, poin) VALUES ('$nama', '$poin')";
    if ($conn->query($sql)) {
        echo "✔️ $nama berhasil ditambahkan<br>";
    } else {
        echo "❌ Gagal menambahkan $nama: " . $conn->error . "<br>";
    }
}
