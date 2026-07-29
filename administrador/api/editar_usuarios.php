<?php
/**
 * editar_usuarios.php - API de Administração de Contas de Usuários
 * Mescla tratamento seguro de exceções, sessão restrita e criptografia simétrica.
 */

session_start();

// Desativa exibição direta de erros HTML no corpo do fluxo para não quebrar a formatação JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Configura buffer de saída para descarte de eventuais warnings residuais no envio do JSON
ob_start();

try {
    // Importa a conexão PDO ($pdo)
    require_once __DIR__ . '/../../config/database.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Falha na conexão com o banco de dados.");
    }

    // ================= SEGURANÇA E AUTORIZAÇÃO =================
    if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'lider') {
        http_response_code(403);
        throw new Exception("Acesso negado. Apenas líderes administradores podem gerenciar usuários.");
    }

    // ================= FUNÇÕES DE CRIPTOGRAFIA DE E-MAIL =================
    if (!function_exists('encryptEmail')) {
        function encryptEmail($email)
        {
            $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'k4m1sh1b41_s3cr3t_k3y_2025';
            $method = 'aes-256-cbc';
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
            $encrypted = openssl_encrypt(strtolower(trim($email)), $method, $key, 0, $iv);
            return base64_encode($iv . $encrypted);
        }
    }

    if (!function_exists('decryptEmail')) {
        function decryptEmail($encrypted)
        {
            $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'k4m1sh1b41_s3cr3t_k3y_2025';
            $method = 'aes-256-cbc';
            $data = base64_decode($encrypted);
            $ivLength = openssl_cipher_iv_length($method);
            if (strlen($data) <= $ivLength) {
                return "Email corrompido";
            }
            $iv = substr($data, 0, $ivLength);
            $cipher = substr($data, $ivLength);
            $decrypted = openssl_decrypt($cipher, $method, $key, 0, $iv);
            return $decrypted !== false ? $decrypted : "Erro ao descriptografar";
        }
    }

    $action = $_GET['action'] ?? '';

    // ========================================================
    // ROTA: LISTAR SALAS CADASTRADAS NO SISTEMA (DINÂMICO)
    // ========================================================
    if ($action === 'listar_salas') {
        // Coleta dinamicamente as tabelas físicas do banco
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Filtra tabelas de salas (ex: 104a, 103d, 102c, 102d, 101d)
        $salas = array_filter($tables, function ($table) {
            return preg_match('/^\d+[a-z]?$/i', $table);
        });

        // Caso a consulta dinâmica falhe, providencia uma lista padrão resiliente
        if (empty($salas)) {
            $salas = ['101d', '102c', '102d', '103d', '104a'];
        }

        echo json_encode([
            'sucesso' => true,
            'salas' => array_values($salas)
        ]);
        exit;
    }

    // ========================================================
    // ROTA: LISTAR TODOS OS USUÁRIOS CADASTRADOS
    // ========================================================
    if ($action === 'listar') {
        $sql = "SELECT u.id, u.nome, u.sobrenome, u.email_encrypted as email, u.cargo, r.ambiente 
                FROM usuarios u 
                LEFT JOIN responsaveis r ON u.id = r.usuario_id 
                ORDER BY u.id ASC";

        $stmt = $pdo->query($sql);
        $usuarios = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = [
                'id' => (int) $row['id'],
                'nome' => $row['nome'],
                'sobrenome' => $row['sobrenome'],
                'email' => decryptEmail($row['email']),
                'cargo' => $row['cargo'],
                'ambiente' => $row['ambiente'] ?? ""
            ];
        }

        echo json_encode([
            'sucesso' => true,
            'usuarios' => $usuarios
        ]);
        exit;
    }

    // Tratamento de requisições POST para evitar quebras por parâmetros vazios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        // ========================================================
        // ROTA: ATUALIZAR USUÁRIO EXISTENTE (COM SEGURANÇA DE TRANSAÇÃO)
        // ========================================================
        if ($action === 'atualizar') {
            $id = $input['id'] ?? null;
            $nome = trim($input['nome'] ?? '');
            $sobrenome = trim($input['sobrenome'] ?? '');
            $email = strtolower(trim($input['email'] ?? ''));
            $cargo = $input['cargo'] ?? '';
            $senha = $input['senha'] ?? '';
            $sala = $input['sala'] ?? '';

            if (!$id || !$nome || !$sobrenome || !$email || !$cargo) {
                throw new Exception("Dados obrigatórios incompletos.");
            }
            if (!in_array($cargo, ['instrutor', 'lider'])) {
                throw new Exception("Cargo inválido.");
            }

            $emailHash = hash('sha256', $email);
            $emailEncrypted = encryptEmail($email);

            // Verifica se o e-mail inserido já pertence a outra conta cadastrada
            $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email_hash = ? AND id <> ?");
            $chk->execute([$emailHash, $id]);
            if ($chk->rowCount() > 0) {
                throw new Exception("Este e-mail já está cadastrado em outra conta.");
            }

            $pdo->beginTransaction();

            // Atualiza dados básicos
            if (!empty($senha)) {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE usuarios SET nome = ?, sobrenome = ?, email_hash = ?, email_encrypted = ?, cargo = ?, senha = ? WHERE id = ?");
                $upd->execute([$nome, $sobrenome, $emailHash, $emailEncrypted, $cargo, $senhaHash, $id]);
            } else {
                $upd = $pdo->prepare("UPDATE usuarios SET nome = ?, sobrenome = ?, email_hash = ?, email_encrypted = ?, cargo = ? WHERE id = ?");
                $upd->execute([$nome, $sobrenome, $emailHash, $emailEncrypted, $cargo, $id]);
            }

            // Gerencia vínculo de responsabilidade do ambiente
            // Remove vínculo pré-existente deste usuário específico
            $delRep = $pdo->prepare("DELETE FROM responsaveis WHERE usuario_id = ?");
            $delRep->execute([$id]);

            if (!empty($sala)) {
                // Remove qualquer vínculo desta sala com outro coordenador (respeitando chave UNIQUE de ambiente)
                $delAmb = $pdo->prepare("DELETE FROM responsaveis WHERE ambiente = ?");
                $delAmb->execute([$sala]);

                // Vincula ao usuário atual
                $insRep = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW())");
                $insRep->execute([$id, $sala]);
            }

            $pdo->commit();
            echo json_encode(['sucesso' => true]);
            exit;
        }

        // ========================================================
        // ROTA: CRIAR NOVO USUÁRIO
        // ========================================================
        if ($action === 'criar') {
            $nome = trim($input['nome'] ?? '');
            $sobrenome = trim($input['sobrenome'] ?? '');
            $email = strtolower(trim($input['email'] ?? ''));
            $cargo = $input['cargo'] ?? '';
            $senha = $input['senha'] ?? '';
            $sala = $input['sala'] ?? '';

            if (!$nome || !$sobrenome || !$email || !$cargo || empty($senha)) {
                throw new Exception("Insira todos os dados obrigatórios incluindo senha.");
            }
            if (!in_array($cargo, ['instrutor', 'lider'])) {
                throw new Exception("Cargo inválido.");
            }

            $emailHash = hash('sha256', $email);
            $emailEncrypted = encryptEmail($email);
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // Verifica duplicidade
            $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email_hash = ?");
            $chk->execute([$emailHash]);
            if ($chk->rowCount() > 0) {
                throw new Exception("Este e-mail já está cadastrado.");
            }

            $pdo->beginTransaction();

            $ins = $pdo->prepare("INSERT INTO usuarios (nome, sobrenome, email_hash, email_encrypted, cargo, senha, data_criacao) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $ins->execute([$nome, $sobrenome, $emailHash, $emailEncrypted, $cargo, $senhaHash]);
            $novoId = $pdo->lastInsertId();

            if (!empty($sala)) {
                // Remove vínculo de ambiente se pertencer a outro para respeitar a chave de restrição UNIQUE
                $delAmb = $pdo->prepare("DELETE FROM responsaveis WHERE ambiente = ?");
                $delAmb->execute([$sala]);

                $insRep = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW())");
                $insRep->execute([$novoId, $sala]);
            }

            $pdo->commit();
            echo json_encode(['sucesso' => true]);
            exit;
        }

        // ========================================================
        // ROTA: EXCLUIR USUÁRIO
        // ========================================================
        if ($action === 'excluir') {
            $id = $input['id'] ?? null;
            if (!$id) {
                throw new Exception("ID do usuário não fornecido.");
            }

            $pdo->beginTransaction();

            // Remove responsabilidades antes de remover a conta (redundância de integridade caso chave cascade falhe)
            $delResp = $pdo->prepare("DELETE FROM responsaveis WHERE usuario_id = ?");
            $delResp->execute([$id]);

            $delUser = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $delUser->execute([$id]);

            $pdo->commit();
            echo json_encode(['sucesso' => true]);
            exit;
        }
    }

    throw new Exception("Ação inválida.");

} catch (Exception $e) {
    // Reverte transações ativas em caso de falhas
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Descarta mensagens residuais no buffer para manter a formatação do JSON limpa
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
