<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email'], $input['senha'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$email = trim($input['email']);
$senha = $input['senha'];

$stmt = $pdo->prepare("SELECT id, nome, sobrenome, email, cargo, senha FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($senha, $usuario['senha'])) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_sobrenome'] = $usuario['sobrenome'];
    $_SESSION['usuario_cargo'] = $usuario['cargo'];
    $_SESSION['usuario_email'] = $usuario['email'];

    echo json_encode([
        'sucesso' => true,
        'cargo' => $usuario['cargo'],
        'nome' => $usuario['nome'] . ' ' . $usuario['sobrenome']
    ]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'E-mail ou senha inválidos']);
}
?>