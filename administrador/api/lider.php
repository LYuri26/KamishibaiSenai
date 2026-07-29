<?php
/**
 * lider.php - API de Atribuição de Responsáveis por Ambientes
 * Mapeia as salas técnicas e gerencia a governança dos coordenadores responsáveis.
 */

session_start();

// Desativa a amostragem de erros diretamente no corpo do documento para evitar quebra de JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Inicia buffer para limpar saídas indesejadas do PHP se houver falhas no fluxo
ob_start();

try {
    // Importa a conexão PDO ($pdo)
    require_once __DIR__ . '/../../config/database.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Falha na conexão interna com o banco de dados.");
    }

    // ================= CONTROLE DE SEGURANÇA =================
    // Permite que apenas administradores líderes executem alterações de governança
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'lider') {
        http_response_code(403);
        throw new Exception("Acesso negado. Apenas líderes administradores podem acessar esta rota.");
    }

    $action = $_GET['action'] ?? '';

    // ========================================================
    // ROTA: LISTAR SALAS E SEUS RESPECTIVOS COORDENADORES ATUAIS
    // ========================================================
    if ($action === 'listar') {
        // Coleta dinamicamente as tabelas físicas do banco
        $stmt = $pdo->query("SHOW TABLES");
        $todasTabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Filtra tabelas de salas (ex: 104a, 103d, 102c, 102d, 101d)
        $ambientes = array_filter($todasTabelas, function ($tabela) {
            return $tabela !== 'relatorios' &&
                $tabela !== 'usuarios' &&
                $tabela !== 'responsaveis' &&
                preg_match('/^\d+[a-z]?$/i', $tabela);
        });

        // Caso a consulta dinâmica falhe ou esteja vazia, usa fallback seguro
        if (empty($ambientes)) {
            $ambientes = ['101d', '102c', '102d', '103d', '104a'];
        }

        // Buscar responsáveis atualmente atribuídos
        $stmtResp = $pdo->query("
            SELECT r.ambiente, u.id, u.nome, u.sobrenome 
            FROM responsaveis r 
            JOIN usuarios u ON r.usuario_id = u.id
        ");

        $responsaveis = [];
        while ($row = $stmtResp->fetch(PDO::FETCH_ASSOC)) {
            $responsaveis[$row['ambiente']] = [
                'id' => (int) $row['id'],
                'nome' => $row['nome'] . ' ' . $row['sobrenome']
            ];
        }

        // Listar todos os usuários ativos no sistema para atribuição
        $stmtUsu = $pdo->prepare("SELECT id, nome, sobrenome, cargo FROM usuarios ORDER BY nome ASC");
        $stmtUsu->execute();
        $usuarios = $stmtUsu->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($ambientes as $ambiente) {
            $result[$ambiente] = $responsaveis[$ambiente] ?? null;
        }

        echo json_encode([
            'sucesso' => true,
            'ambientes' => $result,
            'usuarios' => $usuarios
        ]);
        exit;
    }

    // ========================================================
    // ROTA: ATRIBUIR RESPONSÁVEL A UM AMBIENTE ESPECÍFICO
    // ========================================================
    if ($action === 'atribuir') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Método de requisição inválido.");
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $ambiente = $input['ambiente'] ?? '';
        $usuario_id = $input['usuario_id'] ?? null;

        if (empty($ambiente) || !$usuario_id) {
            throw new Exception("Dados incompletos fornecidos.");
        }

        // Verificar se o ambiente existe no banco (a tabela correspondente)
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$ambiente]);
        if ($stmt->rowCount() == 0) {
            throw new Exception("Ambiente selecionado inválido ou inexistente.");
        }

        // Verificar se o usuário existe na base
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Usuário selecionado não foi encontrado.");
        }

        // Upsert seguro na tabela de responsáveis (Sincronismo de Líderes)
        $sql = "INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id), data_atribuicao = NOW()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $ambiente]);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Responsável atribuído com sucesso.'
        ]);
        exit;
    }

    throw new Exception("Ação administrativa inválida.");

} catch (PDOException $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro interno de banco de dados: ' . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
    exit;
}
?>