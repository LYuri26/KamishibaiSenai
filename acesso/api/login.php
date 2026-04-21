<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/encryption.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email'], $input['senha'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$email = strtolower(trim($input['email']));
$senha = $input['senha'];

// Busca pelo hash do e-mail
$emailHash = hash('sha256', $email);
$stmt = $pdo->prepare("SELECT id, nome, sobrenome, email_encrypted, cargo, senha FROM usuarios WHERE email_hash = ?");
$stmt->execute([$emailHash]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($senha, $usuario['senha'])) {
    // Descriptografa o e-mail para uso na sessão
    $emailOriginal = decryptEmail($usuario['email_encrypted']);

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_sobrenome'] = $usuario['sobrenome'];
    $_SESSION['usuario_cargo'] = $usuario['cargo'];
    $_SESSION['usuario_email'] = $emailOriginal;  // e-mail legível

    echo json_encode([
        'sucesso' => true,
        'cargo' => $usuario['cargo'],
        'nome' => $usuario['nome'] . ' ' . $usuario['sobrenome']
    ]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'E-mail ou senha inválidos']);
}
?>