<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

// ================= VERIFICAÇÃO DE ACESSO =================
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

// ================= CONEXÃO COM BANCO =================
require_once __DIR__ . '/../../config/database.php';

// ================= PARÂMETROS =================
$periodo = $_GET['periodo'] ?? 'mensal';   // 'mensal' ou 'anual'
$ano = (int) ($_GET['ano'] ?? date('Y'));
$salaFiltro = $_GET['sala'] ?? 'todas';

$salasPermitidas = ['102c', '103d', '104a'];
$salas = ($salaFiltro === 'todas') ? $salasPermitidas : [$salaFiltro];

// ================= FUNÇÃO PARA ESCAPAR NOME DE TABELA =================
function escTabela($tabela)
{
    return "`$tabela`";
}

// ================= OBTÉM CAMPOS DE PROBLEMA DE CADA TABELA =================
function getCamposProblemas($pdo, $tabela)
{
    try {
        $stmt = $pdo->query("DESCRIBE " . escTabela($tabela));
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        // Campos que não são de problema
        $excluir = ['id', 'nome', 'data', 'momento', 'observacoes'];
        $campos = array_diff($colunas, $excluir);
        return array_values($campos);
    } catch (PDOException $e) {
        return [];
    }
}

$camposPorSala = [];
foreach ($salas as $sala) {
    $camposPorSala[$sala] = getCamposProblemas($pdo, $sala);
}

// ================= CONDIÇÃO DE DATA =================
$condicaoData = ($periodo === 'mensal')
    ? "data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"
    : "YEAR(data) = $ano";

// ================= COLETA DE INSPEÇÕES COM SEUS CAMPOS ESPECÍFICOS =================
$todasInspecoes = [];   // array com ['sala', 'data', 'campos', 'valores']
$totalInspecoes = 0;
$totalProblemas = 0;
$totalCampos = 0;

foreach ($salas as $sala) {
    $campos = $camposPorSala[$sala];
    if (empty($campos))
        continue;

    $tabela = escTabela($sala);
    $camposList = implode(', ', $campos);
    $sql = "SELECT id, data, $camposList FROM $tabela WHERE $condicaoData";
    $stmt = $pdo->query($sql);
    $inspecoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalInspecoes += count($inspecoes);

    foreach ($inspecoes as $ins) {
        $insData = [
            'sala' => $sala,
            'data' => $ins['data'],
            'campos' => $campos,
            'valores' => $ins
        ];
        $todasInspecoes[] = $insData;

        // Contagem para taxa média
        foreach ($campos as $campo) {
            $totalCampos++;
            if (isset($ins[$campo]) && strtolower(trim($ins[$campo])) === 'nao') {
                $totalProblemas++;
            }
        }
    }
}

$taxaMedia = ($totalCampos > 0) ? round(($totalProblemas / $totalCampos) * 100, 1) : 0;

// ================= EVOLUÇÃO TEMPORAL =================
$evolucao = ['labels' => [], 'valores' => []];

if ($periodo === 'mensal') {
    // Agrupa por mês (YYYY-MM)
    $porMes = [];
    foreach ($todasInspecoes as $ins) {
        $mes = date('Y-m', strtotime($ins['data']));
        if (!isset($porMes[$mes])) {
            $porMes[$mes] = ['total_campos' => 0, 'problemas' => 0];
        }
        foreach ($ins['campos'] as $campo) {
            $porMes[$mes]['total_campos']++;
            if (isset($ins['valores'][$campo]) && strtolower(trim($ins['valores'][$campo])) === 'nao') {
                $porMes[$mes]['problemas']++;
            }
        }
    }
    ksort($porMes);
    foreach ($porMes as $mes => $d) {
        $taxa = ($d['total_campos'] > 0) ? round(($d['problemas'] / $d['total_campos']) * 100, 1) : 0;
        $evolucao['labels'][] = $mes;
        $evolucao['valores'][] = $taxa;
    }
} else {
    // Anual: últimos 5 anos
    $anos = range($ano - 4, $ano);
    $porAno = [];
    foreach ($anos as $a) {
        $porAno[$a] = ['total_campos' => 0, 'problemas' => 0];
    }
    foreach ($todasInspecoes as $ins) {
        $anoIns = date('Y', strtotime($ins['data']));
        if (isset($porAno[$anoIns])) {
            foreach ($ins['campos'] as $campo) {
                $porAno[$anoIns]['total_campos']++;
                if (isset($ins['valores'][$campo]) && strtolower(trim($ins['valores'][$campo])) === 'nao') {
                    $porAno[$anoIns]['problemas']++;
                }
            }
        }
    }
    foreach ($anos as $a) {
        $d = $porAno[$a];
        $taxa = ($d['total_campos'] > 0) ? round(($d['problemas'] / $d['total_campos']) * 100, 1) : 0;
        $evolucao['labels'][] = (string) $a;
        $evolucao['valores'][] = $taxa;
    }
}

