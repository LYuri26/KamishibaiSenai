<?php
// =====================================================
// ANALISE IA - KAMISHIBAI (Versão de Alta Precisão)
// =====================================================
// Melhorias:
// - Mínimo de 6 meses para Holt-Winters (2 ciclos)
// - Otimização automática de parâmetros (α,β,γ)
// - Intervalos de confiança
// - Detecção de outliers
// - Métricas robustas (MAE, MAPE, RMSE)
// - Fallback inteligente (Holt, Média Móvel)
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 0); // Desliga exibição direta (log apenas)
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['erro' => 'Erro interno', 'detalhe' => $error['message']]);
        exit;
    }
});

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] !== 'lider') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
if (!isset($pdo)) {
    echo json_encode(['erro' => 'Falha na conexão com o banco']);
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
        return array_values(array_filter($todas, fn($t) => !in_array($t, $excluir)));
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
        error_log("Erro ao descrever $tabela: " . $e->getMessage());
        return [];
    }
}

/**
 * Detecta outliers usando o método IQR (Intervalo Interquartil)
 */
function detectOutliers($data)
{
    if (count($data) < 4)
        return [];
    sort($data);
    $q1 = $data[floor(count($data) * 0.25)];
    $q3 = $data[floor(count($data) * 0.75)];
    $iqr = $q3 - $q1;
    $lower = $q1 - 1.5 * $iqr;
    $upper = $q3 + 1.5 * $iqr;
    $outliers = [];
    foreach ($data as $i => $val) {
        if ($val < $lower || $val > $upper)
            $outliers[] = $i;
    }
    return $outliers;
}

/**
 * Suavização Exponencial Simples (nível apenas)
 */
function simpleExpSmoothing($y, $alpha = 0.3, $forecast_steps = 3)
{
    $n = count($y);
    if ($n === 0)
        return array_fill(0, $forecast_steps, 0);
    $level = $y[0];
    for ($t = 1; $t < $n; $t++) {
        $level = $alpha * $y[$t] + (1 - $alpha) * $level;
    }
    $forecast = array_fill(0, $forecast_steps, round($level, 1));
    return ['forecast' => $forecast, 'level' => $level];
}

/**
 * Suavização de Holt (nível + tendência)
 */
function holtExpSmoothing($y, $alpha = 0.3, $beta = 0.2, $forecast_steps = 3)
{
    $n = count($y);
    if ($n < 2)
        return simpleExpSmoothing($y, $alpha, $forecast_steps);

    $level = $y[0];
    $trend = $y[1] - $y[0];
    for ($t = 1; $t < $n; $t++) {
        $last_level = $level;
        $level = $alpha * $y[$t] + (1 - $alpha) * ($level + $trend);
        $trend = $beta * ($level - $last_level) + (1 - $beta) * $trend;
    }
    $forecast = [];
    for ($h = 1; $h <= $forecast_steps; $h++) {
        $val = $level + $h * $trend;
        $forecast[] = round(max(0, min(100, $val)), 1);
    }
    return ['forecast' => $forecast, 'level' => $level, 'trend' => $trend];
}

/**
 * Holt-Winters com período sazonal fixo (3 meses)
 * Otimização de parâmetros por grid search
 */
