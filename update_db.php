<?php
include "config.php"; // koneksi DB
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pembaruan Database</title>
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 15px;
        background: #f4f6f9;
        color: #333;
        max-width: 600px;
        margin: auto;
    }
    h2 { text-align: center; }
    .log {
        background: #fff;
        margin: 6px 0;
        padding: 10px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        font-size: 14px;
        word-wrap: break-word;
    }
    .success { background: #d4edda; color: #155724; }
    .error   { background: #f8d7da; color: #721c24; }
    .btn {
        display: block;
        margin: 20px auto 0;
        padding: 10px 18px;
        border-radius: 6px;
        background: #007bff;
        color: white;
        text-decoration: none;
        font-size: 16px;
        text-align: center;
        max-width: 250px;
    }
    .btn:hover { background: #0056b3; }
</style>
</head>
<body>
<h2>🔧 Proses Pembaruan Database</h2>
<div>
<?php
/* === Tambah kolom no_wa di tabel siswa === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM siswa LIKE 'no_wa'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE siswa ADD no_wa VARCHAR(20) AFTER status";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>no_wa</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom no_wa: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>no_wa</b> sudah ada.</div>";
}

/* === Tambah kolom rfid_uid di tabel siswa === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM siswa LIKE 'rfid_uid'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE siswa ADD rfid_uid VARCHAR(50) AFTER no_wa";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>rfid_uid</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom rfid_uid: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>rfid_uid</b> sudah ada.</div>";
}

/* === Tambah kolom foto_siswa di tabel siswa === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM siswa LIKE 'foto_siswa'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE siswa ADD foto_siswa VARCHAR(255) AFTER rfid_uid";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>foto_siswa</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom foto_siswa: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>foto_siswa</b> sudah ada.</div>";
}

/* === Tambah kolom nama di tabel users === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'nama'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE users ADD nama VARCHAR(100) AFTER password";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>nama</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom nama: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>nama</b> sudah ada.</div>";
}

/* === Tambah kolom role di tabel users === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE users ADD role VARCHAR(50) AFTER nama";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>role</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom role: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>role</b> sudah ada.</div>";
}

/* === Tambah kolom key_wa_sidobe di tabel profil_sekolah === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM profil_sekolah LIKE 'key_wa_sidobe'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE profil_sekolah ADD key_wa_sidobe VARCHAR(255) AFTER nama_sekolah";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>key_wa_sidobe</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom key_wa_sidobe: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>key_wa_sidobe</b> sudah ada.</div>";
}

/* === Tambah kolom background_kartu di tabel profil_sekolah === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM profil_sekolah LIKE 'background_kartu'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE profil_sekolah ADD background_kartu VARCHAR(255) AFTER logo";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>background_kartu</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom background_kartu: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>background_kartu</b> sudah ada.</div>";
}

/* === Tambah kolom jam_masuk di tabel profil_sekolah === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM profil_sekolah LIKE 'jam_masuk'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE profil_sekolah ADD jam_masuk TIME NULL DEFAULT NULL AFTER key_wa_sidobe";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>jam_masuk</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom jam_masuk: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>jam_masuk</b> sudah ada.</div>";
}

/* === Tambah kolom jam_pulang di tabel profil_sekolah === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM profil_sekolah LIKE 'jam_pulang'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE profil_sekolah ADD jam_pulang TIME NULL DEFAULT NULL AFTER jam_masuk";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>jam_pulang</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom jam_pulang: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>jam_pulang</b> sudah ada.</div>";
}

/* === Update role user admin dan wali === */
mysqli_query($conn, "UPDATE users SET role = 'admin' WHERE username = 'admin'");
mysqli_query($conn, "UPDATE users SET role = 'wali' WHERE username = 'wali'");

/* === Tambahkan user guru jika belum ada === */
$check = mysqli_query($conn, "SELECT * FROM users WHERE username='guru'");
if (mysqli_num_rows($check) == 0) {
    $sql = "INSERT INTO users (username, password, nama, role)
            VALUES ('guru', MD5('guru'), 'guru', 'guru')";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>User <b>guru</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah user guru: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>User <b>guru</b> sudah ada.</div>";
}

/* === Tambah kolom jam_pulang di tabel absensi === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM absensi LIKE 'jam_pulang'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE absensi ADD jam_pulang TIME NULL DEFAULT NULL AFTER jam";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>jam_pulang</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom jam_pulang (absensi): " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>jam_pulang</b> di tabel absensi sudah ada.</div>";
}
	/* === Tambah kolom jam_dzuhur di tabel absensi === */
$check = mysqli_query($conn, "SHOW COLUMNS FROM absensi LIKE 'jam_dzuhur'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE absensi ADD jam_dzuhur TIME NULL DEFAULT NULL AFTER jam_pulang";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='log success'>Kolom <b>jam_dzuhur</b> berhasil ditambahkan!</div>";
    } else {
        echo "<div class='log error'>Error tambah kolom jam_dzuhur (absensi): " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='log'>Kolom <b>jam_dzuhur</b> di tabel absensi sudah ada.</div>";
}

?>
</div>
<a href="dashboard.php" class="btn">⬅️ Kembali ke Dashboard</a>
</body>
</html>
