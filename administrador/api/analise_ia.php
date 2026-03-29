<?php
// =====================================================
// ANALISE IA - KAMISHIBAI SENAI (Versão final)
// =====================================================

// Ativa exibição de erros para depuração (remova depois)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log'); // Ajuste conforme necessário

// Captura erros fatais e retorna JSON
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'erro' => 'Erro fatal no servidor',
            'detalhe' => $error['message'],
            'arquivo' => $error['file'],
            'linha' => $error['line']
        ]);
        exit;
    }
});

session_start();
header('Content-Type: application/json');

// Verifica sessão
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

// Inclui banco de dados
require_once __DIR__ . '/../../config/database.php';

// Verifica a conexão
if (!isset($pdo)) {
    echo json_encode(['erro' => 'Falha na conexão com o banco de dados']);
    exit;
}

// ================= PARÂMETROS =================
$periodo = $_GET['periodo'] ?? 'mensal';
$ano = (int) ($_GET['ano'] ?? date('Y'));
$salaFiltro = $_GET['sala'] ?? 'todas';

// ================= FUNÇÕES AUXILIARES =================
function obterTabelasSalas($pdo)
{
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $todas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $excluir = ['relatorios', 'usuarios'];
        $filtradas = array_filter($todas, function ($t) use ($excluir) {
            return !in_array($t, $excluir);
        });
        return array_values($filtradas);
    } catch (PDOException $e) {
        error_log("Erro ao obter tabelas: " . $e->getMessage());
        return [];
    }
}

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
        error_log("Erro ao descrever tabela $tabela: " . $e->getMessage());
        return [];
    }
}

// Função Holt-Winters adaptada para aceitar n == seasonal_period
function holt_winters_forecast($y, $seasonal_period = 12, $forecast_steps = 3, $alpha = 0.3, $beta = 0.2, $gamma = 0.1)
{
    $n = count($y);

    // Se houver menos de seasonal_period observações, usa média simples
    if ($n < $seasonal_period) {
        $avg = array_sum($y) / max(1, $n);
        return [
            'forecast' => array_fill(0, $forecast_steps, round($avg, 1)),
            'level' => [],
            'trend' => [],
            'seasonal' => [],
            'mae' => null
        ];
    }

    $level = [];
    $trend = [];
    $seasonal = [];

    // Inicialização da sazonalidade com as primeiras seasonal_period observações
    $avg_first = array_sum(array_slice($y, 0, $seasonal_period)) / $seasonal_period;
    for ($i = 0; $i < $seasonal_period; $i++) {
        $seasonal[$i] = $y[$i] / $avg_first;
    }

    // Nível inicial
    $level[0] = $y[0] / $seasonal[0];

    // Tendência inicial: se temos pelo menos seasonal_period+1 pontos, usa a diferença
    if ($n > $seasonal_period) {
        $trend[0] = ($y[$seasonal_period] / $seasonal[0] - $y[0] / $seasonal[0]) / $seasonal_period;
    } else {
        // Com exatamente seasonal_period pontos, estima tendência como a variação média entre o último e o primeiro
        $trend[0] = (($y[$seasonal_period - 1] / $seasonal[$seasonal_period - 1]) - ($y[0] / $seasonal[0])) / ($seasonal_period - 1);
    }

    for ($t = 1; $t < $n; $t++) {
        $idx = $t % $seasonal_period;
        $level[$t] = $alpha * ($y[$t] / $seasonal[$idx]) + (1 - $alpha) * ($level[$t - 1] + $trend[$t - 1]);
        $trend[$t] = $beta * ($level[$t] - $level[$t - 1]) + (1 - $beta) * $trend[$t - 1];
        $seasonal[$idx] = $gamma * ($y[$t] / $level[$t]) + (1 - $gamma) * $seasonal[$idx];
    }

    $last_level = end($level);
    $last_trend = end($trend);
    $forecast = [];
    for ($h = 1; $h <= $forecast_steps; $h++) {
        $idx = ($n - 1 + $h) % $seasonal_period;
        $val = ($last_level + $h * $last_trend) * $seasonal[$idx];
        $forecast[] = round(max(0, min(100, $val)), 1);
    }

    // Calcular MAE nos últimos 3 períodos (validação simples)
    $mae = null;
    if ($n >= $seasonal_period + 3) {
        $train = array_slice($y, 0, -3);
        $test = array_slice($y, -3);
        $hw_test = holt_winters_forecast($train, $seasonal_period, 3, $alpha, $beta, $gamma);
        $mae = 0;
        for ($i = 0; $i < 3; $i++) {
            $mae += abs($test[$i] - $hw_test['forecast'][$i]);
        }
        $mae = round($mae / 3, 2);
    }

    return [
        'forecast' => $forecast,
        'level' => $level,
        'trend' => $trend,
        'seasonal' => $seasonal,
        'mae' => $mae
    ];
}

