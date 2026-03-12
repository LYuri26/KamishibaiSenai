<?php
session_start();
header('Content-Type: application/json');

// Verifica se usuário está logado e é gerência
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'gerencia') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$data = $_GET['data'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT id, inspecao_id, sala, periodo, momento, observacoes, data_geracao 
                           FROM relatorios 
                           WHERE data = ? 
                           ORDER BY id");
    $stmt->execute([$data]);
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($relatorios);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao buscar relatórios: ' . $e->getMessage()]);
}
?>