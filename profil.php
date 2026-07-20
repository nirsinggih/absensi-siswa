<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
include "config.php";

$msg_profil = "";
$msg_password = "";

// Ambil data profil
$q = $conn->query("SELECT * FROM profil_sekolah LIMIT 1");
$profil = $q->fetch_assoc();

// Proses update profil sekolah
if (isset($_POST['simpan'])) {
    $nama       = $_POST['nama'];
    $alamat     = $_POST['alamat'];
    $kepala     = $_POST['kepala'];
    $nip        = $_POST['nip'];
    $jam_masuk  = $_POST['jam_masuk'];
    $jam_pulang = $_POST['jam_pulang'];

    // Upload logo jika ada (AMAN)
$logo = $profil['logo'];

if (!empty($_FILES['logo']['name'])) {

    $allowed_ext  = ['jpg','jpeg','png'];
    $allowed_mime = ['image/jpeg','image/png'];

    $file_tmp  = $_FILES['logo']['tmp_name'];
    $file_name = $_FILES['logo']['name'];
    $file_size = $_FILES['logo']['size'];

    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Cek ekstensi
    if (!in_array($ext, $allowed_ext)) {
        $msg_profil = "<span style='color:red;'>Format logo tidak diizinkan!</span>";
    }
    // Cek ukuran (maks 2MB)
    elseif ($file_size > 2 * 1024 * 1024) {
        $msg_profil = "<span style='color:red;'>Ukuran logo maksimal 2MB!</span>";
    }
    else {
        // Cek MIME type asli
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_mime)) {
            $msg_profil = "<span style='color:red;'>File bukan gambar valid!</span>";
        } 
        // Cek benar-benar gambar
        elseif (!getimagesize($file_tmp)) {
            $msg_profil = "<span style='color:red;'>File bukan gambar asli!</span>";
        }
        else {
            $logo = "logo_" . time() . "." . $ext;
            move_uploaded_file($file_tmp, "uploads/" . $logo);
        }
    }
}

    // Upload background kartu (AMAN)
$background = $profil['background_kartu'];

if (!empty($_FILES['background']['name'])) {

    $allowed_ext  = ['jpg','jpeg'];
    $allowed_mime = ['image/jpeg'];

    $file_tmp  = $_FILES['background']['tmp_name'];
    $file_name = $_FILES['background']['name'];
    $file_size = $_FILES['background']['size'];

    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        $msg_profil = "<span style='color:red;'>Background harus JPG!</span>";
    }
    elseif ($file_size > 3 * 1024 * 1024) {
        $msg_profil = "<span style='color:red;'>Ukuran background maksimal 3MB!</span>";
    }
    else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_mime)) {
            $msg_profil = "<span style='color:red;'>File bukan JPG valid!</span>";
        }
        elseif (!getimagesize($file_tmp)) {
            $msg_profil = "<span style='color:red;'>File bukan gambar asli!</span>";
        }
        else {
            $background = "background_" . time() . ".jpg";
            move_uploaded_file($file_tmp, "uploads/" . $background);
        }
    }
}
    $conn->query("UPDATE profil_sekolah SET 
        nama_sekolah     = '$nama',
        alamat           = '$alamat',
        kepala_sekolah   = '$kepala',
        nip_kepala       = '$nip',
        logo             = '$logo',
        background_kartu = '$background',
        jam_masuk        = " . ($jam_masuk ? "'$jam_masuk'" : "NULL") . ",
        jam_pulang       = " . ($jam_pulang ? "'$jam_pulang'" : "NULL") . "
    WHERE id=" . $profil['id']);

    if (!$msg_profil) {
        $msg_profil = "<span style='color:green;'>Profil sekolah berhasil diperbarui!</span>";
    }

    $q = $conn->query("SELECT * FROM profil_sekolah LIMIT 1");
    $profil = $q->fetch_assoc();
}

