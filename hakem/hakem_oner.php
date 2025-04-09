<?php
require_once '../includes/db_connection.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$makale_id = $_POST['makale_id'] ?? die("Makale ID belirtilmedi!");

$query = $conn->prepare("SELECT anahtar_kelimeler FROM makaleler WHERE id = ?");
$query->bind_param("i", $makale_id);
$query->execute();
$result = $query->get_result();
$makale = $result->fetch_assoc();

if (!$makale) {
    die("Makale bulunamadı!");
}

$makale_kelimeler = explode(', ', strtolower($makale['anahtar_kelimeler']));

$stop_kelimeler = ['the', 'and', 'of', 'to', 'in', 'a', 'is', 'it', 'that', 'this', 'for', 'on', 'with', 'as', 'at', 'by'];
$makale_kelimeler = array_diff($makale_kelimeler, $stop_kelimeler);

$hakem_query = $conn->query("SELECT id, ad_soyad, uzmanlik_alani FROM hakemler");
$hakemler = [];

while ($hakem = $hakem_query->fetch_assoc()) {
    $uzmanlik_kelimeleri = explode(', ', strtolower($hakem['uzmanlik_alani']));
    $eslesme_skoru = count(array_intersect($makale_kelimeler, $uzmanlik_kelimeleri));

    if ($eslesme_skoru > 0) {
        $hakemler[] = [
            'id' => $hakem['id'],
            'ad_soyad' => $hakem['ad_soyad'],
            'eslesme_skoru' => $eslesme_skoru
        ];
    }
}

usort($hakemler, function ($a, $b) {
    return $b['eslesme_skoru'] - $a['eslesme_skoru'];
});

$atanan_hakemler = array_slice($hakemler, 0, 3);

if (!empty($atanan_hakemler)) {
    foreach ($atanan_hakemler as $hakem) {
        echo "Makale, " . $hakem['ad_soyad'] . " hakemine atandı (Eşleşme Skoru: " . $hakem['eslesme_skoru'] . ")<br>";

        $insert = $conn->prepare("INSERT INTO hakem_atamalari (makale_id, hakem_id) VALUES (?, ?)");
        $insert->bind_param("ii", $makale_id, $hakem['id']);
        $insert->execute();
    }
} else {
    echo "Makale için uygun hakem bulunamadı!";
}
?>
