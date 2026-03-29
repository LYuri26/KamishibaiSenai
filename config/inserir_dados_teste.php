<?php
// =====================================================
// INSERIR DADOS COMPLETOS 2022-2026
// =====================================================

require_once __DIR__ . '/database.php';

// Garante que a tabela relatorios existe (se não existir, cria)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `relatorios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `inspecao_id` INT NOT NULL,
        `sala` VARCHAR(50) NOT NULL,
        `data` DATE NOT NULL,
        `periodo` ENUM('manha','tarde','noite') NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `data_geracao` DATETIME NOT NULL,
        `imagens` TEXT NULL,
        UNIQUE KEY `unique_inspecao` (`inspecao_id`, `sala`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Tabela relatorios verificada/criada.<br>";
} catch (PDOException $e) {
    echo "Erro ao criar tabela relatorios: " . $e->getMessage() . "<br>";
}

// Limpa todas as tabelas de dados (mantém apenas a estrutura)
$tabelas = ['104a', '103d', '102c', 'relatorios'];
foreach ($tabelas as $tabela) {
    try {
        $pdo->exec("TRUNCATE TABLE `$tabela`");
        echo "Tabela $tabela limpa.<br>";
    } catch (PDOException $e) {
        echo "Erro ao truncar $tabela: " . $e->getMessage() . "<br>";
    }
}

// Lista de instrutores fictícios
$instrutores = [
    'Carlos Silva',
    'Mariana Souza',
    'João Pereira',
    'Ana Lima',
    'Roberto Alves',
    'Fernanda Costa',
    'Lucas Mendes',
    'Juliana Rocha',
    'Paulo Henrique',
    'Cristina Oliveira',
    'Ricardo Santos',
    'Patrícia Gomes'
];

// Campos por sala (idênticos ao schema fornecido)
$camposPorSala = [
    '104a' => [
        'carteiras_organizadas',
        'carteiras_quantidade',
        'carteiras_danificadas',
        'tv_presente',
        'tv_integra',
        'tv_hdmi',
        'tv_cabos_organizados',
        'tv_conectada',
        'tv_cabos_ok',
        'ar_presentes',
        'ar_controle',
        'ar_danos',
        'quadro_limpo',
        'quadro_danos',
        'quadro_fixo',
        'porta_funciona',
        'janelas_intactas',
        'janelas_vidros',
        'tomadas_intactas',
        'tomadas_fios',
        'tomadas_adaptadores',
        'mesa_firme',
        'mesa_gavetas',
        'cadeira_integra'
    ],
    '103d' => [
        'computadores_ligam',
        'mouses_funcionam',
        'teclados_funcionam',
        'monitores_funcionam',
        'gabinetes_estado',
        'cadeiras_baias',
        'ar_condicionado_funciona',
        'quadro_limpo',
        'mesa_instrutor',
        'cadeira_instrutor',
        'portao_funciona',
        'janelas_intactas',
        'tomadas_intactas',
        'fios_expostos'
    ],
    '102c' => [
        'portao_funciona',
        'instrutor_epi',
        'box1_epi_completo',
        'box1_ferramentas_ok',
        'box1_organizacao',
        'box2_epi_completo',
        'box2_ferramentas_ok',
        'box2_organizacao',
        'box3_epi_completo',
        'box3_ferramentas_ok',
        'box3_organizacao',
        'box4_epi_completo',
        'box4_ferramentas_ok',
        'box4_organizacao',
        'box5_epi_completo',
        'box5_ferramentas_ok',
        'box5_organizacao',
        'box6_epi_completo',
        'box6_ferramentas_ok',
        'box7_epi_completo',
        'box7_ferramentas_ok',
        'box7_organizacao',
        'box8_epi_completo',
        'box8_ferramentas_ok',
        'box8_organizacao',
        'box9_epi_completo',
        'box9_ferramentas_ok',
        'box9_organizacao',
        'box10_epi_completo',
        'box10_ferramentas_ok',
        'box10_organizacao',
        'area_limpa',
        'area_organizacao',
        'equipamentos_local',
        'macarico_ok',
        'estufa_ok'
    ]
];

