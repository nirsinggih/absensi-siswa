<?php
include "config.php";
session_start();

// ✅ Atur timezone ke WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

// Pastikan hanya role tertentu yang bisa mengakses
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

// Pastikan ada parameter NISN
if (!isset($_GET['nisn'])) {
    echo json_encode(["message" => "NISN tidak ditemukan"]);
    exit;
}

$nisn = $_GET['nisn'];

// 🔎 Cari data siswa + nomor WA
$sql = "SELECT id, nama, kelas, no_wa FROM siswa WHERE nisn='$nisn' LIMIT 1";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    echo json_encode(["message" => "❌ Siswa tidak ditemukan"]);
    exit;
}

$s = mysqli_fetch_assoc($res);
$siswa_id = $s['id'];
$nama     = $s['nama'];
$kelas    = $s['kelas'];
$no_wa    = $s['no_wa'] ?? '';

$tanggal  = date("Y-m-d");
$jam      = date("H:i:s");
$jam_disp = date("H:i");

// ✅ Normalisasi nomor WA ke format E.164 (+62…)
$no_wa = preg_replace('/[^0-9]/', '', $no_wa); // hanya angka
if (substr($no_wa, 0, 1) === "0") {
    $no_wa = "+62" . substr($no_wa, 1);
} elseif (substr($no_wa, 0, 2) === "62") {
    $no_wa = "+" . $no_wa;
} elseif (substr($no_wa, 0, 3) !== "+62") {
    $no_wa = "";
}

// ✅ Ambil secret key dari tabel profil_sekolah
$secretKey = "";
$qKey = mysqli_query($conn, "SELECT key_wa_sidobe FROM profil_sekolah LIMIT 1");
if ($qKey && mysqli_num_rows($qKey) > 0) {
    $rowKey = mysqli_fetch_assoc($qKey);
    $secretKey = $rowKey['key_wa_sidobe'] ?? "";
}

// ✅ Cek apakah sudah ada absen hari ini
$cek = mysqli_query($conn, "SELECT id, jam, jam_pulang FROM absensi WHERE siswa_id='$siswa_id' AND tanggal='$tanggal' LIMIT 1");

$msg = "";
$wa_status = "Nomor WA belum diisi atau tidak valid.";
$pesan = "";

if (mysqli_num_rows($cek) == 0) {
    // Belum absen → catat jam masuk
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, jam, status) 
                         VALUES ('$siswa_id', '$tanggal', '$jam', 'H')");

    $msg   = "✅ Absen masuk berhasil: $nama ($kelas)<br>🕒 Jam hadir: $jam_disp";
    $pesan = "Halo, Orang tua/wali dari $nama (kelas $kelas).\n\n"
           . "Telah *HADIR* pada $tanggal pukul $jam_disp.";

} else {
    $row_absen = mysqli_fetch_assoc($cek);

    if (is_null($row_absen['jam_pulang']) && $jam >= "09:00:00") {
        // Sudah absen masuk, belum pulang → catat jam pulang
        mysqli_query($conn, "UPDATE absensi SET jam_pulang='$jam' WHERE id='{$row_absen['id']}'");

        $msg   = "✅ Absen pulang berhasil: $nama ($kelas)<br>🕒 Jam pulang: $jam_disp";
        $pesan = "Halo, Orang tua/wali dari $nama (kelas $kelas).\n\n"
               . "Telah *PULANG* pada $tanggal pukul $jam_disp.";
    } else {
        // Sudah absen masuk & mungkin pulang juga
        $msg = "ℹ️ $nama sudah absen hari ini.<br>🕒 Jam hadir: {$row_absen['jam']}";
        if (!is_null($row_absen['jam_pulang'])) {
            $msg .= "<br>🕒 Jam pulang: {$row_absen['jam_pulang']}";
        }
    }
}

// ✅ Kirim WA otomatis hanya jika valid
if (!empty($pesan) && !empty($no_wa) && !empty($secretKey)) {
    $data = [
        'phone'   => $no_wa,   // format +628xxxx
        'message' => $pesan
    ];

    $ch = curl_init('https://api.sidobe.com/wa/v1/send-message');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Secret-Key: ' . $secretKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $resData = json_decode($response, true);
    if ($resData && isset($resData['is_success']) && $resData['is_success']) {
        $wa_status = "📲 WA berhasil dikirim ke $no_wa";
    } else {
        $wa_status = "⚠️ Gagal kirim WA. Response: " . $response;
    }
} elseif (empty($secretKey)) {
    $wa_status = "⚠️ Secret key WA tidak ditemukan di tabel profil_sekolah.";
}

// ✅ Balikan ke frontend
echo json_encode([
    "message" => $msg . "<br>" . $wa_status
]);
?>
