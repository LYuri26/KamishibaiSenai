<?php
session_start();
header('Content-Type: application/json');

// Verifica se usuário está logado e é gerência
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {
    // UNION das duas tabelas, selecionando os campos comuns e adicionando a coluna 'sala'
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
    echo json_encode(['erro' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>