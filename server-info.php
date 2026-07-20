<?php
$versi = phpversion();
$zipAktif = extension_loaded('zip') ? "✅ Aktif" : "❌ Tidak Aktif";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Versi PHP & ZIP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        h1 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }
        .version, .zip-status {
            font-size: 20px;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            margin: 8px 0;
        }
        .version {
            color: #007bff;
            background: #eaf2ff;
        }
        .zip-status {
            color: #28a745;
            background: #e6f9ee;
        }
        .zip-status.off {
            color: #dc3545;
            background: #fdeaea;
        }
        footer {
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Informasi Server</h1>
        <div class="version">PHP: <?php echo $versi; ?></div>
        <div class="zip-status <?php echo extension_loaded('zip') ? '' : 'off'; ?>">
            Ekstensi ZIP: <?php echo $zipAktif; ?>
        </div>
        <footer>&copy; <?php echo date("Y"); ?> - Info Server</footer>
    </div>
</body>
</html>