function holtWintersOptimized($y, $seasonal_period = 3, $forecast_steps = 3, $alpha_grid = null, $beta_grid = null, $gamma_grid = null)
{
    $n = count($y);
    // Exige pelo menos 2 ciclos completos para evitar overfitting
    if ($n < $seasonal_period * 2) {
        return ['forecast' => null, 'error' => 'Dados insuficientes (mínimo ' . ($seasonal_period * 2) . ' meses)'];
    }

    // Grid de parâmetros (valores comuns para séries não muito voláteis)
    if ($alpha_grid === null)
        $alpha_grid = [0.1, 0.3, 0.5, 0.7, 0.9];
    if ($beta_grid === null)
        $beta_grid = [0.05, 0.1, 0.2, 0.3];
    if ($gamma_grid === null)
        $gamma_grid = [0.05, 0.1, 0.2, 0.3];

    $best_mae = INF;
    $best_params = null;
    $best_forecast = null;
    $best_components = null;

    // Validação simples: últimos 3 pontos como teste
    $train = array_slice($y, 0, -3);
    $test = array_slice($y, -3);

    foreach ($alpha_grid as $alpha) {
        foreach ($beta_grid as $beta) {
            foreach ($gamma_grid as $gamma) {
                $result = holtWintersCore($train, $seasonal_period, 3, $alpha, $beta, $gamma);
                if (isset($result['forecast']) && count($result['forecast']) === 3) {
                    $mae = 0;
                    for ($i = 0; $i < 3; $i++) {
                        $mae += abs($test[$i] - $result['forecast'][$i]);
                    }
                    $mae /= 3;
                    if ($mae < $best_mae) {
                        $best_mae = $mae;
                        $best_params = ['alpha' => $alpha, 'beta' => $beta, 'gamma' => $gamma];
                        // Roda o modelo completo com os melhores parâmetros
                        $full = holtWintersCore($y, $seasonal_period, $forecast_steps, $alpha, $beta, $gamma);
                        $best_forecast = $full['forecast'];
                        $best_components = [
                            'level' => array_slice($full['level'], -6),
                            'trend' => array_slice($full['trend'], -6),
                            'seasonal' => $full['seasonal']
                        ];
                    }
                }
            }
        }
    }

    if ($best_params === null) {
        return ['forecast' => null, 'error' => 'Não foi possível ajustar o modelo'];
    }

    // Calcula métricas de qualidade no conjunto de validação
    $mae = $best_mae;
    $mape = 0;
    for ($i = 0; $i < 3; $i++) {
        if ($test[$i] != 0)
            $mape += abs(($test[$i] - $best_forecast[$i]) / $test[$i]);
    }
    $mape = ($mape / 3) * 100;
    $rmse = sqrt(array_sum(array_map(function ($a, $b) {
        return pow($a - $b, 2);
    }, $test, $best_forecast)) / 3);

    // Intervalo de confiança (baseado no erro residual histórico)
    $residuals = [];
    // Pega resíduos dos últimos 6 pontos
    $full_train = array_slice($y, 0, -3);
    $full_forecast = holtWintersCore($full_train, $seasonal_period, 3, $best_params['alpha'], $best_params['beta'], $best_params['gamma']);
    for ($i = 0; $i < 3; $i++) {
        $residuals[] = $test[$i] - $full_forecast['forecast'][$i];
    }
    $std_res = (count($residuals) > 1) ? sqrt(array_sum(array_map(function ($r) use ($residuals) {
        return pow($r - array_sum($residuals) / count($residuals), 2);
    }, $residuals)) / (count($residuals) - 1)) : 5;
    $z80 = 1.28;
    $z95 = 1.96;

    $confidence80 = [
        'lower' => array_map(fn($f) => round(max(0, $f - $z80 * $std_res), 1), $best_forecast),
        'upper' => array_map(fn($f) => round(min(100, $f + $z80 * $std_res), 1), $best_forecast)
    ];
    $confidence95 = [
        'lower' => array_map(fn($f) => round(max(0, $f - $z95 * $std_res), 1), $best_forecast),
        'upper' => array_map(fn($f) => round(min(100, $f + $z95 * $std_res), 1), $best_forecast)
    ];

    return [
        'forecast' => $best_forecast,
        'params' => $best_params,
        'mae' => round($mae, 2),
        'mape' => round($mape, 2),
        'rmse' => round($rmse, 2),
        'confidence80' => $confidence80,
        'confidence95' => $confidence95,
        'components' => $best_components,
        'std_residual' => round($std_res, 2)
    ];
}

/**
 * Núcleo do Holt-Winters (sem otimização)
 */
function holtWintersCore($y, $seasonal_period, $forecast_steps, $alpha, $beta, $gamma)
{
    $n = count($y);
    if ($n < $seasonal_period) {
        $avg = array_sum($y) / max(1, $n);
        return ['forecast' => array_fill(0, $forecast_steps, round($avg, 1)), 'level' => [], 'trend' => [], 'seasonal' => []];
    }

    $level = [];
    $trend = [];
    $seasonal = [];

    // Inicialização
    $avg_first = array_sum(array_slice($y, 0, $seasonal_period)) / $seasonal_period;
    for ($i = 0; $i < $seasonal_period; $i++) {
        $seasonal[$i] = $y[$i] / $avg_first;
    }
    $level[0] = $y[0] / $seasonal[0];
    $trend[0] = ($y[$seasonal_period] / $seasonal[0] - $y[0] / $seasonal[0]) / $seasonal_period;

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

    return ['forecast' => $forecast, 'level' => $level, 'trend' => $trend, 'seasonal' => $seasonal];
}

