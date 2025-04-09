<?php
require_once '../includes/db_connection.php';
require_once '../vendor/autoload.php';


$makale_id = isset($_GET['makale_id']) ? (int)$_GET['makale_id'] : die("Makale ID belirtilmedi.");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Makale Değerlendir</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Makale Değerlendir</h1>
        <form action="degerlendirme_yonet.php" method="post">
           
            <input type="hidden" name="makale_id" value="<?= $makale_id ?>">
            <label for="puan">Puan (0-100):</label><br>
            <input type="number" name="puan" min="0" max="100" required><br><br>
            <label for="degerlendirme">Yorum:</label><br>
            <textarea name="degerlendirme" rows="6" cols="50" required></textarea><br><br>
            <button type="submit">Değerlendir</button>
        </form>
       
        <p><a href="../index.php">Anasayfaya Dön</a></p>
    </div>
</body>
</html>
