<?php
require_once '../includes/db_connection.php';
require_once '../includes/security_functions.php';


$makale_id = isset($_GET['makale_id']) ? (int) $_GET['makale_id'] : die("Makale ID belirtilmedi!");


$makale_query = $conn->prepare("SELECT baslik FROM makaleler WHERE id = ?");
$makale_query->bind_param("i", $makale_id);
$makale_query->execute();
$makale_result = $makale_query->get_result();
$makale = $makale_result->fetch_assoc();

if (!$makale) {
    die("Makale bulunamadı!");
}


$yorum_query = $conn->prepare("SELECT * FROM degerlendirmeler WHERE makale_id = ?");
$yorum_query->bind_param("i", $makale_id);
$yorum_query->execute();
$yorum_result = $yorum_query->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hakem Yorumları</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>🧾 <?= htmlspecialchars($makale['baslik']) ?> - Hakem Yorumları</h1>

        <?php if ($yorum_result->num_rows > 0): ?>
            <?php while ($yorum = $yorum_result->fetch_assoc()): ?>
                <div class="yorum-kutusu" style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
                    <p><strong>Yorum:</strong> <?= nl2br(htmlspecialchars($yorum['degerlendirme'])) ?></p>
                    <p><strong>Ek Açıklama:</strong> <?= nl2br(htmlspecialchars($yorum['ek_aciklama'])) ?></p>
                    <p><strong>Puan:</strong> <?= $yorum['puan'] ?>/100</p>
                    <p><strong>Tarih:</strong> <?= $yorum['tarih'] ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:red;">❌ Bu makaleye ait herhangi bir hakem değerlendirmesi bulunmamaktadır.</p>
        <?php endif; ?>

        <br>
        <a href="yonetici_paneli.php">🔙 Yönetici Paneline Dön</a>
    </div>
</body>
</html>

