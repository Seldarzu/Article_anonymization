<?php
require_once '../includes/db_connection.php';
require_once '../includes/uzmanlik_kelime_haritasi.php'; 

$makale_id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$makale_id) {
    die("Makale ID belirtilmedi.");
}

$query = $conn->prepare("SELECT anahtar_kelimeler FROM makaleler WHERE id = ?");
$query->bind_param("i", $makale_id);
$query->execute();
$result = $query->get_result();
$makale = $result->fetch_assoc();

if (!$makale || empty($makale['anahtar_kelimeler'])) {
    die("Makale için anahtar kelime bulunamadı.");
}

$makale_kelimeler = explode(',', strtolower($makale['anahtar_kelimeler']));
$makale_kelimeler = array_map('trim', $makale_kelimeler);


$hakemler = $conn->query("SELECT id, ad_soyad, uzmanlik_alani FROM hakemler");

$en_uygun_hakem = null;
$en_yuksek_puan = 0;

while ($hakem = $hakemler->fetch_assoc()) {
    $uzmanlik = $hakem['uzmanlik_alani'];

    if (!isset($UZMANLIK_HARITASI[$uzmanlik])) continue;

    $anahtarlar = $UZMANLIK_HARITASI[$uzmanlik];
    $puan = 0;

    foreach ($makale_kelimeler as $kelime) {
        foreach ($anahtarlar as $anahtar) {
            similar_text($kelime, $anahtar, $yuzde);
            if ($yuzde > 65) {
                $puan++;
            }
        }
    }

    if ($puan > $en_yuksek_puan) {
        $en_yuksek_puan = $puan;
        $en_uygun_hakem = $hakem;
    }
}

if (!$en_uygun_hakem) {
    echo "<p style='color:red; font-size:18px;'>❌ Bu makale için uygun hakem bulunamadı.</p>";
    exit;
}

$kontrol = $conn->prepare("SELECT id FROM hakem_atamalari WHERE makale_id = ? AND hakem_id = ?");
$kontrol->bind_param("ii", $makale_id, $en_uygun_hakem['id']);
$kontrol->execute();
$kontrol_result = $kontrol->get_result();

if ($kontrol_result->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO hakem_atamalari (makale_id, hakem_id) VALUES (?, ?)");
    $insert->bind_param("ii", $makale_id, $en_uygun_hakem['id']);
    $insert->execute();

    $update = $conn->prepare("UPDATE makaleler SET hakem_kodu = ? WHERE id = ?");
    $hakem_kodu = $en_uygun_hakem['id'];
    $update->bind_param("si", $hakem_kodu, $makale_id);
    $update->execute();

    echo "<p style='color:green; font-size:18px;'>✅ Atanan Hakem: {$en_uygun_hakem['ad_soyad']}</p>";
} else {
    echo "<p style='color:orange;'>⚠️ Bu hakem zaten atanmış.</p>";
}
    echo "<a href='yonetici_paneli.php'>Anasayfaya Dön</a>";
?>
