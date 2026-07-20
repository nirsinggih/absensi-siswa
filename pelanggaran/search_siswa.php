<?php
include "../config.php";

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : "";

$sql = "SELECT id, nama FROM siswa WHERE nama LIKE '%$q%' ORDER BY nama LIMIT 10";
$res = $conn->query($sql);

while($row = $res->fetch_assoc()){
    echo "<div class='autocomplete-item' onclick=\"pilihSiswa('{$row['id']}', '{$row['nama']}')\">{$row['nama']}</div>";
}
