<?php
require_once '../includes/db_connection.php';
require_once '../includes/encryption_functions.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

$makale_id = $_POST['makale_id'] ?? die("Makale ID belirtilmedi!");


$query = $conn->prepare("SELECT anonim_dosya_yolu FROM makaleler WHERE id = ?");
$query->bind_param("i", $makale_id);
$query->execute();
$result = $query->get_result();
$makale = $result->fetch_assoc();

if (!$makale || !file_exists($makale['anonim_dosya_yolu'])) {
    die("Anonim PDF dosyası bulunamadı!");
}

$anonim_pdf = $makale['anonim_dosya_yolu'];


$yorum_query = $conn->prepare("SELECT h.ad_soyad, dy.yorum, dy.puan FROM degerlendirme_yonet dy JOIN hakemler h ON dy.hakem_id = h.id WHERE dy.makale_id = ?");
$yorum_query->bind_param("i", $makale_id);
$yorum_query->execute();
$yorum_result = $yorum_query->get_result();
$yorumlar = $yorum_result->fetch_all(MYSQLI_ASSOC);

$yorum_metin = "<h3>--- HAKEM DEĞERLENDİRMELERİ ---</h3>\n";
foreach ($yorumlar as $yorum) {
    $yorum_metin .= "<strong>Hakem:</strong> " . htmlspecialchars($yorum['ad_soyad']) . "<br>";
    $yorum_metin .= "<strong>Puan:</strong> " . htmlspecialchars($yorum['puan']) . "/10<br>";
    $yorum_metin .= "<strong>Yorum:</strong> " . nl2br(htmlspecialchars(decrypt_yorum($yorum['yorum']))) . "<br><hr>";
}

// Orijinal anonim PDF'i oku
$mpdf = new Mpdf();
$mpdf->SetImportUse();
$pagecount = $mpdf->SetSourceFile($anonim_pdf);
for ($i = 1; $i <= $pagecount; $i++) {
    $tpl = $mpdf->ImportPage($i);
    $mpdf->AddPage();
    $mpdf->UseTemplate($tpl);
}

// Yorum sayfasını ekle
$mpdf->AddPage();
$mpdf->WriteHTML($yorum_metin);

// Yeni dosya yolunu belirle
$output_pdf = "../anonim_icerikler/rapor_makale_" . $makale_id . ".pdf";
$mpdf->Output($output_pdf, \Mpdf\Output\Destination::FILE);

// Log ekle
$log = $conn->prepare("INSERT INTO loglar (makale_id, islem) VALUES (?, 'Hakem yorumları PDF'e eklendi')");
$log->bind_param("i", $makale_id);
$log->execute();

echo "<p style='color:green;'>\u2705 Rapor PDF oluşturuldu!</p>";
echo "<a href='$output_pdf' download>Rapor PDF'yi İndir</a>";
?>
