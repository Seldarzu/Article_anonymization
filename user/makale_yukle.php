<?php
require_once '../includes/db_connection.php';
require_once '../includes/security_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslik = sanitize_input($_POST['baslik']);
    $yazar = sanitize_input($_POST['yazar']);
    $yazar_eposta = sanitize_input($_POST['yazar_eposta']);
    $dosya_yolu = 'uploads/' . basename($_FILES['makale_dosya']['name']);

    if (!validate_file($_FILES['makale_dosya'])) {
        die("Geçersiz dosya formatı veya boyutu!");
    }

    move_uploaded_file($_FILES['makale_dosya']['tmp_name'], $dosya_yolu);

    $takip_numarasi = strtoupper(bin2hex(random_bytes(4)));

    $query = $conn->prepare("INSERT INTO makaleler (baslik, yazar, yazar_eposta, dosya_yolu, takip_numarasi, durum) VALUES (?, ?, ?, ?, ?, 'Beklemede')");
    $query->bind_param("sssss", $baslik, $yazar, $yazar_eposta, $dosya_yolu, $takip_numarasi);
    $query->execute();

    echo "<p style='color: green;'>Makale başarıyla yüklendi! Takip numaranız: <strong>$takip_numarasi</strong></p>";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Makale Yükle</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>Makale Yükle</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Başlık:</label>
        <input type="text" name="baslik" required><br>
        <label>Yazar Adı:</label>
        <input type="text" name="yazar" required><br>
        <label>Yazar E-Posta:</label>
        <input type="email" name="yazar_eposta" required><br>
        <label>Makale Dosyası:</label>
        <input type="file" name="makale_dosya" required><br>
        <button type="submit">Makale Yükle</button>
    </form>
</body>
</html>