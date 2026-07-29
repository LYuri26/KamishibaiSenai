<?php
/**
 * listar_inspecoes.php - API de Listagem Unificada de Auditorias
 * Executa uma união (UNION ALL) de todas as tabelas de ambientes do sistema.
 */

session_start();

// Desativa exibição direta de erros HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Inicia buffer para limpar saídas indesejadas do PHP se houver falhas no fluxo
ob_start();

try {
    // ================= CONTROLE DE SEGURANÇA =================
    // Permite que tanto o 'lider' quanto o 'instrutor' acessem a listagem geral
    $cargosPermitidos = ['lider', 'instrutor'];

    if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_cargo'], $cargosPermitidos)) {
        http_response_code(403);
        throw new Exception("Acesso negado. Faça login para continuar.");
    }

    // ================= BANCO DE DADOS =================
    require_once __DIR__ . '/../../config/database.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Falha na conexão com o banco de dados.");
    }

    // Executa a consolidação unificada de vistorias (Inclusão de 102d e 101d)
    $sql = "
        (SELECT id, nome, data, momento, observacoes, '104a' AS sala FROM `104a`)
        UNION ALL
        (SELECT id, nome, data, momento, observacoes, '103d' AS sala FROM `103d`)
        UNION ALL
        (SELECT id, nome, data, momento, observacoes, '102c' AS sala FROM `102c`)
        UNION ALL
        (SELECT id, nome, data, momento, observacoes, '102d' AS sala FROM `102d`)
        UNION ALL
        (SELECT id, nome, data, momento, observacoes, '101d' AS sala FROM `101d`)
        ORDER BY data DESC
    ";

    $stmt = $pdo->query($sql);
    $inspecoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($inspecoes);
    exit;

} catch (PDOException $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro interno de banco de dados ao unificar vistorias: ' . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'erro' => $e->getMessage()
    ]);
    exit;
}
?>