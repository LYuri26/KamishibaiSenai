<?php
/**
 * listar_relatorios.php - API de Consolidação de Relatórios
 * Filtra vistorias realizadas por intervalo de datas (período), data única e por sala.
 */

session_start();

// Desativa a amostragem de erros diretamente no output para não corromper o JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Inicia buffer para limpar saídas indesejadas do PHP se houver falhas no fluxo
ob_start();

try {
    // Importa a conexão PDO ($pdo)
    require_once __DIR__ . '/../../config/database.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Falha na conexão com o banco de dados.");
    }

    // ================= CONTROLE DE SEGURANÇA =================
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'lider') {
        http_response_code(403);
        throw new Exception("Acesso negado. Apenas líderes administradores podem ler os relatórios.");
    }

    // Coleta dos parâmetros enviados via GET
    $data_inicio = $_GET['data_inicio'] ?? '';
    $data_fim = $_GET['data_fim'] ?? '';
    $data_unica = $_GET['data'] ?? ''; // Suporte retrocompatível
    $sala = $_GET['sala'] ?? 'todas';

    // Construção dinâmica da query SQL
    $sql = "SELECT id, inspecao_id, sala, data, periodo, momento, observacoes, imagens, data_geracao FROM relatorios";
    $where = [];
    $params = [];

    // 1. Aplicação de filtros temporais (período ou data única)
    if (!empty($data_inicio) && !empty($data_fim)) {
        $where[] = "data BETWEEN :data_inicio AND :data_fim";
        $params[':data_inicio'] = $data_inicio;
        $params[':data_fim'] = $data_fim;
    } elseif (!empty($data_unica)) {
        $where[] = "data = :data_unica";
        $params[':data_unica'] = $data_unica;
    } else {
        // Se nenhum parâmetro de tempo for definido, busca os últimos 30 dias para otimização de memória
        $where[] = "data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }

    // 2. Aplicação de filtro por Sala/Ambiente
    if ($sala !== 'todas') {
        $where[] = "sala = :sala";
        $params[':sala'] = $sala;
    }

    // Une os filtros acumulados à consulta base
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY data_geracao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($relatorios);
    exit;

} catch (PDOException $e) {
    // Tratamento específico para erros do banco de dados
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro interno de banco de dados: ' . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    // Tratamento para erros lógicos gerais ou de sessão
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'erro' => $e->getMessage()
    ]);
    exit;
}
?>