<?php
session_start();
header('Content-Type: application/json');

// ================= SEGURANÇA =================
// Permite apenas os cargos 'lider' e 'instrutor'
$cargosPermitidos = ['lider', 'instrutor'];

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_cargo'], $cargosPermitidos)) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

// ================= BANCO =================
require_once __DIR__ . '/../../config/database.php';

try {
    // UNION das três tabelas de inspeção
    $sql = "
        (SELECT id, nome, data, momento, observacoes, '104a' AS sala FROM `104a`)
        UNION ALL
        (SELECT id, nome, data, momento, observacoes, '103d' AS sala FROM `103d`)
        UNION ALL
        (SELECT id, nome, data, momento, observacoes, '102c' AS sala FROM `102c`)
        ORDER BY data DESC
    ";
    $stmt = $pdo->query($sql);
    $inspecoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($inspecoes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>