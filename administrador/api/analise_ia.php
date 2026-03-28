<?php
ini_set('display_errors', 0); // Desliga exibição de erros para não poluir JSON
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

// Verificação de acesso
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Parâmetros
$periodo = $_GET['periodo'] ?? 'mensal';
$ano = (int) ($_GET['ano'] ?? date('Y'));
$salaFiltro = $_GET['sala'] ?? 'todas';

// Obtém todas as tabelas que são salas de inspeção
function obterTabelasSalas($pdo)
{
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $todas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $excluir = ['relatorios', 'usuarios'];
        return array_values(array_filter($todas, function ($t) use ($excluir) {
            return !in_array($t, $excluir);
        }));
    } catch (PDOException $e) {
        return [];
    }
}

$salasPermitidas = obterTabelasSalas($pdo);
$salas = ($salaFiltro === 'todas') ? $salasPermitidas : array_intersect($salasPermitidas, [$salaFiltro]);

function escTabela($tabela)
{
    return "`$tabela`";
}

function getCamposProblemas($pdo, $tabela)
{
    try {
        $stmt = $pdo->query("DESCRIBE " . escTabela($tabela));
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $excluir = ['id', 'nome', 'data', 'momento', 'observacoes'];
        return array_values(array_diff($colunas, $excluir));
    } catch (PDOException $e) {
        return [];
    }
}

$camposPorSala = [];
foreach ($salas as $sala) {
    $camposPorSala[$sala] = getCamposProblemas($pdo, $sala);
}

$condicaoData = ($periodo === 'mensal')
    ? "data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"
    : "YEAR(data) = $ano";

// Coleta de dados
$todasInspecoes = [];
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
        $todasInspecoes[] = [
            'sala' => $sala,
            'data' => $ins['data'],
            'campos' => $campos,
            'valores' => $ins
        ];
        foreach ($campos as $campo) {
            $totalCampos++;
            if (isset($ins[$campo]) && strtolower(trim($ins[$campo])) === 'nao') {
                $totalProblemas++;
            }
        }
    }
}
$taxaMedia = ($totalCampos > 0) ? round(($totalProblemas / $totalCampos) * 100, 1) : 0;

// Evolução temporal
$evolucao = ['labels' => [], 'valores' => []];
if ($periodo === 'mensal') {
    $porMes = [];
    foreach ($todasInspecoes as $ins) {
        $mes = date('Y-m', strtotime($ins['data']));
        if (!isset($porMes[$mes]))
            $porMes[$mes] = ['total_campos' => 0, 'problemas' => 0];
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
    $anos = range($ano - 4, $ano);
    $porAno = array_fill_keys($anos, ['total_campos' => 0, 'problemas' => 0]);
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

// Previsão (regressão linear para os próximos 3 períodos)
$previsao = ['labels' => [], 'historico' => [], 'previsao' => []];
if ($periodo === 'mensal' && count($evolucao['valores']) >= 2) {
    $valores = $evolucao['valores'];
    $labels = $evolucao['labels'];
    $historico = array_slice($valores, -6);
    $indices = range(1, count($historico));
    $n = count($historico);
    if ($n > 1) {
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

        // Previsões para os próximos 3 períodos
        $previsoes = [];
        $labelsPrev = $labels;
        for ($i = 1; $i <= 3; $i++) {
            $proximo = $a + $b * ($n + $i);
            $proximo = round(max(0, min(100, $proximo)), 1);
            $previsoes[] = $proximo;
            $labelsPrev[] = 'Prev ' . $i;
        }

        $historicoCompleto = array_merge($valores, array_fill(0, 3, null));
        $previsaoValores = array_fill(0, count($valores), null);
        $previsaoValores = array_merge($previsaoValores, $previsoes);

        $previsao = [
            'labels' => $labelsPrev,
            'historico' => $historicoCompleto,
            'previsao' => $previsaoValores
        ];
    }
}

// Ranking
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
usort($ranking, fn($a, $b) => $b['incidencia'] <=> $a['incidencia']);
$ranking = array_slice($ranking, 0, 10);

// Comparativo por sala
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

// Resposta
$resposta = [
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

echo json_encode($resposta);
?>