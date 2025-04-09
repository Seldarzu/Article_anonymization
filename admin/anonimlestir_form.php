<?php
require_once '../includes/db_connection.php';

$makale_id = $_GET['id'] ?? die("Makale ID belirtilmedi!");


$query = $conn->prepare("SELECT * FROM makaleler WHERE id = ?");
$query->bind_param("i", $makale_id);
$query->execute();
$result = $query->get_result();
$makale = $result->fetch_assoc();

if (!$makale) {
    die("Makale bulunamadı!");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Anonimleştirme Formu</title>
</head>
<body>
    <h2>Makale Anonimleştirme</h2>
    <form action="anonimlestir.php" method="POST">
        <input type="hidden" name="makale_id" value="<?= $makale_id ?>">

        <label><input type="checkbox" name="anon_fields[]" value="author_name"> Yazar Adı Anonimleştir</label><br>
        <label><input type="checkbox" name="anon_fields[]" value="author_email"> Yazar E-postası Anonimleştir</label><br>
        <label><input type="checkbox" name="anon_fields[]" value="author_affiliation"> Yazar Kurumu Anonimleştir</label><br>
        <a href="index.php">Anasayfaya Dön</a>

        <button type="submit">Anonimleştir</button>
    </form>
</body>
</html>
