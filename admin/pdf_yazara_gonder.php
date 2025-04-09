<?php
require_once '../vendor/autoload.php';
require_once '../includes/db_connection.php';

use Mpdf\Mpdf;


$makale_id = $_GET['id'] ?? die("ID yok");

$query = $conn->prepare("SELECT * FROM makaleler WHERE id=?");
$query->bind_param("i", $makale_id);
$query->execute();
$makale = $query->get_result()->fetch_assoc();


$yorumlar = $conn->query("SELECT * FROM degerlendirmeler WHERE makale_id=$makale_id");


$mpdf = new Mpdf();
$mpdf->WriteHTML("<h2>Makale Başlığı: " . htmlspecialchars($makale['baslik']) . "</h2>");
$mpdf->WriteHTML("<hr><h3>Yazar İçeriği:</h3>");
$mpdf->WriteHTML("<p>" . nl2br(htmlspecialchars($makale['icerik'])) . "</p>");
$mpdf->WriteHTML("<hr><h3>🔍 Hakem Yorumları:</h3>");

while ($y = $yorumlar->fetch_assoc()) {
    $mpdf->WriteHTML("<p><strong>Yorum:</strong> " . nl2br(htmlspecialchars($y['degerlendirme'])) . "</p>");
    if ($y['ek_aciklama']) {
        $mpdf->WriteHTML("<p><em>Ek Açıklama:</em> " . nl2br(htmlspecialchars($y['ek_aciklama'])) . "</p>");
    }
    $mpdf->WriteHTML("<hr>");
}


$mpdf->Output("makale_hakemli_$makale_id.pdf", \Mpdf\Output\Destination::DOWNLOAD);
?>