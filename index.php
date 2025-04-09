<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Akademik Makale Sistemi</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f2f5;
      text-align: center;
      padding: 50px;
    }
    .paneller {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
    }
    .panel {
      background: #fff;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      width: 280px;
      transition: transform 0.2s ease;
    }
    .panel:hover {
      transform: scale(1.05);
    }
    .panel h2 {
      margin-bottom: 15px;
    }
    .panel a {
      display: inline-block;
      padding: 10px 20px;
      background: #3498db;
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
    }
    .panel a:hover {
      background: #2980b9;
    }
  </style>
</head>
<body>
  <h1> Akademik Makale Değerlendirme Sistemi</h1>
  <p>Lütfen bir panel seçin:</p>

  <div class="paneller">
  
    <div class="panel">
      <h2> Yönetici Paneli</h2>
      <a href="admin/yonetici_paneli.php">Giriş Yap</a>
    </div>

   
    <div class="panel">
      <h2>Hakem Paneli</h2>
      <a href="hakem/hakem_paneli.php?hakem_id=1">Giriş Yap</a>
    </div>

    <div class="panel">
      <h2> Yazar Paneli</h2>
      <a href="user/makale_yukle.php">Makale Yükle</a>
    </div>

    
    <div class="panel">
      <h2>🔍 Makale Sorgula / Revize Gönder</h2>
      <a href="user/makale_sorgula.php">Sorgula</a>
    </div>
  </div>
</body>
</html>
