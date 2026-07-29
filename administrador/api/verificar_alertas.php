<?php
/**
 * verificar_alertas.php - API de Alertas de Rotas Não Inspecionadas
 * Varre os ambientes em tempo real comparando horários letivos com vistorias registradas hoje.
 */

ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');

// Desativa exibição direta de erros PHP
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../config/database.php';

date_default_timezone_set('America/Sao_Paulo');

// ================= VALIDAÇÃO DE CONEXÃO =================
if (!isset($pdo) || !($pdo instanceof PDO)) {
    ob_clean();
    echo json_encode(['erro' => 'Falha na conexão interna com o banco de dados.']);
    exit;
}

// ================= HORÁRIO ATUAL (EM MINUTOS) =================
$agora = time();
$hora = (int) date('H', $agora);
$min = (int) date('i', $agora);
$minutos = $hora * 60 + $min;

// ================= INTERVALOS DOS PERÍODOS LETIVOS (MINUTOS) =================
$periodos = [
    'manha' => ['inicio' => 480, 'fim' => 690],   // 08:00 às 11:30 (meio-dia letivo)
    'tarde' => ['inicio' => 810, 'fim' => 1050],  // 13:30 às 17:30
    'noite' => ['inicio' => 1110, 'fim' => 1350]  // 18:30 às 22:30
];

// ================= IDENTIFICA O PERÍODO ATUAL =================
function getPeriodoAtual($minutos, $periodos)
{
    foreach ($periodos as $nome => $p) {
        if ($minutos >= $p['inicio'] && $minutos <= $p['fim']) {
            return $nome;
        }
    }
    return null;
}

$periodoAtual = getPeriodoAtual($minutos, $periodos);

// Se estiver fora de um horário de aula regular, não gera alertas
if (!$periodoAtual) {
    ob_clean();
    echo json_encode([]);
    exit;
}

// ================= DEFINE O MOMENTO OPERACIONAL (INÍCIO / FIM) =================
$meio = ($periodos[$periodoAtual]['inicio'] + $periodos[$periodoAtual]['fim']) / 2;
$momentoAtual = ($minutos > $meio) ? 'fim' : 'inicio';

// ================= MAPEAMENTO DE AMBIENTES ATIVOS =================
try {
    $stmtSalas = $pdo->query("SELECT DISTINCT sala FROM relatorios");
    $salas = $stmtSalas ? $stmtSalas->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (Exception $e) {
    $salas = [];
}

// Fallback preventivo de ambientes para instalações novas ou limpas
if (empty($salas)) {
    $salas = ['101d', '102c', '102d', '103d', '104a'];
}

// ================= FILTRAGEM TEMPORAL =================
$dataHoje = date('Y-m-d');
$alertas = [];

foreach ($salas as $sala) {
    // Se o turno atual estiver na segunda metade ('fim'), o sistema verifica tanto o 'inicio' quanto o 'fim'
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

            // Se não encontrar nenhuma auditoria correspondente, dispara o alerta
            if (!$existe) {
                $momentoLegivel = $momento === 'inicio' ? "Início" : "Fim";
                $periodoLegivel = ucfirst($periodoAtual);
                $alertas[] = [
                    'mensagem' => "Sala {$sala} - Turno {$periodoLegivel} ({$momentoLegivel}) sem inspeção registrada."
                ];
            }

        } catch (Exception $e) {
            // Falha isolada de consulta em um laço não compromete o fluxo principal
            continue;
        }
    }
}

// ================= RETORNO DE SINALIZAÇÃO DOS ALERTAS =================
ob_clean();
echo json_encode($alertas);
exit;