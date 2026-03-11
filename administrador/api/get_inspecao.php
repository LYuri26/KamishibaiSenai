<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    echo json_encode(['erro' => 'ID não fornecido']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `104a` WHERE id = ?");
$stmt->execute([$id]);
$inspecao = $stmt->fetch(PDO::FETCH_ASSOC);

if ($inspecao) {
    echo json_encode($inspecao);
} else {
    echo json_encode(['erro' => 'Inspeção não encontrada']);
}
?>