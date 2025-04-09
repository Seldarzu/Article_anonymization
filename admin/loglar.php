<?php
// Yönetici giriş kontrolü (örneğin, admin_id oturum kontrolü)
session_start();
if (!isset($_SESSION['admin_id'])) {
    die("Yetkisiz erişim! Lütfen giriş yapınız.");
}

// Log dosyasının yolu (projenizin yapısına göre ayarlayın)
$logFile = __DIR__ . '/../logs/app.log';

// Log dosyası kontrolü
if (!file_exists($logFile)) {
    die("Log dosyası bulunamadı.");
}

// Log dosyasını satır satır oku
$logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Log Kayıtları</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sisteme Ait Log Kayıtları</h1>
        <table>
            <tr>
                <th>Tarih</th>
                <th>Düzey</th>
                <th>Mesaj</th>
            </tr>
            <?php foreach ($logs as $line): 
                // Log formatı örneğin: [2025-04-02 15:06:46] INFO: İşlem başlatıldı: 8
                if (preg_match('/\[(.*?)\]\s+(\w+):\s+(.*)/', $line, $matches)) {
                    $timestamp = $matches[1];
                    $level = $matches[2];
                    $message = $matches[3];
                } else {
                    $timestamp = '';
                    $level = '';
                    $message = $line;
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($timestamp) ?></td>
                <td><?= htmlspecialchars($level) ?></td>
                <td><?= htmlspecialchars($message) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><a href="yonetici_paneli.php">Yönetici Paneline Dön</a></p>
    </div>
</body>
</html>
