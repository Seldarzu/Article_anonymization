<?php
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['takip_numarasi']) && isset($_FILES['revize_dosya'])) {
        $takip_numarasi = $_POST['takip_numarasi'];
        $dosya_adi = uniqid() . "_" . basename($_FILES["revize_dosya"]["name"]);
        $revize_dosya_yolu = "uploads/" . $dosya_adi;

        
        $izinli_turler = ['application/pdf'];
        if (in_array($_FILES["revize_dosya"]["type"], $izinli_turler)) {
            if (move_uploaded_file($_FILES["revize_dosya"]["tmp_name"], $revize_dosya_yolu)) {
                
                $query = $conn->prepare("SELECT dosya_yolu FROM makaleler WHERE takip_numarasi = ?");
                $query->bind_param("s", $takip_numarasi);
                $query->execute();
                $result = $query->get_result();
                $makale = $result->fetch_assoc();
                
                if ($makale && file_exists($makale['dosya_yolu'])) {
                    unlink($makale['dosya_yolu']); 
                }

                
                $sql = "UPDATE makaleler SET dosya_yolu=?, durum='Revize Edildi' WHERE takip_numarasi=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $revize_dosya_yolu, $takip_numarasi);

                if ($stmt->execute()) {
                    echo "✅ Revize dosyası başarıyla yüklendi ve makale güncellendi!";
                } else {
                    echo "⚠️ Hata: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "⚠️ Dosya yükleme hatası!";
            }
        } else {
            echo "⚠️ Sadece PDF dosyaları yüklenebilir!";
        }
    } else {
        echo "⚠️ Form verileri eksik!";
    }
} else {
    echo "⚠️ Geçersiz istek!";
}

$conn->close();
?>
