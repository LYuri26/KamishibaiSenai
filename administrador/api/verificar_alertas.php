<?php
ob_start();
session_start();

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/database.php';

date_default_timezone_set('America/Sao_Paulo');

// ================= VALIDAÇÃO PDO =================
if (!isset($pdo)) {
    ob_clean();
    echo json_encode(['erro' => 'Falha na conexão com banco']);
    exit;
}

// ================= HORÁRIO ATUAL =================
$agora = time();
$hora = (int) date('H', $agora);
$min = (int) date('i', $agora);
$minutos = $hora * 60 + $min;

// ================= PERÍODOS =================
$periodos = [
    'manha' => ['inicio' => 480, 'fim' => 690],
    'tarde' => ['inicio' => 810, 'fim' => 1050],
    'noite' => ['inicio' => 1110, 'fim' => 1350]
];

// ================= IDENTIFICA PERÍODO =================
function getPeriodoAtual($minutos, $periodos) {
    foreach ($periodos as $nome => $p) {
        if ($minutos >= $p['inicio'] && $minutos <= $p['fim']) {
            return $nome;
        }
    }
    return null;
}

$periodoAtual = getPeriodoAtual($minutos, $periodos);

// Fora do horário → sem alerta
if (!$periodoAtual) {
    ob_clean();
    echo json_encode([]);
    exit;
}

// ================= DEFINE MOMENTO =================
$meio = ($periodos[$periodoAtual]['inicio'] + $periodos[$periodoAtual]['fim']) / 2;
$momentoAtual = ($minutos > $meio) ? 'fim' : 'inicio';

// ================= SALAS DINÂMICAS =================
try {
    $stmtSalas = $pdo->query("SELECT DISTINCT sala FROM relatorios");
    $salas = $stmtSalas ? $stmtSalas->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (Exception $e) {
    $salas = [];
}

// fallback seguro
if (empty($salas)) {
    $salas = ['102c', '103d', '104a'];
}

// ================= DATA =================
$dataHoje = date('Y-m-d');

// ================= PROCESSAMENTO =================
$alertas = [];

foreach ($salas as $sala) {

    $momentosParaVerificar = ['inicio'];

    if ($momentoAtual === 'fim') {
        $momentosParaVerificar[] = 'fim';
    }

    foreach ($momentosParaVerificar as $momento) {

        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM relatorios
                WHERE sala = ?
                AND data = ?
                AND periodo = ?
                AND momento = ?
            ");

            $stmt->execute([$sala, $dataHoje, $periodoAtual, $momento]);

            $existe = $stmt->fetchColumn();

            if (!$existe) {
                $alertas[] = [
                    'mensagem' => "Sala {$sala} - {$periodoAtual} ({$momento}) sem inspeção"
                ];
            }

        } catch (Exception $e) {
            // falha isolada não derruba o sistema
            continue;
        }
    }
}

// ================= SAÍDA LIMPA =================
ob_clean();
echo json_encode($alertas);
exit;