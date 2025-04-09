<?php
require_once '../includes/db_connection.php';

$makale_id = $_POST['makale_id'] ?? $_GET['makale_id'] ?? die("Makale ID belirtilmedi!");


$query = $conn->prepare("SELECT anahtar_kelimeler FROM makaleler WHERE id = ?");
$query->bind_param("i", $makale_id);
$query->execute();
$result = $query->get_result();
$makale = $result->fetch_assoc();

if (!$makale || empty($makale['anahtar_kelimeler'])) {
    die("Anahtar kelimeler bulunamadı.");
}

$keywords = explode(',', strtolower($makale['anahtar_kelimeler']));
$stop_words = ['the','and','of','to','in','a','is','it','this','that','we','our'];
$keywords = array_diff(array_map('trim', $keywords), $stop_words);


$hakemler = $conn->query("SELECT id, ad_soyad, uzmanlik_alani FROM hakemler");
$eslesen = [];

while ($hakem = $hakemler->fetch_assoc()) {
    $hakem_keywords = explode(',', strtolower($hakem['uzmanlik_alani']));
    $score = count(array_intersect($keywords, $hakem_keywords));
    if ($score > 0) {
        $eslesen[] = ['id' => $hakem['id'], 'ad_soyad' => $hakem['ad_soyad'], 'puan' => $score];
    }
}

if (empty($eslesen)) {
    die("Uygun hakem bulunamadı.");
}

usort($eslesen, fn($a, $b) => $b['puan'] <=> $a['puan']);
$hakem = $eslesen[0];


$kontrol = $conn->prepare("SELECT id FROM hakem_atamalari WHERE makale_id = ? AND hakem_id = ?");
$kontrol->bind_param("ii", $makale_id, $hakem['id']);
$kontrol->execute();
$kontrol_result = $kontrol->get_result();

if ($kontrol_result->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO hakem_atamalari (makale_id, hakem_id) VALUES (?, ?)");
    $insert->bind_param("ii", $makale_id, $hakem['id']);
    $insert->execute();

    $update = $conn->prepare("UPDATE makaleler SET hakem_kodu = ? WHERE id = ?");
    $hakem_kodu = $hakem['id'];
    $update->bind_param("si", $hakem_kodu, $makale_id);
    $update->execute();

    echo "✅ Hakem atandı: {$hakem['ad_soyad']} (#{$hakem['id']})";
} else {
    echo "⚠️ Bu hakem zaten atanılmış.";
}

$log = $conn->prepare("INSERT INTO loglar (makale_id, islem) VALUES (?, ?)");
$islem = "{$hakem['ad_soyad']} hakemi atandı";
$log->bind_param("is", $makale_id, $islem);
$log->execute();
?>
