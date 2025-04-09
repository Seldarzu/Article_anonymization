<?php
require_once '../includes/db_connection.php';
require_once '../includes/security_functions.php';
require_once '../vendor/autoload.php';

// Makaleleri getir
$makale_query = $conn->query("SELECT * FROM makaleler");

// Mesajları getir
$mesaj_query = $conn->query("SELECT * FROM mesajlar");

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Yönetici Paneli</h1>

    <h2>Makaleler</h2>
    <table border="1">
        <tr>
            <th>Başlık</th>
            <th>Takip Numarası</th>
            <th>Durum</th>
            <th>İşlemler</th>
        </tr>
        <?php while ($makale = $makale_query->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($makale['baslik']) ?></td>
                <td><?= htmlspecialchars($makale['takip_numarasi']) ?></td>
                <td>
                    <?= ($makale['anonim_dosya_yolu'] ? "Anonimleştirildi" : "Henüz anonimleştirilmedi.") ?>
                </td>
                <td>
                    <a href="anonimlestir.php?id=<?= $makale['id'] ?>">Anonimleştir</a> |
                    <a href="hakem_ata.php?id=<?= $makale['id'] ?>">Hakem Ata</a> |
                    <?php if ($makale['anonim_dosya_yolu']): ?>
                        <a href="<?= $makale['anonim_dosya_yolu'] ?>" target="_blank">Anonim PDF</a> |
                    <?php endif; ?>
                    <a href="hakem_yorumlarini_gor.php?makale_id=<?= $makale['id'] ?>">Hakem Yorumlarını Gör</a> |
                    <a href="pdf_final_olustur.php?id=<?= $makale['id'] ?>">PDF Son Halini Oluştur</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Gelen Mesajlar</h2>
    <table border="1">
        <tr>
            <th>Gönderen</th>
            <th>Takip Numarası</th>
            <th>Mesaj</th>
            <th>Tarih</th>
        </tr>
        <?php while ($mesaj = $mesaj_query->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($mesaj['yazar_eposta']) ?></td>
                <td><?= htmlspecialchars($mesaj['takip_numarasi']) ?></td>
                <td><?= htmlspecialchars($mesaj['mesaj']) ?></td>
                <td><?= htmlspecialchars($mesaj['tarih']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