/**
 * Decide o melhor modelo com base nos dados
 */
function selectBestModel($y, $forecast_steps = 3)
{
    $n = count($y);
    if ($n < 2) {
        return ['type' => 'media', 'forecast' => array_fill(0, $forecast_steps, round(array_sum($y) / max(1, $n), 1))];
    }

    // Teste de tendência
    $indices = range(1, $n);
    $cor = correlation($indices, $y);
    $hasTrend = abs($cor) > 0.3;

    // Teste de sazonalidade: autocorrelação no lag = 3 (trimestral)
    $autoCorr3 = autoCorrelation($y, 3);
    // Relaxamos o limiar: agora aceitamos > 0.2 (antes era 0.4)
    $hasSeasonality = abs($autoCorr3) > 0.2 && $n >= 6;

    // Se tivermos pelo menos 2 ciclos completos (6 meses), tentamos Holt-Winters
    // independentemente da autocorrelação – o grid search vai decidir se o modelo é bom.
    if ($n >= 6) {
        $hw = holtWintersOptimized($y, 3, $forecast_steps);
        // Se o modelo convergiu e o MAE for aceitável (menor que 20%, por exemplo)
        if ($hw['forecast'] !== null && $hw['mae'] < 20) {
            return ['type' => 'holt_winters', 'forecast' => $hw['forecast'], 'details' => $hw];
        }
        // Se o MAE for alto, ainda assim podemos usar Holt-Winters, mas com aviso
        // Aqui optamos por não usar se o erro for muito grande.
    }

    // Fallback para Holt (nível + tendência) se houver tendência clara
    if ($hasTrend && $n >= 3) {
        $holt = holtExpSmoothing($y, 0.3, 0.2, $forecast_steps);
        return ['type' => 'holt', 'forecast' => $holt['forecast']];
    }

    // Último recurso: média móvel ponderada
    $weights = [0.5, 0.3, 0.2];
    $lastVals = array_slice($y, -min(3, $n));
    $weights = array_slice($weights, 0, count($lastVals));
    $weights = array_map(fn($w) => $w / array_sum($weights), $weights);
    $avg = 0;
    foreach ($lastVals as $i => $val) {
        $avg += $val * $weights[$i];
    }
    return ['type' => 'ponderada', 'forecast' => array_fill(0, $forecast_steps, round($avg, 1))];
}

function correlation($x, $y)
{
    $n = count($x);
    $meanX = array_sum($x) / $n;
    $meanY = array_sum($y) / $n;
    $num = 0;
    $denX = 0;
    $denY = 0;
    for ($i = 0; $i < $n; $i++) {
        $dx = $x[$i] - $meanX;
        $dy = $y[$i] - $meanY;
        $num += $dx * $dy;
        $denX += $dx * $dx;
        $denY += $dy * $dy;
    }
    if ($denX == 0 || $denY == 0)
        return 0;
    return $num / sqrt($denX * $denY);
}

function autoCorrelation($y, $lag)
{
    $n = count($y);
    if ($n <= $lag)
        return 0;
    $mean = array_sum($y) / $n;
    $num = 0;
    $den = 0;
    for ($i = 0; $i < $n - $lag; $i++) {
        $num += ($y[$i] - $mean) * ($y[$i + $lag] - $mean);
    }
    for ($i = 0; $i < $n; $i++) {
        $den += pow($y[$i] - $mean, 2);
    }
    if ($den == 0)
        return 0;
    return $num / $den;
}

