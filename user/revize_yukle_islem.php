<?php
require_once '../includes/db_connection.php';
require_once '../vendor/autoload.php';


session_start();
if (!isset($_SESSION['user_id'])) {
    die("Yetkisiz erişim! Lütfen giriş yapınız.");
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $makale_id = (int)$_POST['makale_id'];

    if (isset($_FILES['revize_dosyasi'])) {
        $dosya = $_FILES['revize_dosyasi'];
        $dosyaAdi = "revize_makale_{$makale_id}_" . time() . ".pdf";
        $hedefKlasor = '../revize_dosyalar/';
        
        if (!file_exists($hedefKlasor)) {
            mkdir($hedefKlasor, 0777, true);
        }

        $hedefYolu = $hedefKlasor . $dosyaAdi;
        if (move_uploaded_file($dosya['tmp_name'], $hedefYolu)) {
            $stmt = $conn->prepare("UPDATE makaleler SET revize_dosya_yolu = ?, durum = 'Revize Gönderildi' WHERE id = ?");
            $stmt->bind_param('si', $hedefYolu, $makale_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<p style='color: green;'>✅ Revize dosyası başarıyla yüklendi.</p>";
            } else {
                echo "<p style='color: red;'>❌ Veritabanına kaydedilemedi.</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Dosya yüklenemedi.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Dosya seçilmedi.</p>";
    }
}
?>
<a href="makale_sorgula.php">🔙 Makale Sorgula / Revize Gönder</a>
