<?php
require_once '../includes/db_connection.php';

$tarih = $_GET['tarih'] ?? null;
$islem = $_GET['islem'] ?? null;
$makale_id = $_GET['makale_id'] ?? null;

$query = "SELECT * FROM loglar WHERE 1=1";
$params = [];
$types = "";

if ($tarih) {
    $query .= " AND DATE(tarih) = ?";
    $params[] = $tarih;
    $types .= "s";
}
if ($islem) {
    $query .= " AND islem LIKE ?";
    $params[] = "%$islem%";
    $types .= "s";
}
if ($makale_id) {
    $query .= " AND makale_id = ?";
    $params[] = $makale_id;
    $types .= "i";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$loglar = [];
while ($row = $result->fetch_assoc()) {
    $loglar[] = $row;
}

header('Content-Type: application/json');
echo json_encode($loglar);
?>
