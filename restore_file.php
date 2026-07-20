<?php
session_start();
include __DIR__ . '/config.php';

if (!isset($_SESSION['username']) || 
    !isset($_SESSION['role']) || 
    $_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

$uploadDir = __DIR__ . '/uploads';

function deleteFolderContents($folder) {
    if (!is_dir($folder)) return;

    $protectedFiles = ['.htaccess', 'index.php'];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {

        $filename = $fileinfo->getBasename();

        // 🔒 Lewati file yang dilindungi
        if (in_array($filename, $protectedFiles)) {
            continue;
        }

        if ($fileinfo->isDir()) {
            @rmdir($fileinfo->getRealPath());
        } else {
            @unlink($fileinfo->getRealPath());
        }
    }
}

$message = "";
$messageColor = "green";

if (isset($_POST['restore'])) {

    if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] != 0) {
        $message = "File tidak valid.";
        $messageColor = "red";
    } else {

        $tmpName = $_FILES['backup_file']['tmp_name'];
        $fileSize = $_FILES['backup_file']['size'];

        // 🔒 Batasi ukuran file (maks 20MB)
        if ($fileSize > 20 * 1024 * 1024) {
            $message = "Ukuran file terlalu besar (maks 20MB).";
            $messageColor = "red";
        }

        // 🔒 Validasi MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if ($mime !== 'application/zip') {
            $message = "File harus berformat ZIP.";
            $messageColor = "red";
        }

        if (!extension_loaded('zip')) {
            $message = "Ekstensi ZIP tidak aktif.";
            $messageColor = "red";
        }

        if (empty($message)) {

            $zip = new ZipArchive;

            if ($zip->open($tmpName) === TRUE) {

                // 🔒 Hapus isi uploads dulu
                deleteFolderContents($uploadDir);

                // 🔒 Daftar ekstensi yang DIIZINKAN
                $allowedExtensions = ['jpg','jpeg','png','gif','webp'];

                for ($i = 0; $i < $zip->numFiles; $i++) {

                    $fileName = $zip->getNameIndex($i);

                    // 🔒 Cegah path traversal
                    if (strpos($fileName, '..') !== false) {
                        continue;
                    }

                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    // 🔒 Tolak file berbahaya
                    if (!in_array($ext, $allowedExtensions)) {
                        continue;
                    }

                    $fileContent = $zip->getFromIndex($i);

                    if ($fileContent !== false) {

                        $destination = $uploadDir . '/' . basename($fileName);

                        file_put_contents($destination, $fileContent);
                    }
                }

                $zip->close();

                $message = "Restore berhasil!";
                $messageColor = "green";

            } else {
                $message = "Gagal membuka file ZIP.";
                $messageColor = "red";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Backup & Restore File Foto dan Logo</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.card {
    background: white;
    width: 95%;
    max-width: 400px;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    text-align: center;
}

h2 {
    margin-bottom: 15px;
}

input[type="file"] {
    width: 100%;
    margin-bottom: 10px;
}

button, .btn-link {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    margin-top: 8px;
    text-decoration: none;
    display: inline-block;
}

.restore-btn {
    background-color: #e74c3c;
    color: white;
}

.restore-btn:hover {
    background-color: #c0392b;
}

.backup-btn {
    background-color: #3498db;
    color: white;
}

.backup-btn:hover {
    background-color: #2980b9;
}

.back-btn {
    background-color: #95a5a6;
    color: white;
}

.back-btn:hover {
    background-color: #7f8c8d;
}

.message {
    margin-bottom: 10px;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="card">
    <h2>Backup & Restore File Data Foto dan Logo</h2>

    <?php if($message): ?>
        <div class="message" style="color: <?php echo $messageColor; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- TOMBOL BACKUP -->
    <a href="backup_file.php" class="btn-link backup-btn">
        Download File Backup
    </a>

    <hr style="margin:15px 0;">

    <!-- FORM RESTORE -->
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="backup_file" required>
        <button type="submit" name="restore" class="restore-btn"
        onclick="return confirm('Semua file uploads akan diganti. Lanjutkan?')">
            Restore Sekarang
        </button>
    </form>

    <!-- TOMBOL KEMBALI -->
    <a href="dashboard.php" class="btn-link back-btn">
        Kembali ke Dashboard
    </a>
</div>

</body>
</html>