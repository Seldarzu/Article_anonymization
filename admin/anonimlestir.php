<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../includes/encryption_functions.php'; 


set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});


$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/php_errors.log');

function log_message($message, $level = 'INFO') {
    $logEntry = sprintf(
        "[%s] %s: %s\n",
        date('Y-m-d H:i:s'),
        $level,
        $message
    );
    file_put_contents(__DIR__ . '/../logs/app.log', $logEntry, FILE_APPEND);
}

log_message("anonimlestir.php betiği başlatıldı.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (empty($_POST['makale_id'])) {
            throw new RuntimeException("Makale ID gereklidir");
        }

        $makale_id = (int)$_POST['makale_id'];
        log_message("İşlem başlatıldı: $makale_id");

       
        $stmt = $conn->prepare("SELECT dosya_yolu FROM makaleler WHERE id = ?");
        if (!$stmt) {
            throw new RuntimeException("SQL Hatası: " . $conn->error);
        }
        $stmt->bind_param('i', $makale_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new RuntimeException("Makale bulunamadı");
        }
        $makale = $result->fetch_assoc();
        log_message("Veritabanından alınan dosya yolu: " . $makale['dosya_yolu']);

        
        $pdfPath = realpath("../user/" . $makale['dosya_yolu']);
        log_message("Oluşturulan PDF yolu: " . $pdfPath);
        if (!$pdfPath || !file_exists($pdfPath)) {
            throw new RuntimeException("PDF dosyası bulunamadı: $pdfPath");
        }

        
        $outputDir = realpath(__DIR__ . '/../anonim_icerikler');
        if (!$outputDir) {
            log_message("anonim_icerikler klasörü bulunamadı, oluşturuluyor...", "WARNING");
            if (!mkdir(__DIR__ . '/../anonim_icerikler', 0777, true)) {
                throw new RuntimeException("anonim_icerikler klasörü oluşturulamadı");
            }
            $outputDir = realpath(__DIR__ . '/../anonim_icerikler');
        }
        log_message("Çıktı klasörü: " . $outputDir);

        $anon_pdf_file = "makale_{$makale_id}_anonim.pdf";
        $anon_pdf_path = $outputDir . DIRECTORY_SEPARATOR . $anon_pdf_file;
        log_message("Anonim PDF dosya adı: " . $anon_pdf_file);

        
        $selectedOptions = isset($_POST['anon_options']) ? $_POST['anon_options'] : [];
        $adminOptionsStr = implode(',', $selectedOptions);
        if (empty($adminOptionsStr)) {
            $adminOptionsStr = "isim,kurum,eposta";
        }
        log_message("Anonimleştirme seçenekleri: " . $adminOptionsStr);

        
        $pythonScript = escapeshellarg(realpath(__DIR__ . '/../scripts/integrated_anonymization.py'));
        if (!$pythonScript) {
            throw new RuntimeException("Python script yolu bulunamadı.");
        }
        $inputPdfArg = escapeshellarg($pdfPath);
        
        $outputPdfArg = escapeshellarg($anon_pdf_file);
        $adminOptionsArg = escapeshellarg($adminOptionsStr);

        $command = "python $pythonScript $inputPdfArg $outputPdfArg $adminOptionsArg 2>&1";
        log_message("Oluşturulan Python komutu: " . $command);

        $output = shell_exec($command);
        log_message("Python çıktısı: " . $output);

       
        if (strpos($output, '"status":"success"') === false &&
            strpos($output, '"status": "success"') === false) {
            throw new RuntimeException("Anonimleştirme hatası: " . $output);
        }

        $relativePath = "anonim_icerikler/" . $anon_pdf_file;
        log_message("Güncellenecek anonim_dosya_yolu: " . $relativePath);

        
        $stmt2 = $conn->prepare("UPDATE makaleler SET anonim_dosya_yolu = ? WHERE id = ?");
        if (!$stmt2) {
            throw new RuntimeException("SQL Hatası (UPDATE): " . $conn->error);
        }
        $stmt2->bind_param("si", $relativePath, $makale_id);
        $stmt2->execute();
        log_message("Veritabanında anonim_dosya_yolu güncellendi.");

        
        $finalDetailsPath = $outputDir . DIRECTORY_SEPARATOR . "final_details.json";
        if (file_exists($finalDetailsPath)) {
            $jsonContent = file_get_contents($finalDetailsPath);
            $finalDetails = json_decode($jsonContent, true);
            if (isset($finalDetails['extracted_keywords']) && is_array($finalDetails['extracted_keywords'])) {
                $keywords = implode(',', $finalDetails['extracted_keywords']);
                $stmt3 = $conn->prepare("UPDATE makaleler SET anahtar_kelimeler = ? WHERE id = ?");
                if ($stmt3) {
                    $stmt3->bind_param("si", $keywords, $makale_id);
                    $stmt3->execute();
                    log_message("Veritabanında anahtar_kelimeler güncellendi: " . $keywords);
                } else {
                    log_message("SQL Hatası (UPDATE anahtar_kelimeler): " . $conn->error, "ERROR");
                }
            } else {
                log_message("Extracted keywords bulunamadı veya formatı hatalı.", "WARNING");
            }
        } else {
            log_message("final_details.json dosyası bulunamadı.", "WARNING");
        }

        echo json_encode([
            'success' => true,
            'download_link' => $relativePath
        ]);
    } catch (Exception $e) {
        log_message($e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Makale Anonimleştirme</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Makale Anonimleştirme</h1>
        <form id="anonimlestirForm">
            <label for="makale_id">Makale ID:</label>
            <input type="number" id="makale_id" name="makale_id" required>
            <br><br>
            <p>Anonimleştirme Seçenekleri:</p>
            <label>
                <input type="checkbox" name="anon_options[]" value="isim">
                İsim
            </label>
            <br>
            <label>
                <input type="checkbox" name="anon_options[]" value="kurum">
                Kurum
            </label>
            <br>
            <label>
                <input type="checkbox" name="anon_options[]" value="eposta">
                E-posta
            </label>
            <br><br>
            <button type="submit">Anonimleştir</button>
        </form>
        <div id="result"></div>
       
        <div style="text-align: center; margin-top: 20px;">
            <a href="yonetici_paneli.php">Yönetici Paneline Git</a>
        </div>
    </div>

    <script>
        document.getElementById('anonimlestirForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const formElement = document.getElementById('anonimlestirForm');
            const formData = new FormData(formElement);
            try {
                const response = await fetch('anonimlestir.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('result').innerHTML = `
                        <p>Anonimleştirme başarılı! Dosyayı aşağıdan indirebilirsiniz:</p>
                        <a href="${result.download_link}" download>Anonimleştirilmiş Makaleyi İndir</a>
                    `;
                } else {
                    document.getElementById('result').innerHTML = `<p>Hata: ${result.error}</p>`;
                }
            } catch (error) {
                document.getElementById('result').innerHTML = `<p>Beklenmedik bir hata oluştu.</p>`;
            }
        });
    </script>
</body>
</html>
