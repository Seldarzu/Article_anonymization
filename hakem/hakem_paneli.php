<?php
require_once '../includes/db_connection.php';

$hakem_id = $_GET['hakem_id'] ?? die("Hakem ID belirtilmedi!");

$query = $conn->prepare("SELECT m.id, m.baslik, m.anonim_dosya_yolu 
                         FROM hakem_atamalari ha 
                         JOIN makaleler m ON ha.makale_id = m.id 
                         WHERE ha.hakem_id = ?");
$query->bind_param("i", $hakem_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hakem Paneli</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .makale-box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 10px;
        }
        .yonetici-link {
            margin-top: 20px;
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>📄 Değerlendirilecek Makaleler</h2>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="makale-box">
            <h3><?= htmlspecialchars($row['baslik']) ?></h3>
            <p>
                <a href="../<?= htmlspecialchars($row['anonim_dosya_yolu']) ?>" target="_blank">
                    📥 Anonim Makaleyi Görüntüle
                </a>
            </p>
            
            <form action="degerlendir.php" method="post">
                <input type="hidden" name="makale_id" value="<?= (int)$row['id'] ?>">
                <input type="hidden" name="hakem_id" value="<?= (int)$hakem_id ?>">
                <button type="submit">Değerlendir</button>
            </form>
        </div>
    <?php endwhile; ?>
    
   
   
</body>
</html>
