<?php
require_once '../includes/db_connection.php';
require_once '../vendor/autoload.php';


$makale_id = isset($_GET['id']) ? (int) $_GET['id'] : die("Makale ID belirtilmedi!");


$stmt = $conn->prepare("SELECT dosya_yolu FROM makaleler WHERE id = ?");
$stmt->bind_param("i", $makale_id);
$stmt->execute();
$res = $stmt->get_result();
$makale = $res->fetch_assoc();

if (!$makale) {
    die("Makale bulunamadı veya dosya_yolu boş.");
}


$original_pdf_relative = $makale['dosya_yolu']; 
$original_pdf = __DIR__ . '/../user/' . $original_pdf_relative; 

if (!file_exists($original_pdf)) {
    die("Orijinal PDF bulunamadı: $original_pdf");
}


$yorum_query = $conn->prepare("SELECT * FROM degerlendirmeler WHERE makale_id = ?");
$yorum_query->bind_param("i", $makale_id);
$yorum_query->execute();
$yorum_result = $yorum_query->get_result();


ob_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hakem Yorumları</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        h1, h2 { color: #333; }
        .yorum-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>📝 Hakem Yorumları</h2>
    <hr>
    <?php if ($yorum_result->num_rows > 0): ?>
        <?php while ($yorum = $yorum_result->fetch_assoc()): ?>
            <div class="yorum-box">
                <p><strong>Yorum:</strong><br><?= nl2br(htmlspecialchars($yorum['degerlendirme'])) ?></p>
                <p><strong>Ek Açıklama:</strong><br><?= nl2br(htmlspecialchars($yorum['ek_aciklama'])) ?></p>
                <p><strong>Puan:</strong> <?= (int)$yorum['puan'] ?>/100</p>
                <p><strong>Tarih:</strong> <?= $yorum['tarih'] ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color:red;">Henüz değerlendirme yapılmamış.</p>
    <?php endif; ?>
</body>
</html>
<?php
$html = ob_get_clean();

$yorum_pdf_yolu = "../temp/hakem_yorum_$makale_id.pdf";
if (!file_exists(dirname($yorum_pdf_yolu))) {
    mkdir(dirname($yorum_pdf_yolu), 0777, true);
}


$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output($yorum_pdf_yolu, \Mpdf\Output\Destination::FILE);


$final_pdf = "../final_dosyalar/makale_final_$makale_id.pdf";

if (!file_exists(dirname($final_pdf))) {
    mkdir(dirname($final_pdf), 0777, true);
}


exec("python ../scripts/pdf_merge.py " 
    . escapeshellarg($original_pdf) . " " 
    . escapeshellarg($yorum_pdf_yolu) . " " 
    . escapeshellarg($final_pdf), 
    $output, 
    $return_var
);

if ($return_var !== 0) {
    die("❌ PDF birleştirme başarısız!");
}


$update = $conn->prepare("UPDATE makaleler SET final_dosya_yolu = ? WHERE id = ?");
$update->bind_param("si", $final_pdf, $makale_id);
$update->execute();

echo "<p style='color: green;'>✅ Final PDF oluşturuldu ve kaydedildi!</p>";
echo "<a href='$final_pdf' download>📥 PDF'yi indir</a> | ";
echo "<a href='yonetici_paneli.php'>🔙 Yöneticiye Dön</a>";
