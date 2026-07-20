<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Pelanggaran</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .autocomplete-box {
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
            position: absolute;
            background: #fff;
            width: 100%;
            z-index: 999;
        }
        .autocomplete-item {
            padding: 6px;
            cursor: pointer;
        }
        .autocomplete-item:hover {
            background: #eee;
        }
        .input-wrapper {
            position: relative;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>➕ Tambah Pelanggaran</h2>
    <form action="simpan.php" method="post">
        <div class="card">
            <!-- Autocomplete siswa -->
            <label>Siswa</label>
            <div class="input-wrapper">
                <input type="text" id="siswa_search" placeholder="Ketik nama siswa..." autocomplete="off">
                <input type="hidden" name="siswa_id" id="siswa_id">
                <div id="siswa_result" class="autocomplete-box"></div>
            </div>

            <!-- Autocomplete pelanggaran -->
            <label>Pelanggaran</label>
            <div class="input-wrapper">
                <input type="text" id="pelanggaran_search" placeholder="Ketik nama pelanggaran..." autocomplete="off">
                <input type="hidden" name="pelanggaran_id" id="pelanggaran_id">
                <div id="pelanggaran_result" class="autocomplete-box"></div>
            </div>

            <label>Keterangan</label>
            <textarea name="keterangan"></textarea>

            <button type="submit">Simpan</button>
        </div>
    </form>
</div>

<script>
// Fungsi AJAX autocomplete siswa
document.getElementById("siswa_search").addEventListener("keyup", function(){
    let keyword = this.value;
    if(keyword.length < 2){
        document.getElementById("siswa_result").innerHTML = "";
        return;
    }
    fetch("search_siswa.php?q="+keyword)
    .then(res => res.text())
    .then(data => {
        document.getElementById("siswa_result").innerHTML = data;
    });
});

function pilihSiswa(id, nama){
    document.getElementById("siswa_id").value = id;
    document.getElementById("siswa_search").value = nama;
    document.getElementById("siswa_result").innerHTML = "";
}

// Fungsi AJAX autocomplete pelanggaran
document.getElementById("pelanggaran_search").addEventListener("keyup", function(){
    let keyword = this.value;
    if(keyword.length < 2){
        document.getElementById("pelanggaran_result").innerHTML = "";
        return;
    }
    fetch("search_pelanggaran.php?q="+keyword)
    .then(res => res.text())
    .then(data => {
        document.getElementById("pelanggaran_result").innerHTML = data;
    });
});

function pilihPelanggaran(id, nama){
    document.getElementById("pelanggaran_id").value = id;
    document.getElementById("pelanggaran_search").value = nama;
    document.getElementById("pelanggaran_result").innerHTML = "";
}
</script>
</body>
</html>
