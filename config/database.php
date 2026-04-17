<?php
$host = 'localhost';
$dbname = 'u196097154_kamishibai';
$username = 'u196097154_lenon'; // altere conforme necessário
$password = 'j=3KPDnhVj6x';     // altere conforme necessário

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['erro' => 'Falha na conexão: ' . $e->getMessage()]));
}
?>