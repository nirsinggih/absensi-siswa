<?php
include "../config.php"; // koneksi ke database (gunakan $conn)

try {
    // === Buat tabel pelanggaran jika belum ada ===
    $sql1 = "CREATE TABLE IF NOT EXISTS pelanggaran (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_pelanggaran VARCHAR(100) NOT NULL,
        poin INT NOT NULL
    ) ENGINE=InnoDB;";

    if ($conn->query($sql1) === TRUE) {
        echo "Tabel 'pelanggaran' siap digunakan.<br>";
    } else {
        echo "Error membuat tabel pelanggaran: " . $conn->error . "<br>";
    }

    // === Buat tabel pelanggaran_log jika belum ada ===
    $sql2 = "CREATE TABLE IF NOT EXISTS pelanggaran_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siswa_id INT NOT NULL,
        pelanggaran_id INT NOT NULL,
        user_id INT NOT NULL,
        keterangan TEXT,
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pelanggaran_id) REFERENCES pelanggaran(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";

    if ($conn->query($sql2) === TRUE) {
        echo "Tabel 'pelanggaran_log' siap digunakan.<br>";
    } else {
        echo "Error membuat tabel pelanggaran_log: " . $conn->error . "<br>";
    }

} catch (Exception $e) {
    echo "Terjadi error: " . $e->getMessage();
}

$conn->close();
?>
