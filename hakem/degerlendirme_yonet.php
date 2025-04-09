<?php
require_once '../includes/db_connection.php';
require_once '../vendor/autoload.php';


$hakem_id = 0; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $makale_id     = (int)$_POST['makale_id'];
    $puan          = (int)$_POST['puan'];
    $degerlendirme = trim($_POST['degerlendirme']);
    $tarih         = date('Y-m-d H:i:s');

    if ($puan < 0 || $puan > 100) {
        die("Geçersiz puan. Puan 0 ile 100 arasında olmalıdır.");
    }

    $stmt = $conn->prepare("INSERT INTO degerlendirmeler (makale_id, hakem_id, puan, degerlendirme, tarih) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iiiss', $makale_id, $hakem_id, $puan, $degerlendirme, $tarih);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<p style='color: green;'>✅ Değerlendirme başarıyla kaydedildi.</p>";
    } else {
        echo "<p style='color: red;'>❌ Değerlendirme kaydedilemedi.</p>";
    }
}
?>
<p><a href="../index.php">🔙 Anasayfaya Dön</a></p>