// Proses ubah password admin (pakai MD5)
if (isset($_POST['ubah_password'])) {
    $old     = $_POST['old_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Ambil data admin
    $qAdmin = $conn->query("SELECT * FROM users WHERE username='admin' LIMIT 1");
    $admin  = $qAdmin->fetch_assoc();

    if (!$admin) {
        $msg_password = "<span style='color:red;'>Data admin tidak ditemukan!</span>";
    } elseif ($admin['password'] !== md5($old)) {
        $msg_password = "<span style='color:red;'>Password lama salah!</span>";
    } elseif ($new !== $confirm) {
        $msg_password = "<span style='color:red;'>Password baru dan konfirmasi tidak cocok!</span>";
    } else {
        $hash = md5($new);
        $conn->query("UPDATE users SET password='$hash' WHERE username='admin'");
        $msg_password = "<span style='color:green;'>Password berhasil diubah!</span>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profil Sekolah</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f4; }
        .container { max-width: 500px; margin: auto; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
        label { font-weight: bold; display: block; margin-top: 10px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 10px; padding: 10px; background: #28a745; color: #fff; border: none; width: 100%; border-radius: 5px; cursor: pointer; }
        img { max-width: 150px; display: block; margin-top: 10px; border: 1px solid #ccc; padding: 3px; background: #f9f9f9; }
        .msg { margin-top: 10px; padding: 8px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" style="display:inline-block; padding:10px 15px; background:#6c757d; color:#fff; text-decoration:none; border-radius:5px; margin-top:15px;">← Kembali ke Dashboard</a>

    <h2>Profil Sekolah</h2>
    <?php if ($msg_profil) echo "<div class='msg'>$msg_profil</div>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Nama Sekolah</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($profil['nama_sekolah']) ?>" required>

        <label>Alamat</label>
        <textarea name="alamat" required><?= htmlspecialchars($profil['alamat']) ?></textarea>

        <label>Nama Kepala Sekolah</label>
        <input type="text" name="kepala" value="<?= htmlspecialchars($profil['kepala_sekolah']) ?>" required>

        <label>NIP Kepala Sekolah</label>
        <input type="text" name="nip" value="<?= htmlspecialchars($profil['nip_kepala']) ?>" required>

        <label>Jam Masuk</label>
        <input type="time" name="jam_masuk" value="<?= htmlspecialchars($profil['jam_masuk']) ?>" step="60">

        <label>Jam Pulang</label>
        <input type="time" name="jam_pulang" value="<?= htmlspecialchars($profil['jam_pulang']) ?>" step="60">

        <label>Logo Sekolah (PNG)</label>
        <input type="file" name="logo" accept="image/*">
        <?php
        if ($profil['logo']) {
            $logoPath = "uploads/" . $profil['logo'];
            if (file_exists($logoPath)) {
                $version = filemtime($logoPath);
                echo "<img src='{$logoPath}?v={$version}' alt='Logo Sekolah'>";
            }
        }
        ?>

        <label>Background Kartu (JPG)</label>
        <small style="color:#555; display:block; margin-bottom:5px;">
            Gunakan background dengan warna bagian atas gelap, karena teks di atas akan berwarna putih.
            <br>Unduh contoh: <a href="background_kartu.jpg" target="_blank">background_kartu.jpg</a>
        </small>
        <input type="file" name="background" accept="image/jpeg">
        <?php
        if ($profil['background_kartu']) {
            $bgPath = "uploads/" . $profil['background_kartu'];
            if (file_exists($bgPath)) {
                $version = filemtime($bgPath);
                echo "<img src='{$bgPath}?v={$version}' alt='Background Kartu'>";
            }
        }
        ?>

        <button type="submit" name="simpan">Simpan Profil</button>
    </form>

    <hr>

    <h3>Ubah Password Admin</h3>
    <p>Gunakan password yang kuat terdiri dari kombinasi huruf besar kecil, angka, dan tanda baca.</p>
    <?php if ($msg_password) echo "<div class='msg'>$msg_password</div>"; ?>
    <form method="POST">
        <label>Password Lama</label>
        <input type="password" name="old_password" required>

        <label>Password Baru</label>
        <input type="password" name="new_password" required>

        <label>Ulangi Password Baru</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" name="ubah_password">Ubah Password</button>
    </form>
</div>
</body>
</html>
