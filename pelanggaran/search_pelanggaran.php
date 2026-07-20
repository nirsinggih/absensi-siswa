<?php
include "../config.php";

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : "";

$sql = "SELECT id, nama_pelanggaran, poin FROM pelanggaran 
        WHERE nama_pelanggaran LIKE '%$q%' ORDER BY nama_pelanggaran LIMIT 10";
$res = $conn->query($sql);

while($row = $res->fetch_assoc()){
    echo "<div class='autocomplete-item' onclick=\"pilihPelanggaran('{$row['id']}', '{$row['nama_pelanggaran']} ({$row['poin']} poin)')\">{$row['nama_pelanggaran']} ({$row['poin']} poin)</div>";
}
