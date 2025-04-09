<?php
require_once '../includes/db_connection.php';
require_once '../includes/security_functions.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? null;
$makale = null;
$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'sorgula') {
    $takip_numarasi = $_POST['takip_numarasi'] ?? null;
    $yazar_eposta   = $_POST['yazar_eposta'] ?? null;

    if ($takip_numarasi && $yazar_eposta) {
        $query = $conn->prepare("SELECT * FROM makaleler WHERE takip_numarasi = ? AND yazar_eposta = ?");
        $query->bind_param("ss", $takip_numarasi, $yazar_eposta);
        $query->execute();
        $result = $query->get_result();
        $makale = $result->fetch_assoc();
    }
}


elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'revize') {
    $makale_id = $_POST['makale_id'] ?? null;
    if ($makale_id && isset($_FILES['revize_pdf']) && $_FILES['revize_pdf']['error'] === UPLOAD_ERR_OK) {
        
        $stmt = $conn->prepare("SELECT dosya_yolu FROM makaleler WHERE id = ?");
        $stmt->bind_param("i", $makale_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $makale = $result->fetch_assoc();

        if ($makale) {
            
            $originalFileName = basename($makale['dosya_yolu']); 
            

            $info = pathinfo($originalFileName);
            $baseFilename = $info['filename'];
            $extension    = $info['extension'];

            
            if (substr($baseFilename, -7) === '_revize') {
                $baseFilename = substr($baseFilename, 0, -7);
            }
            $newFileName = $baseFilename . '_revize.' . $extension; 
            
            $uploadDir = realpath("../uploads");
            if (!$uploadDir) {
                mkdir("../uploads", 0777, true);
                $uploadDir = realpath("../uploads");
            }
            $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

            if (move_uploaded_file($_FILES['revize_pdf']['tmp_name'], $uploadPath)) {
                
                $relativePath = "uploads/" . $newFileName;

               
                $stmt2 = $conn->prepare("UPDATE makaleler SET dosya_yolu = ? WHERE id = ?");
                $stmt2->bind_param("si", $relativePath, $makale_id);

                if ($stmt2->execute()) {
                    $message = "Revize dosyası başarıyla yüklendi ve veritabanı güncellendi.";

                    
                    $stmt3 = $conn->prepare("SELECT * FROM makaleler WHERE id = ?");
                    $stmt3->bind_param("i", $makale_id);
                    $stmt3->execute();
                    $result3 = $stmt3->get_result();
                    $makale = $result3->fetch_assoc();
                } else {
                    $message = "Revize dosyası yüklendi ancak veritabanı güncellemesi yapılamadı.";
                }
            } else {
                $message = "Revize dosyası yüklenirken bir hata oluştu.";
            }
        } else {
            $message = "Makale bulunamadı. Revize yükleme başarısız.";
        }
    } else {
        $message = "Revize dosyası seçilmedi veya yükleme hatası oluştu.";
    }
}



elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mesaj') {
    $yazar_eposta = isset($_POST['yazar_eposta']) ? sanitize_input($_POST['yazar_eposta']) : null;
    $mesaj        = isset($_POST['mesaj']) ? sanitize_input($_POST['mesaj']) : null;

    if (!$yazar_eposta || !$mesaj) {
        $message = "Hata: Yazar e-posta ve mesaj alanları zorunludur!";
    } else {
        $query = $conn->prepare("INSERT INTO mesajlar (yazar_eposta, mesaj, tarih) VALUES (?, ?, NOW())");
        if ($query) {
            $query->bind_param("ss", $yazar_eposta, $mesaj);
            if ($query->execute()) {
                $message = "Mesaj başarıyla gönderildi!";
            } else {
                $message = "Hata: Mesaj gönderilemedi!";
            }
        } else {
            $message = "SQL hatası: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Makale Durumu Sorgula, Revize ve Mesaj Gönder</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #fdfdfd;
        }
        h1, h2 { color: #333; }
        .dosya { margin-top: 10px; }
        .geri { margin-top: 20px; }
        .message { margin-top: 20px; font-weight: bold; color: green; }
        .error { margin-top: 20px; font-weight: bold; color: red; }
    </style>
</head>
<body>
<div class="container">
    <h1>📄 Makale Durumunu Sorgula, Revize ve Mesaj Gönder</h1>

   
    <form method="POST">
        <input type="hidden" name="action" value="sorgula">
        <label>Takip Numarası:</label><br>
        <input type="text" name="takip_numarasi" required><br><br>

        <label>Yazar E-posta:</label><br>
        <input type="email" name="yazar_eposta" required><br><br>

        <button type="submit">Sorgula</button>
    </form>

    
    <?php if ($message): ?>
        <p class="<?= (stripos($message, 'hata') !== false || stripos($message, 'error') !== false) ? 'error' : 'message' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($makale): ?>
        <hr>
        <h2>🧾 Makale Bilgileri</h2>
        <p><strong>Başlık:</strong> <?= htmlspecialchars($makale['baslik']) ?></p>
        <p><strong>Durum:</strong> <?= htmlspecialchars($makale['durum']) ?></p>

        <p><strong>Makale Dosyası:</strong>
        <?php if (file_exists("../user/" . $makale['dosya_yolu'])): ?>
            <a href="<?= htmlspecialchars("../user/" . $makale['dosya_yolu']) ?>" download>İndir</a>
        <?php else: ?>
            <span style="color:red;">Dosya bulunamadı!</span>
        <?php endif; ?>
        </p>

        <?php if (!empty($makale['final_dosya_yolu']) && file_exists("../anonim_icerikler/" . $makale['final_dosya_yolu'])): ?>
            <div class="dosya">
                ✅ <a href="<?= htmlspecialchars("../anonim_icerikler/" . $makale['final_dosya_yolu']) ?>" download>
                Hakem Yorumlu Final PDF'yi İndir</a>
            </div>
        <?php else: ?>
            <p style="color: red;">📭 Henüz final PDF oluşturulmamış.</p>
        <?php endif; ?>

       
        <?php if (!empty($makale['yazar_eposta'])): ?>
            <hr>
            <h2>📥 Makale Revizesi Yükle</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="revize">
                <input type="hidden" name="makale_id" value="<?= htmlspecialchars($makale['id']) ?>">
                <label>Revize PDF Dosyası:</label><br>
                <input type="file" name="revize_pdf" accept="application/pdf" required><br><br>
                <button type="submit">Revize Yükle</button>
            </form>
        <?php endif; ?>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'sorgula'): ?>
        <p class="error">❌ Bu takip numarasına ait bir makale bulunamadı.</p>
    <?php endif; ?>

   
    <hr>
    <h2>Mesaj Gönder</h2>
    <form method="POST">
        <input type="hidden" name="action" value="mesaj">
        <label>Yazar E-posta:</label><br>
        <input type="email" name="yazar_eposta" required><br><br>

        <label>Mesajınız:</label><br>
        <textarea name="mesaj" rows="4" cols="50" required></textarea><br><br>

        <button type="submit">Mesaj Gönder</button>
    </form>

    <div class="geri">
        <a href="../index.php">🔙 Anasayfa</a>
    </div>
</div>
</body>
</html>
