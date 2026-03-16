<?php

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'gerencia') {

    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;

}

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;
$sala = $_GET['sala'] ?? null;

if (!$id || !$sala) {

    echo json_encode(['erro' => 'ID ou sala não fornecidos']);
    exit;

}

$tabelasPermitidas = ['104a', '103d'];

if (!in_array($sala, $tabelasPermitidas)) {

    echo json_encode(['erro' => 'Sala inválida']);
    exit;

}

try {

    $stmt = $pdo->prepare("SELECT * FROM `$sala` WHERE id = ?");

    $stmt->execute([$id]);

    $inspecao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inspecao) {

        echo json_encode(['erro' => 'Inspeção não encontrada']);
        exit;

    }

    $inspecao['sala'] = $sala;

    echo json_encode($inspecao);

} catch (PDOException $e) {

    echo json_encode([
        'erro' => 'Erro no banco de dados',
        'detalhe' => $e->getMessage()
    ]);

}