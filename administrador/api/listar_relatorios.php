<?php
session_start();
header('Content-Type: application/json');

// ================= SEGURANÇA =================
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

// ================= BANCO =================
require_once __DIR__ . '/../../config/database.php';

try {

    // (Opcional) filtro por data - igual ao outro padrão de simplicidade
    $data = $_GET['data'] ?? null;

    if ($data) {
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
            ORDER BY data_geracao DESC
        ");
        $stmt->execute([$data]);
    } else {
        // Sem filtro (igual estilo do listar_inspecoes)
        $stmt = $pdo->query("
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
            ORDER BY data_geracao DESC
        ");
    }

    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($relatorios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro ao buscar dados: ' . $e->getMessage()
    ]);
}
?>