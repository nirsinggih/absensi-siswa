<?php
include "config.php";
date_default_timezone_set("Asia/Jakarta");

if (isset($_GET['rfid_uid'])) {

    $uid = $conn->real_escape_string($_GET['rfid_uid']);

    // Cari data siswa berdasarkan UID RFID
    $q = $conn->query("SELECT * FROM siswa WHERE rfid_uid='$uid' LIMIT 1");

    if ($q && $q->num_rows > 0) {

        $user   = $q->fetch_assoc();
        $userId = (int) $user['id'];
        $nama   = $user['nama'];
        $kelas  = $user['kelas'];
        $no_wa  = $user['no_wa'] ?? '';

        $today  = date("Y-m-d");
        $jam    = date("H:i:s");
        $jamDisp = date("H:i");

        // ==================================================
        // 1. CEK HARI LIBUR
        // ==================================================
        $cekLibur = $conn->query(
            "SELECT tanggal FROM hari_libur
             WHERE tanggal='$today' LIMIT 1"
        );

        if ($cekLibur && $cekLibur->num_rows > 0) {
            echo "<div class='alert alert-danger'>
                    ⛔ Hari ini libur!
                  </div>";
            exit;
        }

        // ==================================================
        // 2. NORMALISASI NOMOR WHATSAPP
        // ==================================================
        // Contoh:
        // 081234567890  -> +6281234567890
        // 6281234567890 -> +6281234567890
        // +6281234567890 -> +6281234567890

        $no_wa = preg_replace('/\D/', '', $no_wa);

        if (substr($no_wa, 0, 1) === '0') {
            $no_wa = '62' . substr($no_wa, 1);
        } elseif (substr($no_wa, 0, 2) !== '62') {
            if (substr($no_wa, 0, 1) === '8') {
                $no_wa = '62' . $no_wa;
            } else {
                $no_wa = '';
            }
        }

        // Pastikan nomor diawali +62
        if ($no_wa !== '' && substr($no_wa, 0, 2) === '62') {
            $no_wa = '+' . $no_wa;
        } else {
            $no_wa = '';
        }

        // ==================================================
        // 3. AMBIL SECRET KEY SIDOBE
        // ==================================================
        $secretKey = '';

        $qKey = $conn->query(
            "SELECT key_wa_sidobe
             FROM profil_sekolah
             LIMIT 1"
        );

        if ($qKey && $qKey->num_rows > 0) {
            $rowKey = $qKey->fetch_assoc();
            $secretKey = trim($rowKey['key_wa_sidobe'] ?? '');
        }

        // ==================================================
        // 4. FUNGSI KIRIM WHATSAPP
        // ==================================================
        $kirimWA = function ($pesan) use ($no_wa, $secretKey) {

            if (empty($no_wa)) {
                return "⚠️ Nomor WA orang tua/wali belum diisi atau tidak valid.";
            }

            if (empty($secretKey)) {
                return "⚠️ Secret key WA Sidobe belum diatur.";
            }

            if (!function_exists('curl_init')) {
                return "⚠️ Ekstensi cURL PHP belum tersedia.";
            }

            $data = [
                'phone'   => $no_wa,
                'message' => $pesan
            ];

            $ch = curl_init(
                'https://api.sidobe.com/wa/v1/send-message'
            );

            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Secret-Key: ' . $secretKey
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE
                ),
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 20
            ]);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($response === false) {
                error_log("Sidobe cURL error: " . $curlError);
                return "⚠️ Gagal menghubungi layanan WhatsApp.";
            }

            $resData = json_decode($response, true);

            if (
                $httpCode >= 200 &&
                $httpCode < 300 &&
                is_array($resData) &&
                !empty($resData['is_success'])
            ) {
                return "📲 Notifikasi WhatsApp berhasil dikirim.";
            }

            // Detail respons disimpan di log server.
            // Jangan tampilkan respons API atau secret key kepada pengguna.
            error_log(
                "Sidobe API gagal. HTTP: " . $httpCode .
                ". Response: " . $response
            );

            return "⚠️ Notifikasi WhatsApp gagal dikirim.";
        };

        // ==================================================
        // 5. CEK ABSENSI HARI INI
        // ==================================================
        $cek = $conn->query(
            "SELECT id, jam, jam_pulang
             FROM absensi
             WHERE siswa_id='$userId'
             AND tanggal='$today'
             LIMIT 1"
        );

        if (!$cek) {
            echo "<div class='alert alert-danger'>
                    ❌ Gagal memeriksa data absensi.
                  </div>";
            exit;
        }

        // Pesan untuk ditampilkan di halaman
        $namaHTML = htmlspecialchars(
            $nama,
            ENT_QUOTES,
            'UTF-8'
        );

        $kelasHTML = htmlspecialchars(
            $kelas,
            ENT_QUOTES,
            'UTF-8'
        );

        // ==================================================
        // 6. ABSEN MASUK
        // ==================================================
        if ($cek->num_rows == 0) {

            $insert = $conn->query(
                "INSERT INTO absensi
                    (siswa_id, tanggal, jam, status)
                 VALUES
                    ('$userId', '$today', '$jam', 'H')"
            );

            if (!$insert) {
                echo "<div class='alert alert-danger'>
                        ❌ Gagal menyimpan absensi masuk.
                      </div>";
                exit;
            }

            $pesanWA =
                "Halo, Orang tua/wali dari $nama (kelas $kelas).\n\n" .
                "Putra/putri Bapak/Ibu telah *HADIR* di sekolah " .
                "pada tanggal $today pukul $jamDisp WIB.";

            $statusWA = $kirimWA($pesanWA);

            echo "<div class='alert alert-success'>
                    ✅ Selamat datang, $namaHTML ($kelasHTML)!
                    <br>🕒 Jam hadir: $jamDisp WIB
                    <hr>
                    " . htmlspecialchars(
                        $statusWA,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . "
                  </div>";

        } else {

            $row = $cek->fetch_assoc();

            // ==============================================
            // 7. ABSEN PULANG
            // ==============================================
            if (
                is_null($row['jam_pulang']) &&
                $jam >= "09:00:00"
            ) {

                $absenId = (int) $row['id'];

                $update = $conn->query(
                    "UPDATE absensi
                     SET jam_pulang='$jam'
                     WHERE id='$absenId'
                     AND siswa_id='$userId'
                     AND tanggal='$today'
                     AND jam_pulang IS NULL"
                );

                if (!$update || $conn->affected_rows !== 1) {
                    echo "<div class='alert alert-danger'>
                            ❌ Gagal menyimpan jam pulang.
                            Silakan periksa data absensi.
                          </div>";
                    exit;
                }

                $pesanWA =
                    "Halo, Orang tua/wali dari $nama (kelas $kelas).\n\n" .
                    "Putra/putri Bapak/Ibu telah *PULANG* dari sekolah " .
                    "pada tanggal $today pukul $jamDisp WIB.";

                $statusWA = $kirimWA($pesanWA);

                echo "<div class='alert alert-warning'>
                        👋 Sampai jumpa, $namaHTML ($kelasHTML)!
                        <br>🕒 Jam pulang: $jamDisp WIB
                        <hr>
                        " . htmlspecialchars(
                            $statusWA,
                            ENT_QUOTES,
                            'UTF-8'
                        ) . "
                      </div>";

            } else {

                // ==========================================
                // 8. SUDAH ABSEN HARI INI
                // Tidak mengirim WA ulang
                // ==========================================
                $jamMasukHTML = htmlspecialchars(
                    substr($row['jam'], 0, 5),
                    ENT_QUOTES,
                    'UTF-8'
                );

                echo "<div class='alert alert-info'>
                        ℹ️ $namaHTML sudah absen hari ini.
                        <br>🕒 Jam hadir: $jamMasukHTML WIB";

                if (!is_null($row['jam_pulang'])) {

                    $jamPulangHTML = htmlspecialchars(
                        substr($row['jam_pulang'], 0, 5),
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    echo "<br>🕒 Jam pulang: $jamPulangHTML WIB";
                }

                echo "</div>";
            }
        }

    } else {

        echo "<div class='alert alert-danger'>
                ❌ UID tidak dikenal!
              </div>";
    }

} else {

    echo "<div class='alert alert-danger'>
            ❌ UID kosong!
          </div>";
}
?>