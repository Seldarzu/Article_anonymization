<?php
require_once '../includes/db_connection.php';

$makale_id = $_GET['id'] ?? null;
if (!$makale_id) {
    die(json_encode(["error" => "Makale ID bulunamadı."]));
}


$query = $conn->prepare("SELECT icerik FROM makaleler WHERE id = ?");
$query->bind_param("i", $makale_id);
$query->execute();
$result = $query->get_result();
$makale = $result->fetch_assoc();

if (!$makale) {
    die(json_encode(["error" => "Makale bulunamadı."]));
}


$python = "python ../scripts/nlp_functions.py";
$descriptor = [0 => ["pipe", "r"], 1 => ["pipe", "w"]];
$process = proc_open($python, $descriptor, $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], $makale['icerik']);
    fclose($pipes[0]);

    $json_output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);

    echo $json_output; 
} else {
    die(json_encode(["error" => "Python NLP işlemi başlatılamadı."]));
}
?>
