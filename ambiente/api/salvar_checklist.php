<?php
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

// Lista branca de salas permitidas (evita injeção de tabelas)
$salas_permitidas = ['104a', '103d']; // Adicione aqui novas salas conforme necessário

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['nome'], $input['respostas'], $input['observacoes'], $input['sala'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos (faltam nome, respostas, observacoes ou sala)']);
    exit;
}

$nome = trim($input['nome']);
$respostas = $input['respostas'];
$observacoes = trim($input['observacoes']);
$sala = trim($input['sala']);

if (!in_array($sala, $salas_permitidas)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sala não permitida']);
    exit;
}

if (empty($nome) || empty($respostas)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nome e respostas são obrigatórios']);
    exit;
}

$agora = time();
$horaAtual = (int) date('H', $agora);
$minutoAtual = (int) date('i', $agora);
$minutosTotais = $horaAtual * 60 + $minutoAtual;

$periodos = [
    'manha' => ['inicio' => 8 * 60, 'fim' => 11 * 60 + 30],
    'tarde' => ['inicio' => 13 * 60 + 30, 'fim' => 17 * 60 + 30],
    'noite' => ['inicio' => 18 * 60 + 30, 'fim' => 22 * 60 + 30]
];

function getPeriodo($minutos, $periodos)
{
    if ($minutos < $periodos['manha']['inicio'])
        return 'manha';
    if ($minutos > $periodos['noite']['fim'])
        return 'noite';
    foreach ($periodos as $nome => $p) {
        if ($minutos >= $p['inicio'] && $minutos <= $p['fim'])
            return $nome;
    }
    return 'manha';
}

$periodoAtual = getPeriodo($minutosTotais, $periodos);
$meio = ($periodos[$periodoAtual]['inicio'] + $periodos[$periodoAtual]['fim']) / 2;
$momento = ($minutosTotais > $meio) ? 'fim' : 'inicio';

// Monta a consulta dinâmica para a sala informada
$colunas = ['nome', 'data', 'momento', 'observacoes'];
$placeholders = [':nome', ':data', ':momento', ':observacoes'];
$valores = [
    ':nome' => $nome,
    ':data' => date('Y-m-d H:i:s', $agora),
    ':momento' => $momento,
    ':observacoes' => $observacoes
];

foreach ($respostas as $item => $resposta) {
    $colunas[] = $item;
    $placeholders[] = ":$item";
    $valores[":$item"] = $resposta;
}

// Escapa o nome da tabela com backticks
$tabela = "`$sala`";
$sql = "INSERT INTO $tabela (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $placeholders) . ")";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute($valores);
    $idInspecao = $pdo->lastInsertId();

    // Insere ou atualiza na tabela relatorios
    $dataAtual = date('Y-m-d', $agora);

    $sql2 = "INSERT INTO relatorios (inspecao_id, sala, data, periodo, momento, observacoes, data_geracao)
             VALUES (?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                 observacoes = VALUES(observacoes),
                 data_geracao = NOW()";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([$idInspecao, $sala, $dataAtual, $periodoAtual, $momento, $observacoes]);

    echo json_encode(['sucesso' => true, 'id' => $idInspecao]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar: ' . $e->getMessage()]);
}
?>