// Função para calcular a probabilidade de um campo ser 'nao'
function getProbabilidadeProblema($ano, $mes)
{
    // Sazonalidade: pico no verão (dezembro/janeiro) e vale no inverno (junho/julho)
    // Pico em janeiro (mês 1)
    $sazonal = 0.25 * (1 + cos(2 * M_PI * ($mes - 1) / 12));
    // Tendência: aumento gradual dos problemas ao longo dos anos
    $tendencia = 0.02 * ($ano - 2022);
    // Base
    $base = 0.12 + $tendencia + $sazonal;
    return min(0.75, max(0.05, $base));
}

// Determinar o último mês a ser inserido (atual)
$anoAtual = (int) date('Y');
$mesAtual = (int) date('m');
$ultimoAno = $anoAtual;
$ultimoMes = $mesAtual;

echo "Gerando dados de 2022-01 até $ultimoAno-$ultimoMes<br><br>";

$totalInsercoes = 0;

// Loop sobre os anos e meses
for ($ano = 2022; $ano <= $ultimoAno; $ano++) {
    $mesInicio = ($ano == 2022) ? 1 : 1;
    $mesFim = ($ano == $ultimoAno) ? $ultimoMes : 12;

    for ($mes = $mesInicio; $mes <= $mesFim; $mes++) {
        $prob = getProbabilidadeProblema($ano, $mes);
        echo "Processando $ano-$mes | Taxa base: " . round($prob * 100, 1) . "%<br>";

        // Para cada sala
        foreach (['104a', '103d', '102c'] as $sala) {
            $campos = $camposPorSala[$sala];

            // Para cada momento (início e fim)
            foreach (['inicio' => '08:00:00', 'fim' => '16:00:00'] as $momento => $hora) {
                // Escolhe um dia representativo (10 para início, 20 para fim)
                $dia = ($momento == 'inicio') ? 10 : 20;
                $dataStr = sprintf('%04d-%02d-%02d %s', $ano, $mes, $dia, $hora);
                $dataObj = new DateTime($dataStr);

                // Gerar valores aleatórios para cada campo
                $valores = [];
                foreach ($campos as $campo) {
                    // Pequena variação aleatória na probabilidade para cada campo
                    $var = mt_rand(-10, 10) / 100;
                    $probCampo = max(0.02, min(0.98, $prob + $var));
                    $valores[$campo] = (mt_rand(1, 100) <= $probCampo * 100) ? 'nao' : 'sim';
                }

                // Inserir na tabela da sala
                $camposList = implode(', ', array_keys($valores));
                $placeholders = implode(', ', array_fill(0, count($valores), '?'));
                $sql = "INSERT INTO `$sala` (nome, data, momento, observacoes, $camposList) 
                        VALUES (?, ?, ?, ?, $placeholders)";
                $stmt = $pdo->prepare($sql);

                $nomeInstrutor = $instrutores[array_rand($instrutores)];
                $observacao = "Inspeção de $momento - $sala - $ano-$mes";

                $params = array_merge([$nomeInstrutor, $dataStr, $momento, $observacao], array_values($valores));
                $stmt->execute($params);
                $inspecao_id = $pdo->lastInsertId();

                // Determinar período (manha/tarde)
                $periodo = ($momento == 'inicio') ? 'manha' : 'tarde';

                // Inserir relatório
                $sqlRel = "INSERT INTO relatorios (inspecao_id, sala, data, periodo, momento, observacoes, data_geracao) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmtRel = $pdo->prepare($sqlRel);
                $stmtRel->execute([$inspecao_id, $sala, $dataObj->format('Y-m-d'), $periodo, $momento, $observacao]);

                $totalInsercoes++;
            }
        }
    }
}

echo "<hr>Inserção concluída! Total de registros: $totalInsercoes inspeções.";