<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['nome'], $input['sobrenome'], $input['email'], $input['cargo'], $input['senha'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$nome = trim($input['nome']);
$sobrenome = trim($input['sobrenome']);
$email = trim($input['email']);
$cargo = $input['cargo'];
$senha = $input['senha'];

// Validação do domínio
if (!preg_match('/@fiemg\.com\.br$/', $email)) {
    echo json_encode(['sucesso' => false, 'erro' => 'O e-mail deve ser do domínio @fiemg.com.br']);
    exit;
}

// Verificar se e-mail já existe
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['sucesso' => false, 'erro' => 'E-mail já cadastrado']);
    exit;
}

// Hash da senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// Inserir
$stmt = $pdo->prepare("INSERT INTO usuarios (nome, sobrenome, email, cargo, senha, data_criacao) VALUES (?, ?, ?, ?, ?, NOW())");
try {
    $stmt->execute([$nome, $sobrenome, $email, $cargo, $senhaHash]);
    echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário cadastrado com sucesso']);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao cadastrar: ' . $e->getMessage()]);
}
?>