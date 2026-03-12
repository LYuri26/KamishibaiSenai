<?php
session_start();
header('Content-Type: application/json');

// Verifica se usuário está logado e é gerência
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'gerencia') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? 0;
$sala = $_GET['sala'] ?? '';

if (!$id || !$sala) {
    echo json_encode(['erro' => 'ID ou sala não fornecidos']);
    exit;
}

// Lista branca de salas permitidas
$salas_permitidas = ['104a', '103d'];
if (!in_array($sala, $salas_permitidas)) {
    echo json_encode(['erro' => 'Sala inválida']);
    exit;
}

$tabela = "`$sala`";
$stmt = $pdo->prepare("SELECT * FROM $tabela WHERE id = ?");
$stmt->execute([$id]);
$inspecao = $stmt->fetch(PDO::FETCH_ASSOC);

if ($inspecao) {
    // Adiciona a sala ao resultado para uso no frontend
    $inspecao['sala'] = $sala;
    echo json_encode($inspecao);
} else {
    echo json_encode(['erro' => 'Inspeção não encontrada']);
}
?>