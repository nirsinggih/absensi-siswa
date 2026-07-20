<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . "/../config.php"; 

// Pastikan role siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['siswa_id']) || !isset($_SESSION['username'])) {
    die("⚠️ Data siswa tidak ditemukan, silakan login ulang.");
}

$siswa_id = intval($_SESSION['siswa_id']);
$username = $_SESSION['username'];

// Ambil data siswa
$q = mysqli_query($conn, "SELECT nama, kelas, no_wa, foto_siswa FROM siswa WHERE id = $siswa_id");
$siswa = mysqli_fetch_assoc($q);

// Proses simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jam_bangun     = $_POST['jam_bangun'] ?? null;
    $beribadah      = $_POST['beribadah'] ?? null;
    $jam_olahraga   = $_POST['jam_olahraga'] ?? null;
    $makanan_sehat  = $_POST['makanan_sehat'] ?? null;
    $jam_belajar    = $_POST['jam_belajar'] ?? null;
    $bermasyarakat  = $_POST['bermasyarakat'] ?? null;
    $jam_tidur      = $_POST['jam_tidur'] ?? null;
    $keterangan     = $_POST['keterangan'] ?? null;

   // ===============================
// 🔐 Upload Foto (Versi Aman)
// ===============================
$foto = null;

if (!empty($_FILES['foto']['name'])) {

    $file_tmp  = $_FILES['foto']['tmp_name'];
    $file_name = $_FILES['foto']['name'];
    $file_size = $_FILES['foto']['size'];
    $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $targetDir = __DIR__ . "/uploads/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // 🔒 1. Batasi ukuran maksimal 1MB (server side)
    if ($file_size > 1 * 1024 * 1024) {
        die("⚠️ Ukuran file terlalu besar (maks 1MB).");
    }

    // 🔒 2. Blokir nama mencurigakan (php, phtml, dll)
    if (preg_match('/\.php|\.phtml|\.phar/i', $file_name)) {
        die("⚠️ File mencurigakan terdeteksi.");
    }

    // 🔒 3. Validasi ekstensi
    $allowedExt = ['jpg','jpeg','png','gif'];
    if (!in_array($ext, $allowedExt)) {
        die("⚠️ Format file tidak diizinkan.");
    }

    // 🔒 4. Validasi MIME type asli
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);

    $allowedMime = ['image/jpeg','image/png','image/gif'];
    if (!in_array($mime, $allowedMime)) {
        die("⚠️ File bukan gambar valid.");
    }

    // 🔒 5. Validasi benar-benar gambar
    if (!getimagesize($file_tmp)) {
        die("⚠️ File bukan gambar asli.");
    }

    // 🔒 6. Buat nama file random (tidak pakai nama asli)
    $foto = "jurnal_" . $siswa_id . "_" . time() . "." . $ext;
    $targetFile = $targetDir . $foto;

    if (!move_uploaded_file($file_tmp, $targetFile)) {
        $foto = null;
    }
}

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO jurnal_kebiasaan 
        (siswa_id, jam_bangun, beribadah, jam_olahraga, makanan_sehat, jam_belajar, bermasyarakat, jam_tidur, keterangan, foto) 
        VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("isssssssss", 
        $siswa_id, $jam_bangun, $beribadah, $jam_olahraga, $makanan_sehat, 
        $jam_belajar, $bermasyarakat, $jam_tidur, $keterangan, $foto
    );

    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ Data berhasil disimpan!</p>";
    } else {
        echo "<p style='color:red'>❌ Gagal menyimpan data: " . $stmt->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Jurnal 7 Kebiasaan Anak Indonesia Hebat</title>
<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 10px; 
        background: #f8f9fa;
    }
    .container {
        max-width: 500px;
        margin: auto;
        background: #fff;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2 { text-align: center; }
    label { display: block; margin-top: 10px; font-weight: bold; }
    textarea, select, input[type="time"], input[type="file"] {
        margin-top: 5px; 
        padding: 8px; 
        width: 100%; 
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    button, .btn-back {
        margin-top: 15px; 
        padding: 10px 15px; 
        width: 100%; 
        border: none; 
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }
    button {
        background: #28a745; 
        color: white;
    }
    .btn-back {
        background: #007bff; 
        color: white; 
        text-decoration: none; 
        display: inline-block;
        text-align: center;
    }
    #preview-img {
        max-width: 120px; 
        max-height: 120px; 
        border-radius: 8px; 
        display: none; 
        border: 1px solid #ccc; 
        padding: 3px;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Halo, <?= htmlspecialchars($siswa['nama']) ?> 👋</h2>
    <p>Silakan isi Jurnal 7 Kebiasaan Anak Indonesia Hebat hari ini.</p>

    <a href="../dashboard_siswa.php" class="btn-back">⬅ Kembali ke Dashboard</a>
<a href="rekap_jurnal.php" class="btn-back">Riwayat Jurnal</a>

    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        <label>Jam Bangun Pagi:
            <input type="time" name="jam_bangun" required>
        </label>

        <label>Beribadah:
            <select name="beribadah" id="beribadah" required>
                <option value="">-- Pilih --</option>
                <option value="Di Rumah">Di Rumah</option>
                <option value="Di Tempat Ibadah">Di Tempat Ibadah</option>
				<option value="Di Rumah dan Tempat Ibadah">Di Rumah dan Tempat Ibadah</option>
            </select>
        </label>

        <label>Jam Olahraga:
            <input type="time" name="jam_olahraga">
        </label>

        <label>Makanan Sehat:
            <select name="makanan_sehat" id="makanan_sehat" required>
                <option value="">-- Pilih --</option>
                <option value="Makanan Asli/Masak">Makanan Asli/Masak</option>
                <option value="Makanan Instan/Pabrik">Makanan Instan/Pabrik</option>
				 <option value="Makanan Asli dan Instan">Makanan Asli dan Instan</option>
            </select>
        </label>

        <label>Jam Belajar di Rumah:
            <input type="time" name="jam_belajar">
        </label>

        <label>Bermasyarakat:
            <textarea name="bermasyarakat"></textarea>
        </label>

        <label>Jam Tidur:
            <input type="time" name="jam_tidur">
        </label>

        <label>Keterangan:
            <textarea name="keterangan"></textarea>
        </label>

        <label>Foto Bermasyarakat:
            <input type="file" name="foto" id="foto" accept="image/*">
        </label>

        <div id="preview" style="margin-top:10px; text-align:center;">
            <img id="preview-img" src="" alt="Preview Foto">
        </div>

        <button type="submit">💾 Simpan</button>
    </form>
</div>

<script>
function validateForm() {
    let ibadah = document.getElementById("beribadah").value;
    let makanan = document.getElementById("makanan_sehat").value;
    let foto = document.getElementById("foto").files[0];

    if (ibadah === "") {
        alert("⚠️ Silakan pilih jenis ibadah terlebih dahulu.");
        return false;
    }
    if (makanan === "") {
        alert("⚠️ Silakan pilih jenis makanan sehat terlebih dahulu.");
        return false;
    }
    if (foto) {
        let maxSize = 100 * 1024; // 100 KB
        if (foto.size > maxSize) {
            alert("⚠️ Ukuran foto maksimal 100 KB!");
            return false;
        }
    }
    return true;
}

// Preview foto otomatis
document.getElementById("foto").addEventListener("change", function() {
    let file = this.files[0];
    let previewImg = document.getElementById("preview-img");

    if (file) {
        let maxSize = 100 * 1024; // 100 KB
        if (file.size > maxSize) {
            alert("⚠️ Ukuran foto maksimal 100 KB!");
            this.value = ""; // reset file input
            previewImg.style.display = "none";
            return;
        }

        let reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = "inline-block";
        }
        reader.readAsDataURL(file);
    } else {
        previewImg.style.display = "none";
    }
});
</script>
</body>
</html>
