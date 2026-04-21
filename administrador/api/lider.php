<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

// Verifica se é admin
if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado. Apenas administradores.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    // Lista todos os ambientes (tabelas que representam salas/oficinas)
    $stmt = $pdo->query("SHOW TABLES");
    $todasTabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $ambientes = array_filter($todasTabelas, function ($tabela) {
        return $tabela !== 'relatorios' && $tabela !== 'usuarios' && $tabela !== 'responsaveis' && preg_match('/^\d+[a-z]?$/i', $tabela);
    });

    // Buscar responsáveis atuais
    $stmtResp = $pdo->query("SELECT r.ambiente, u.id, u.nome, u.sobrenome 
                              FROM responsaveis r 
                              JOIN usuarios u ON r.usuario_id = u.id");
    $responsaveis = [];
    while ($row = $stmtResp->fetch(PDO::FETCH_ASSOC)) {
        $responsaveis[$row['ambiente']] = [
            'id' => $row['id'],
            'nome' => $row['nome'] . ' ' . $row['sobrenome']
        ];
    }

    // Listar todos os usuários (exceto admin) para escolher como responsável
    $stmtUsu = $pdo->prepare("SELECT id, nome, sobrenome, cargo FROM usuarios WHERE cargo != 'lider' ORDER BY nome");
    $stmtUsu->execute();
    $usuarios = $stmtUsu->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($ambientes as $ambiente) {
        $result[$ambiente] = $responsaveis[$ambiente] ?? null;
    }

    echo json_encode(['sucesso' => true, 'ambientes' => $result, 'usuarios' => $usuarios]);

} elseif ($action === 'atribuir') {
    $input = json_decode(file_get_contents('php://input'), true);
    $ambiente = $input['ambiente'] ?? '';
    $usuario_id = $input['usuario_id'] ?? null;

    if (!$ambiente || !$usuario_id) {
        echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
        exit;
    }

    // Verificar se o ambiente existe (a tabela)
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$ambiente]);
    if ($stmt->rowCount() == 0) {
        echo json_encode(['sucesso' => false, 'erro' => 'Ambiente inválido']);
        exit;
    }

    // Verificar se usuário existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado']);
        exit;
    }

    // Upsert na tabela responsaveis
    $sql = "INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id), data_atribuicao = NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $ambiente]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Responsável atribuído com sucesso']);

} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida']);
}