// ================= COLETA DE DADOS =================
try {
    $salasPermitidas = obterTabelasSalas($pdo);
    $salas = ($salaFiltro === 'todas') ? $salasPermitidas : array_intersect($salasPermitidas, [$salaFiltro]);

    if (empty($salas)) {
        echo json_encode([
            'total_inspecoes' => 0,
            'taxa_media_problemas' => 0,
            'previsao_proximo' => 0,
            'evolucao' => ['labels' => [], 'valores' => []],
            'previsao' => ['labels' => [], 'historico' => [], 'previsao' => [], 'modelo' => 'Sem dados', 'mae' => null, 'componentes' => []],
            'ranking' => [],
            'salas' => ['labels' => [], 'valores' => []]
        ]);
        exit;
    }

    $camposPorSala = [];
    foreach ($salas as $sala) {
        $camposPorSala[$sala] = getCamposProblemas($pdo, $sala);
    }

    if ($periodo === 'mensal') {
        $condicaoData = "data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
    } else {
        // Anual: buscar dados dos últimos 5 anos (ano-4 até ano)
        $anoInicio = $ano - 4;
        $condicaoData = "YEAR(data) BETWEEN $anoInicio AND $ano";
    }
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

    // ================= EVOLUÇÃO TEMPORAL =================
    $evolucao = ['labels' => [], 'valores' => []];

    if ($periodo === 'mensal') {
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

    // ================= PREVISÃO =================
    $previsao = [
        'labels' => [],
        'historico' => [],
        'previsao' => [],
        'modelo' => 'Regressão Linear',
        'mae' => null,
        'componentes' => []
    ];

    if ($periodo === 'mensal' && !empty($evolucao['valores'])) {
        $valores = $evolucao['valores'];
        $labels = $evolucao['labels'];
        $n = count($valores);

        if ($n >= 12) { // Agora aceita 12 ou mais meses
            $hw = holt_winters_forecast($valores, 12, 3);
            $previsoes = $hw['forecast'];
            $labelsPrev = array_merge($labels, ['Prev 1', 'Prev 2', 'Prev 3']);
            $historicoCompleto = array_merge($valores, array_fill(0, 3, null));
            $previsaoValores = array_fill(0, $n, null);
            $previsaoValores = array_merge($previsaoValores, $previsoes);

            $previsao = [
                'labels' => $labelsPrev,
                'historico' => $historicoCompleto,
                'previsao' => $previsaoValores,
                'modelo' => 'Holt‑Winters (sazonalidade anual)',
                'mae' => $hw['mae'],
                'componentes' => [
                    'level' => array_slice($hw['level'], -6),
                    'trend' => array_slice($hw['trend'], -6),
                    'seasonal' => $hw['seasonal']
                ]
            ];
        } elseif ($n >= 2) {
            // Regressão linear simples
            $historico = array_slice($valores, -6);
            $indices = range(1, count($historico));
            $n_hist = count($historico);
            if ($n_hist > 1) {
                $sumX = array_sum($indices);
                $sumY = array_sum($historico);
                $sumXY = 0;
                $sumX2 = 0;
                for ($i = 0; $i < $n_hist; $i++) {
                    $sumXY += $indices[$i] * $historico[$i];
                    $sumX2 += $indices[$i] * $indices[$i];
                }
                $b = ($n_hist * $sumXY - $sumX * $sumY) / ($n_hist * $sumX2 - $sumX * $sumX);
                $a = ($sumY - $b * $sumX) / $n_hist;
                $previsoes = [];
                $labelsPrev = $labels;
                for ($i = 1; $i <= 3; $i++) {
                    $proximo = $a + $b * ($n_hist + $i);
                    $proximo = round(max(0, min(100, $proximo)), 1);
                    $previsoes[] = $proximo;
                    $labelsPrev[] = 'Prev ' . $i;
                }
                $historicoCompleto = array_merge($valores, array_fill(0, 3, null));
                $previsaoValores = array_fill(0, $n, null);
                $previsaoValores = array_merge($previsaoValores, $previsoes);
                $previsao = [
                    'labels' => $labelsPrev,
                    'historico' => $historicoCompleto,
                    'previsao' => $previsaoValores,
                    'modelo' => 'Regressão Linear (dados insuficientes)',
                    'mae' => null,
                    'componentes' => []
                ];
            }
        }
    }

    // Último valor da previsão (compatível PHP <7.3)
    $previsao_proximo = 0;
    if (!empty($previsao['previsao'])) {
        $keys = array_keys($previsao['previsao']);
        $last_key = end($keys);
        if ($last_key !== false && isset($previsao['previsao'][$last_key])) {
            $previsao_proximo = $previsao['previsao'][$last_key];
        }
    }

    // ================= RANKING =================
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
        try {
            $stmt = $pdo->query($sql);
        } catch (PDOException $e) {
            error_log("Erro na query comparativa para sala $sala: " . $e->getMessage());
            $salasTaxa[$sala] = 0;
            continue;
        }
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

    // ================= RESPOSTA FINAL =================
    $resposta = [
        'total_inspecoes' => $totalInspecoes,
        'taxa_media_problemas' => $taxaMedia,
        'previsao_proximo' => $previsao_proximo,
        'evolucao' => $evolucao,
        'previsao' => $previsao,
        'ranking' => $ranking,
        'salas' => [
            'labels' => array_keys($salasTaxa),
            'valores' => array_values($salasTaxa)
        ]
    ];

    echo json_encode($resposta);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Exceção não tratada',
        'mensagem' => $e->getMessage(),
        'arquivo' => $e->getFile(),
        'linha' => $e->getLine()
    ]);
}