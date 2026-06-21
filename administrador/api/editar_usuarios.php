<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

// ================= FUNÇÕES DE CRIPTOGRAFIA =================
// (caso não existam em config/encryption.php)
if (!function_exists('encryptEmail')) {
    function encryptEmail($email) {
        $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default_chave_secreta_32bytes_';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($email, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
}
if (!function_exists('decryptEmail')) {
    function decryptEmail($encrypted) {
        $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default_chave_secreta_32bytes_';
        $data = base64_decode($encrypted);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $cipher = substr($data, $ivLength);
        return openssl_decrypt($cipher, 'aes-256-cbc', $key, 0, $iv);
    }
}

// ================= SEGURANÇA =================
if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    // Listar usuários com seu responsável (se houver)
    $sql = "SELECT u.id, u.nome, u.sobrenome, u.email_encrypted as email, u.cargo, r.ambiente 
            FROM usuarios u 
            LEFT JOIN responsaveis r ON u.id = r.usuario_id
            ORDER BY u.id";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Descriptografar email para exibição
    foreach ($usuarios as &$usuario) {
        $usuario['email'] = decryptEmail($usuario['email']);
    }
    echo json_encode(['sucesso' => true, 'usuarios' => $usuarios]);

} elseif ($action === 'listar_salas') {
    // Listar todas as salas (ambientes) existentes - baseado nas tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $salas = array_filter($tables, function($table) {
        // Filtra tabelas que são salas (ex: 104a, 103d, 102c)
        return preg_match('/^\d+[a-z]?$/i', $table);
    });
    echo json_encode(['sucesso' => true, 'salas' => array_values($salas)]);

} elseif ($action === 'atualizar') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    $nome = trim($input['nome'] ?? '');
    $sobrenome = trim($input['sobrenome'] ?? '');
    $email = trim($input['email'] ?? '');
    $cargo = $input['cargo'] ?? '';
    $senha = $input['senha'] ?? '';
    $sala = $input['sala'] ?? null; // pode ser string vazia ou null

    if (!$id || !$nome || !$sobrenome || !$email || !$cargo) {
        echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
        exit;
    }
    if (!in_array($cargo, ['instrutor', 'lider'])) {
        echo json_encode(['sucesso' => false, 'erro' => 'Cargo inválido']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verificar se email já existe para outro usuário
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email_hash = ? AND id != ?");
        $emailHash = hash('sha256', $email);
        $stmtCheck->execute([$emailHash, $id]);
        if ($stmtCheck->fetch()) {
            echo json_encode(['sucesso' => false, 'erro' => 'Email já cadastrado para outro usuário.']);
            $pdo->rollBack();
            exit;
        }

        // Atualizar usuário
        $emailEncrypted = encryptEmail($email);
        $sql = "UPDATE usuarios SET nome = ?, sobrenome = ?, email_hash = ?, email_encrypted = ?, cargo = ?";
        $params = [$nome, $sobrenome, $emailHash, $emailEncrypted, $cargo];
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql .= ", senha = ?";
            $params[] = $senhaHash;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Atualizar responsável (sala)
        if (empty($sala)) {
            // Remover se existir
            $stmtDel = $pdo->prepare("DELETE FROM responsaveis WHERE usuario_id = ?");
            $stmtDel->execute([$id]);
        } else {
            // Verifica se a sala existe (tabela)
            $stmtCheckSala = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmtCheckSala->execute([$sala]);
            if ($stmtCheckSala->rowCount() == 0) {
                echo json_encode(['sucesso' => false, 'erro' => 'Sala inválida.']);
                $pdo->rollBack();
                exit;
            }
            // Upsert
            $stmtUpsert = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id), data_atribuicao = NOW()");
            $stmtUpsert->execute([$id, $sala]);
        }

        $pdo->commit();
        echo json_encode(['sucesso' => true]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar: ' . $e->getMessage()]);
    }

} elseif ($action === 'criar') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nome = trim($input['nome'] ?? '');
    $sobrenome = trim($input['sobrenome'] ?? '');
    $email = trim($input['email'] ?? '');
    $cargo = $input['cargo'] ?? '';
    $senha = $input['senha'] ?? '';
    $sala = $input['sala'] ?? null;

    if (!$nome || !$sobrenome || !$email || !$cargo || empty($senha)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos (senha obrigatória para novo usuário)']);
        exit;
    }
    if (!in_array($cargo, ['instrutor', 'lider'])) {
        echo json_encode(['sucesso' => false, 'erro' => 'Cargo inválido']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $emailHash = hash('sha256', $email);
        // Verifica se email já existe
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email_hash = ?");
        $stmtCheck->execute([$emailHash]);
        if ($stmtCheck->fetch()) {
            echo json_encode(['sucesso' => false, 'erro' => 'Email já cadastrado.']);
            $pdo->rollBack();
            exit;
        }

        $emailEncrypted = encryptEmail($email);
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, sobrenome, email_hash, email_encrypted, cargo, senha, data_criacao) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $sobrenome, $emailHash, $emailEncrypted, $cargo, $senhaHash]);
        $novoId = $pdo->lastInsertId();

        // Atribuir sala se fornecida
        if (!empty($sala)) {
            $stmtCheckSala = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmtCheckSala->execute([$sala]);
            if ($stmtCheckSala->rowCount() > 0) {
                $stmtUpsert = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id), data_atribuicao = NOW()");
                $stmtUpsert->execute([$novoId, $sala]);
            }
        }

        $pdo->commit();
        echo json_encode(['sucesso' => true]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao criar: ' . $e->getMessage()]);
    }

} elseif ($action === 'excluir') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID não fornecido']);
        exit;
    }
    try {
        $pdo->beginTransaction();
        // Excluir responsável se existir
        $stmtDelResp = $pdo->prepare("DELETE FROM responsaveis WHERE usuario_id = ?");
        $stmtDelResp->execute([$id]);
        // Excluir usuário
        $stmtDelUser = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmtDelUser->execute([$id]);
        $pdo->commit();
        echo json_encode(['sucesso' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida']);
}