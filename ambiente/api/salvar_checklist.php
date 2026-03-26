<?php
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

// ================= CONFIGURAÇÃO =================
$salas_permitidas = ['104a', '103d'];

// ================= ENTRADA =================
$nome = $_POST['nome'] ?? '';
$respostas = json_decode($_POST['respostas'] ?? '{}', true);
$observacoes = $_POST['observacoes'] ?? '';
$sala = $_POST['sala'] ?? '';

if (empty($nome) || empty($respostas) || empty($sala)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

if (!in_array($sala, $salas_permitidas)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sala não permitida']);
    exit;
}

// ================= PERÍODO =================
$agora = time();
$horaAtual = (int) date('H', $agora);
$minutoAtual = (int) date('i', $agora);
$minutosTotais = $horaAtual * 60 + $minutoAtual;

$periodos = [
    'manha' => ['inicio' => 480, 'fim' => 690],
    'tarde' => ['inicio' => 810, 'fim' => 1050],
    'noite' => ['inicio' => 1110, 'fim' => 1350]
];

function getPeriodo($minutos, $periodos) {
    foreach ($periodos as $nome => $p) {
        if ($minutos >= $p['inicio'] && $minutos <= $p['fim']) {
            return $nome;
        }
    }
    return 'manha';
}

$periodoAtual = getPeriodo($minutosTotais, $periodos);
$meio = ($periodos[$periodoAtual]['inicio'] + $periodos[$periodoAtual]['fim']) / 2;
$momento = ($minutosTotais > $meio) ? 'fim' : 'inicio';

// ================= DUPLICIDADE =================
$dataHoje = date('Y-m-d', $agora);
$inicioTimestamp = strtotime($dataHoje) + $periodos[$periodoAtual]['inicio'] * 60;
$fimTimestamp = strtotime($dataHoje) + $periodos[$periodoAtual]['fim'] * 60;

$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `$sala` WHERE data BETWEEN ? AND ? AND momento = ?");
$checkStmt->execute([
    date('Y-m-d H:i:s', $inicioTimestamp),
    date('Y-m-d H:i:s', $fimTimestamp),
    $momento
]);

if ($checkStmt->fetchColumn() > 0) {
    echo json_encode(['sucesso' => false, 'erro' => "Inspeção já realizada neste período"]);
    exit;
}

// ================= UPLOAD DE IMAGENS =================
$pathsImagens = [];

if (!empty($_FILES['imagens']['name'][0])) {

    $pastaBase = __DIR__ . "/../../assets/images/";

    $pastaSala = ($sala === '104a')
        ? $pastaBase . "sala104a/"
        : $pastaBase . "laboratorio103d/";

    if (!is_dir($pastaSala)) {
        mkdir($pastaSala, 0777, true);
    }

    foreach ($_FILES['imagens']['tmp_name'] as $index => $tmpName) {

        if (!is_uploaded_file($tmpName)) continue;

        $ext = pathinfo($_FILES['imagens']['name'][$index], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . "." . $ext;

        $caminhoFinal = $pastaSala . $nomeArquivo;

        if (move_uploaded_file($tmpName, $caminhoFinal)) {

            $pathsImagens[] = "assets/images/" .
                ($sala === '104a' ? "sala104a/" : "laboratorio103d/") .
                $nomeArquivo;
        }
    }
}

$imagensJson = json_encode($pathsImagens);

// ================= INSERT INSPEÇÃO =================
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

$sql = "INSERT INTO `$sala` (" . implode(', ', $colunas) . ")
        VALUES (" . implode(', ', $placeholders) . ")";

$stmt = $pdo->prepare($sql);

// ================= EXECUÇÃO =================
try {
    $stmt->execute($valores);
    $idInspecao = $pdo->lastInsertId();

    // ================= RELATÓRIO =================
    $sql2 = "INSERT INTO relatorios 
        (inspecao_id, sala, data, periodo, momento, observacoes, imagens, data_geracao)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            observacoes = VALUES(observacoes),
            imagens = VALUES(imagens),
            data_geracao = NOW()";

    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        $idInspecao,
        $sala,
        $dataHoje,
        $periodoAtual,
        $momento,
        $observacoes,
        $imagensJson
    ]);

    echo json_encode(['sucesso' => true, 'id' => $idInspecao]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}