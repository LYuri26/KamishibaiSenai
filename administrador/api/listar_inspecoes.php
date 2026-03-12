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

try {
    $stmt = $pdo->query("SELECT id, nome, data, momento, observacoes FROM `104a` ORDER BY data DESC");
    $inspecoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($inspecoes);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>