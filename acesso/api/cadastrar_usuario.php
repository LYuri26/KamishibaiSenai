<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/encryption.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['nome'], $input['sobrenome'], $input['email'], $input['cargo'], $input['senha'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$nome = trim($input['nome']);
$sobrenome = trim($input['sobrenome']);
$email = strtolower(trim($input['email']));
$cargo = $input['cargo'];
$senha = $input['senha'];

// Validação do domínio
if (!preg_match('/@fiemg\.com\.br$/', $email)) {
    echo json_encode(['sucesso' => false, 'erro' => 'O e-mail deve ser do domínio @fiemg.com.br']);
    exit;
}

// Validação da senha
if (strlen($senha) < 6 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
    echo json_encode(['sucesso' => false, 'erro' => 'A senha deve ter no mínimo 6 caracteres, com letras e números']);
    exit;
}

// Calcula o hash do e-mail para unicidade e busca
$emailHash = hash('sha256', $email);

// Verifica se o e-mail já está cadastrado (pelo hash)
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email_hash = ?");
$stmt->execute([$emailHash]);
if ($stmt->fetch()) {
    echo json_encode(['sucesso' => false, 'erro' => 'E-mail já cadastrado']);
    exit;
}

// Criptografa o e-mail
$emailEncrypted = encryptEmail($email);
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nome, sobrenome, email_hash, email_encrypted, cargo, senha, data_criacao)
        VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$nome, $sobrenome, $emailHash, $emailEncrypted, $cargo, $senhaHash]);
    echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário cadastrado com sucesso']);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao cadastrar: ' . $e->getMessage()]);
}
?>