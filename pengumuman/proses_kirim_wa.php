<?php
session_start();
include "../config.php";
date_default_timezone_set("Asia/Jakarta");

header('Content-Type: application/json');

if(!isset($_POST['offset']) || !isset($_POST['pesan'])){
    echo json_encode(["message"=>"❌ Parameter tidak lengkap"]);
    exit;
}

$offset = intval($_POST['offset']);
$pesanTemplate = trim($_POST['pesan']);

// Ambil 1 siswa saja
$q = mysqli_query($conn, "SELECT nama, kelas, no_wa FROM siswa WHERE no_wa <> '' LIMIT 1 OFFSET $offset");
if(!$q || mysqli_num_rows($q)==0){
    echo json_encode(["message"=>"⚠️ Data tidak ditemukan"]);
    exit;
}

$s = mysqli_fetch_assoc($q);

// Ganti placeholder
$pesanFinal = str_replace(
    ["{nama}", "{kelas}"],
    [$s['nama'], $s['kelas']],
    $pesanTemplate
);

// Format nomor Indonesia
$nomor = preg_replace('/[^0-9]/', '', $s['no_wa']);
if (substr($nomor,0,1)=='0'){
    $nomor = '+62'.substr($nomor,1);
}elseif(substr($nomor,0,2)!='62'){
    $nomor = '+62'.$nomor;
}else{
    $nomor = '+'.$nomor;
}

// Ambil token
$t = mysqli_query($conn, "SELECT key_wa_sidobe FROM profil_sekolah WHERE id=1");
$row = mysqli_fetch_assoc($t);
$token = trim($row['key_wa_sidobe']);

// Kirim via cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.sidobe.com/wa/v1/send-message",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "X-Secret-Key: $token",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode([
        "phone"=>$nomor,
        "message"=>$pesanFinal
    ])
]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if($err){
    echo json_encode(["message"=>"❌ {$s['nama']} ($nomor) : $err"]);
    exit;
}

$data = json_decode($response,true);
if(!empty($data['is_success']) && strtolower($data['data']['status'] ?? '')==='success'){
    echo json_encode(["message"=>"✅ {$s['nama']} ($nomor) berhasil"]);
}else{
    echo json_encode(["message"=>"⚠️ {$s['nama']} ($nomor) gagal"]);
}