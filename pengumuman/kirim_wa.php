<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
include "../config.php";

$totalQ = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa WHERE no_wa <> ''");
$totalData = mysqli_fetch_assoc($totalQ)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kirim WA Massal</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
#progressBox { display:none; }
</style>
</head>
<body class="container py-4">

<h3>📢 Kirim WA Massal (Anti Timeout)</h3>
<a href="../modif.php" class="btn btn-secondary mb-3">← Kembali</a>

<form id="formWA">
<div class="mb-3">
<label class="form-label">Format Pesan</label>
<textarea name="pesan" class="form-control" rows="4" required
placeholder="Yth. Orang Tua {nama} kelas {kelas}, besok masuk pukul 07.00 WIB."></textarea>
<div class="form-text">Gunakan {nama} dan {kelas}</div>
</div>

<div class="mb-3">
<label class="form-label">Jeda (detik)</label>
<input type="number" name="delay" value="1" min="0" max="10" class="form-control">
</div>

<button type="submit" class="btn btn-primary">🚀 Mulai Kirim</button>
</form>

<hr>

<div id="progressBox">
<h5>Progress Pengiriman</h5>
<div class="progress mb-3">
<div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
role="progressbar" style="width:0%">0%</div>
</div>
<div id="log" style="height:300px; overflow:auto; background:#f8f9fa; padding:10px;"></div>
</div>

<script>
let total = <?php echo $totalData; ?>;
let current = 0;
let pesanGlobal = '';
let delayGlobal = 1;

document.getElementById('formWA').addEventListener('submit', function(e){
    e.preventDefault();
    pesanGlobal = this.pesan.value;
    delayGlobal = this.delay.value;
    current = 0;

    document.getElementById('progressBox').style.display = 'block';
    document.getElementById('log').innerHTML = '';
    kirimBerikutnya();
});

function kirimBerikutnya(){
    if(current >= total){
        document.getElementById('log').innerHTML += "<hr><b>✅ Semua pesan selesai dikirim</b>";
        return;
    }

    fetch('proses_kirim_wa.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'offset=' + current + 
              '&pesan=' + encodeURIComponent(pesanGlobal) +
              '&delay=' + delayGlobal
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('log').innerHTML += data.message + "<br>";
        document.getElementById('log').scrollTop = document.getElementById('log').scrollHeight;

        current++;
        let persen = Math.round((current/total)*100);
        document.getElementById('progressBar').style.width = persen+'%';
        document.getElementById('progressBar').innerText = persen+'%';

        setTimeout(kirimBerikutnya, delayGlobal * 1000);
    })
    .catch(err=>{
        document.getElementById('log').innerHTML += "❌ Error koneksi<br>";
    });
}
</script>

</body>
</html>