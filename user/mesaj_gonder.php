<?php
require_once '../includes/db_connection.php';
require_once '../includes/security_functions.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $yazar_eposta = isset($_POST['yazar_eposta']) ? sanitize_input($_POST['yazar_eposta']) : null;
    $mesaj = isset($_POST['mesaj']) ? sanitize_input($_POST['mesaj']) : null;
    $takip_numarasi = isset($_POST['takip_numarasi']) ? sanitize_input($_POST['takip_numarasi']) : "Belirtilmemiş";

    if (!$yazar_eposta || !$mesaj) {
        die("<p style='color: red;'>Hata: Yazar e-posta ve mesaj alanları zorunludur!</p>");
    }

    $query = $conn->prepare("INSERT INTO mesajlar (yazar_eposta, takip_numarasi, mesaj, tarih) VALUES (?, ?, ?, NOW())");
    if (!$query) {
        die("<p style='color: red;'>SQL hatası: " . $conn->error . "</p>");
    }
    $query->bind_param("sss", $yazar_eposta, $takip_numarasi, $mesaj);

    if ($query->execute()) {
        echo "<p style='color: green;'>Mesaj başarıyla gönderildi!</p>";
    } else {
        echo "<p style='color: red;'>Hata: Mesaj gönderilemedi!</p>";
    }
} else {
    die("<p style='color: red;'>Hata: Geçersiz istek metodu!</p>");
}
?>