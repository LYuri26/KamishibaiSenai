<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['nome'], $input['respostas'], $input['observacoes'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$nome = trim($input['nome']);
$respostas = $input['respostas'];
$observacoes = trim($input['observacoes']);

if (empty($nome) || empty($respostas)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nome e respostas são obrigatórios']);
    exit;
}

// Prepara inserção
$colunas = ['nome', 'data', 'observacoes'];
$placeholders = [':nome', ':data', ':observacoes'];
$valores = [
    ':nome' => $nome,
    ':data' => date('Y-m-d H:i:s'),
    ':observacoes' => $observacoes
];

foreach ($respostas as $item => $resposta) {
    $colunas[] = $item;
    $placeholders[] = ":$item";
    $valores[":$item"] = $resposta;
}

$sql = "INSERT INTO `104a` (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $placeholders) . ")";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute($valores);
    echo json_encode(['sucesso' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar: ' . $e->getMessage()]);
}
?>