<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

echo json_encode([
    'id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['usuario_nome'] ?? '',
    'sobrenome' => $_SESSION['usuario_sobrenome'] ?? '',
    'cargo' => $_SESSION['usuario_cargo'] ?? '',
    'email' => $_SESSION['usuario_email'] ?? ''
]);
?>