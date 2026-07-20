<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id       = $_POST['siswa_id'];
    $pelanggaran_id = $_POST['pelanggaran_id'];
    $keterangan     = $_POST['keterangan'];
// Pisahkan "id - nama"
$siswa_raw = $_POST['siswa_id'];
$siswa_id = intval(explode(" - ", $siswa_raw)[0]);

$pelanggaran_raw = $_POST['pelanggaran_id'];
$pelanggaran_id = intval(explode(" - ", $pelanggaran_raw)[0]);

    // Ambil user_id dari users sesuai session username
    $username = $_SESSION['username'];
    $q = $conn->query("SELECT id FROM users WHERE username='$username' LIMIT 1");
    $row = $q->fetch_assoc();
    $user_id = $row ? $row['id'] : 0;

    $sql = "INSERT INTO pelanggaran_log (siswa_id, pelanggaran_id, user_id, keterangan, tanggal)
            VALUES ('$siswa_id', '$pelanggaran_id', '$user_id', '$keterangan', NOW())";

    if ($conn->query($sql)) {
        header("Location: riwayat.php?success=1");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
