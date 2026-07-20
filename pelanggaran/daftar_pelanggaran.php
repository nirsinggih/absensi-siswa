<?php
include "../config.php";

// Ambil data dari tabel pelanggaran
$sql = "SELECT id, nama_pelanggaran, poin FROM pelanggaran ORDER BY poin DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pelanggaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2 {
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <h2>Daftar Pelanggaran & Poin</h2>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Pelanggaran</th>
            <th>Poin</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['nama_pelanggaran']}</td>
                        <td>{$row['poin']}</td>
                      </tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='3'>Belum ada data pelanggaran.</td></tr>";
        }
        ?>
    </table>
</body>
</html>
