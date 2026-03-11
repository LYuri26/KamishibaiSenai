<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

try {
    $stmt = $pdo->query("SELECT id, nome, data, observacoes FROM `104a` ORDER BY data DESC");
    $inspecoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($inspecoes);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>