// ================= COLETA DE DADOS =================
try {
    $salasPermitidas = obterTabelasSalas($pdo);
    $salas = ($salaFiltro === 'todas') ? $salasPermitidas : array_intersect($salasPermitidas, [$salaFiltro]);

    if (empty($salas)) { /* retorno vazio padrão */
    }

    $camposPorSala = [];
    foreach ($salas as $sala) {
        $camposPorSala[$sala] = getCamposProblemas($pdo, $sala);
    }

    if ($periodo === 'mensal') {
        $condicaoData = "data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
    } else {
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
        // Lógica anual (mantida igual)
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

    // ================= PREVISÃO INTELIGENTE =================
    $previsao = [
        'labels' => [],
        'historico' => [],
        'previsao' => [],
        'modelo' => 'Nenhum',
        'tipo_modelo' => 'nenhum',   // novo campo
        'mae' => null,
        'mape' => null,
        'rmse' => null,
        'confidence80' => [],
        'confidence95' => [],
        'componentes' => null        // agora pode ser null ou array
    ];

    if ($periodo === 'mensal' && !empty($evolucao['valores'])) {
        $valores = $evolucao['valores'];
        $labels = $evolucao['labels'];
        $n = count($valores);

        // Remove outliers
        $outliers = detectOutliers($valores);
        if (!empty($outliers)) {
            foreach ($outliers as $idx) {
                if ($idx > 0 && $idx < $n - 1) {
                    $valores[$idx] = ($valores[$idx - 1] + $valores[$idx + 1]) / 2;
                }
            }
        }

        $model = selectBestModel($valores, 3);

        if ($model['type'] === 'holt_winters') {
            $details = $model['details'];
            $previsao = [
                'labels' => array_merge($labels, ['Prev 1', 'Prev 2', 'Prev 3']),
                'historico' => array_merge($valores, array_fill(0, 3, null)),
                'previsao' => array_merge(array_fill(0, $n, null), $details['forecast']),
                'modelo' => 'Holt‑Winters (sazonalidade trimestral) com otimização',
                'tipo_modelo' => 'holt_winters',
                'mae' => $details['mae'],
                'mape' => $details['mape'],
                'rmse' => $details['rmse'],
                'confidence80' => $details['confidence80'],
                'confidence95' => $details['confidence95'],
                'componentes' => $details['components'],   // array com level, trend, seasonal
                'params' => $details['params'] ?? null
            ];
        } elseif ($model['type'] === 'holt') {
            $previsao = [
                'labels' => array_merge($labels, ['Prev 1', 'Prev 2', 'Prev 3']),
                'historico' => array_merge($valores, array_fill(0, 3, null)),
                'previsao' => array_merge(array_fill(0, $n, null), $model['forecast']),
                'modelo' => 'Suavização Exponencial de Holt (nível + tendência)',
                'tipo_modelo' => 'holt',
                'mae' => null,
                'mape' => null,
                'rmse' => null,
                'confidence80' => [],
                'confidence95' => [],
                'componentes' => null
            ];
        } else {
            $previsao = [
                'labels' => array_merge($labels, ['Prev 1', 'Prev 2', 'Prev 3']),
                'historico' => array_merge($valores, array_fill(0, 3, null)),
                'previsao' => array_merge(array_fill(0, $n, null), $model['forecast']),
                'modelo' => 'Média Móvel Ponderada (dados limitados)',
                'tipo_modelo' => 'media_movel',
                'mae' => null,
                'mape' => null,
                'rmse' => null,
                'confidence80' => [],
                'confidence95' => [],
                'componentes' => null
            ];
        }
    }

    $previsao_proximo = 0;
    if (!empty($previsao['previsao'])) {
        $last_key = array_key_last($previsao['previsao']);
        $previsao_proximo = $previsao['previsao'][$last_key] ?? 0;
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
        $ranking[] = ['item' => $item, 'incidencia' => $incidencia, 'ocorrencias' => $ocorrenciasPorItem[$item]];
    }
    usort($ranking, fn($a, $b) => $b['incidencia'] <=> $a['incidencia']);
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
                if (isset($ins[$campo]) && strtolower(trim($ins[$campo])) === 'nao')
                    $problemasSala++;
            }
        }
        $salasTaxa[$sala] = ($totalCamposSala > 0) ? round(($problemasSala / $totalCamposSala) * 100, 1) : 0;
    }

    $resposta = [
        'total_inspecoes' => $totalInspecoes,
        'taxa_media_problemas' => $taxaMedia,
        'previsao_proximo' => $previsao_proximo,
        'evolucao' => $evolucao,
        'previsao' => $previsao,
        'ranking' => $ranking,
        'salas' => ['labels' => array_keys($salasTaxa), 'valores' => array_values($salasTaxa)]
    ];

    echo json_encode($resposta);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Exceção', 'mensagem' => $e->getMessage()]);
}