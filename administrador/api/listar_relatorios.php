<?php
session_start();
header('Content-Type: application/json');

// ================= SEGURANÇA =================
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'gerencia') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

// ================= BANCO =================
require_once __DIR__ . '/../../config/database.php';

// ================= PARÂMETRO =================
$data = $_GET['data'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            inspecao_id,
            sala,
            periodo,
            momento,
            observacoes,
            imagens,
            data_geracao
        FROM relatorios
        WHERE data = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$data]);
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($relatorios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro ao buscar relatórios',
        'detalhe' => $e->getMessage() // remover em produção
    ]);
}
?>