// ================= PREVISÃO (REGRESSÃO LINEAR) =================
$previsao = ['labels' => [], 'historico' => [], 'previsao' => []];
if ($periodo === 'mensal' && count($evolucao['valores']) >= 2) {
    $valores = $evolucao['valores'];
    $labels = $evolucao['labels'];
    // Usa últimos 6 valores (ou menos se não houver 6)
    $historico = array_slice($valores, -6);
    $indices = range(1, count($historico));
    $n = count($historico);
    $sumX = array_sum($indices);
    $sumY = array_sum($historico);
    $sumXY = 0;
    $sumX2 = 0;
    for ($i = 0; $i < $n; $i++) {
        $sumXY += $indices[$i] * $historico[$i];
        $sumX2 += $indices[$i] * $indices[$i];
    }
    $b = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    $a = ($sumY - $b * $sumX) / $n;
    $proximo = $a + $b * ($n + 1);
    $proximo = round(max(0, min(100, $proximo)), 1);

    $labelsPrev = array_merge($labels, ['Previsão']);
    $valoresPrev = array_merge($valores, [$proximo]);

    $previsao = [
        'labels' => $labelsPrev,
        'historico' => array_merge($valores, [null]),
        'previsao' => array_fill(0, count($valores), null) + [$proximo]
    ];
}

// ================= RANKING DOS ITENS MAIS PROBLEMÁTICOS =================
$ranking = [];
$ocorrenciasPorItem = [];
$totalRegistrosPorItem = [];

foreach ($todasInspecoes as $ins) {
    foreach ($ins['campos'] as $campo) {
        $itemNome = ucwords(str_replace('_', ' ', $campo));
        if (!isset($totalRegistrosPorItem[$itemNome])) {
            $totalRegistrosPorItem[$itemNome] = 0;
            $ocorrenciasPorItem[$itemNome] = 0;
        }
        $totalRegistrosPorItem[$itemNome]++;
        if (isset($ins['valores'][$campo]) && strtolower(trim($ins['valores'][$campo])) === 'nao') {
            $ocorrenciasPorItem[$itemNome]++;
        }
    }
}

foreach ($totalRegistrosPorItem as $item => $total) {
    $incidencia = round(($ocorrenciasPorItem[$item] / $total) * 100, 1);
    $ranking[] = [
        'item' => $item,
        'incidencia' => $incidencia,
        'ocorrencias' => $ocorrenciasPorItem[$item]
    ];
}
usort($ranking, function ($a, $b) {
    return $b['incidencia'] <=> $a['incidencia'];
});
$ranking = array_slice($ranking, 0, 10);

// ================= COMPARATIVO POR SALA =================
$salasTaxa = [];
foreach ($salasPermitidas as $sala) {
    $campos = $camposPorSala[$sala];
    if (empty($campos)) {
        $salasTaxa[$sala] = 0;
        continue;
    }
    $tabela = escTabela($sala);
    $camposList = implode(', ', $campos);
    $sql = "SELECT $camposList FROM $tabela WHERE $condicaoData";
    $stmt = $pdo->query($sql);
    $insSala = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalCamposSala = 0;
    $problemasSala = 0;
    foreach ($insSala as $ins) {
        foreach ($campos as $campo) {
            $totalCamposSala++;
            if (isset($ins[$campo]) && strtolower(trim($ins[$campo])) === 'nao') {
                $problemasSala++;
            }
        }
    }
    $taxaSala = ($totalCamposSala > 0) ? round(($problemasSala / $totalCamposSala) * 100, 1) : 0;
    $salasTaxa[$sala] = $taxaSala;
}

// ================= MONTA RESPOSTA =================
$dados = [
    'total_inspecoes' => $totalInspecoes,
    'taxa_media_problemas' => $taxaMedia,
    'previsao_proximo' => $previsao['previsao'][array_key_last($previsao['previsao'])] ?? 0,
    'evolucao' => $evolucao,
    'previsao' => $previsao,
    'ranking' => $ranking,
    'salas' => [
        'labels' => array_keys($salasTaxa),
        'valores' => array_values($salasTaxa)
    ]
];

echo json_encode($dados);
?>