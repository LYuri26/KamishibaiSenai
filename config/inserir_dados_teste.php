<?php
// =====================================================
// INSERIR DADOS COMPLETOS 2022-2026 (KAMISHIBAI)
// =====================================================

require_once __DIR__ . '/database.php';

// Aumentando o limite de tempo e memória caso gere muitos dados
set_time_limit(300);
ini_set('memory_limit', '256M');

// =====================================================
// LIMPAR TABELAS (RESET COMPLETO DOS DADOS DE TESTE)
// =====================================================

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Limpar usuários e responsáveis
    $pdo->exec("TRUNCATE TABLE responsaveis");
    $pdo->exec("TRUNCATE TABLE usuarios");

    // Limpar relatórios e tabelas de salas
    $pdo->exec("TRUNCATE TABLE relatorios");
    $pdo->exec("TRUNCATE TABLE `104a`");
    $pdo->exec("TRUNCATE TABLE `103d`");
    $pdo->exec("TRUNCATE TABLE `102c`");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Tabelas limpas com sucesso (usuarios, responsaveis, relatorios e salas).<br>";
} catch (PDOException $e) {
    echo "Erro ao limpar tabelas: " . $e->getMessage() . "<br>";
    exit;
}

// =====================================================
// GERAR USUÁRIOS E RESPONSÁVEIS
// =====================================================

// Lista de usuários fictícios (3 líderes para 3 salas + vários instrutores)
$usuariosSistema = [
    // Lideres (Coordenadores/Supervisores)
    ['Lenon', 'Yuri', 'lider'],
    ['José', 'Ferreira', 'lider'],
    ['Patrícia', 'Mendes', 'lider'],

    // Instrutores
    ['Carlos', 'Silva', 'instrutor'],
    ['Mariana', 'Souza', 'instrutor'],
    ['João', 'Pereira', 'instrutor'],
    ['Ana', 'Lima', 'instrutor'],
    ['Roberto', 'Alves', 'instrutor'],
    ['Fernanda', 'Costa', 'instrutor'],
    ['Lucas', 'Mendes', 'instrutor'],
    ['Juliana', 'Rocha', 'instrutor'],
    ['Paulo', 'Henrique', 'instrutor'],
    ['Cristina', 'Oliveira', 'instrutor'],
    ['Ricardo', 'Santos', 'instrutor'],
    ['Bruno', 'Carvalho', 'instrutor'],
    ['Tatiane', 'Martins', 'instrutor'],
    ['Gustavo', 'Almeida', 'instrutor'],
    ['Camila', 'Ferreira', 'instrutor']
];

$instrutores = [];
$lideres = [];
$senhaPadrao = password_hash('123456', PASSWORD_DEFAULT);

$stmtUsuario = $pdo->prepare("
    INSERT INTO usuarios (nome, sobrenome, email_hash, email_encrypted, cargo, senha, data_criacao)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

foreach ($usuariosSistema as $usuario) {
    [$nome, $sobrenome, $cargo] = $usuario;
    $email = strtolower($nome . "." . $sobrenome) . "@senai.local";
    $emailHash = hash('sha256', $email);

    $stmtUsuario->execute([
        $nome,
        $sobrenome,
        $emailHash,
        $email,
        $cargo,
        $senhaPadrao,
        date('Y-m-d H:i:s')
    ]);

    $usuarioId = $pdo->lastInsertId();

    if ($cargo === 'lider') {
        $lideres[] = $usuarioId;
    } else {
        $instrutores[] = "$nome $sobrenome";
    }
}

// Atribuir responsáveis aos ambientes
$stmtResponsavel = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW())");
$stmtResponsavel->execute([$lideres[0], '104a']);
$stmtResponsavel->execute([$lideres[1], '103d']);
$stmtResponsavel->execute([$lideres[2], '102c']);

echo "Usuários e Responsáveis criados.<br>";

// =====================================================
// DEFINIÇÃO DAS SALAS E SEUS CAMPOS 
// =====================================================

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

// Horários realistas de SENAI/Escola
$horariosPorPeriodo = [
    'manha' => ['inicio' => '07:30:00', 'fim' => '11:30:00'],
    'tarde' => ['inicio' => '13:30:00', 'fim' => '17:30:00'],
    'noite' => ['inicio' => '19:00:00', 'fim' => '22:30:00']
];

function getProbabilidadeProblema($ano, $mes)
{
    $sazonal = 0.25 * (1 + cos(2 * M_PI * ($mes - 1) / 12));
    $tendencia = 0.02 * ($ano - 2022);
    $base = 0.12 + $tendencia + $sazonal;
    return min(0.75, max(0.05, $base));
}

$anoAtual = (int) date('Y');
$mesAtual = (int) date('m');
$totalInsercoes = 0;

echo "Gerando dados de histórico (2022 até $anoAtual-$mesAtual)... Isso pode levar alguns segundos.<br><br>";

// =====================================================
// LOOP DE GERAÇÃO DE DADOS
// =====================================================

for ($ano = 2022; $ano <= $anoAtual; $ano++) {
    $mesInicio = ($ano == 2022) ? 1 : 1;
    $mesFim = ($ano == $anoAtual) ? $mesAtual : 12;

    for ($mes = $mesInicio; $mes <= $mesFim; $mes++) {
        $prob = getProbabilidadeProblema($ano, $mes);
        $mesStr = sprintf('%02d', $mes);

        // Gera duas datas representativas no mês para evitar sobrecarga excessiva de DB
        // Usamos strtotime para pegar dias reais de semana, forçando pelo menos uma sexta-feira no mês
        $datasBase = [
            date('Y-m-d', strtotime("first friday of $ano-$mesStr")),
            date('Y-m-d', strtotime("third wednesday of $ano-$mesStr"))
        ];

        foreach ($datasBase as $dataBase) {
            $isSexta = (date('N', strtotime($dataBase)) == 5); // 5 = Sexta-feira

            foreach (['104a', '103d', '102c'] as $sala) {
                $campos = $camposPorSala[$sala];

                foreach (['manha', 'tarde', 'noite'] as $periodo) {

                    foreach (['inicio', 'fim'] as $momento) {
                        $hora = $horariosPorPeriodo[$periodo][$momento];
                        $dataStr = "$dataBase $hora";

                        $valores = [];
                        $problemas = [];

                        // Preencher "sim" ou "nao"
                        foreach ($campos as $campo) {
                            $var = mt_rand(-10, 10) / 100;
                            $probCampo = max(0.02, min(0.98, $prob + $var));
                            $resultado = (mt_rand(1, 100) <= $probCampo * 100) ? 'nao' : 'sim';
                            $valores[$campo] = $resultado;

                            if ($resultado === 'nao') {
                                $problemas[] = $campo;
                            }
                        }

                        // Observações
                        $observacao = (count($problemas) > 0)
                            ? "Não conformidades encontradas: " . implode(', ', $problemas)
                            : "Ambiente inspecionado sem não conformidades.";

                        // Verificação de Sexta (JSON)
                        $verificacaoSexta = null;
                        if ($isSexta && $momento === 'fim') {
                            $verificacaoSexta = json_encode([
                                'limpeza_pesada_realizada' => (mt_rand(1, 10) <= 9) ? 'sim' : 'nao',
                                'equipamentos_desligados_fds' => (mt_rand(1, 10) <= 8) ? 'sim' : 'nao'
                            ]);
                        }

                        // SQL Dinâmico para inserção
                        $camposList = implode(', ', array_keys($valores));
                        $placeholders = implode(', ', array_fill(0, count($valores), '?'));
                        $sql = "INSERT INTO `$sala` (nome, data, momento, observacoes, verificacao_sexta, $camposList) 
                                VALUES (?, ?, ?, ?, ?, $placeholders)";

                        $stmt = $pdo->prepare($sql);
                        $nomeInstrutor = $instrutores[array_rand($instrutores)];

                        $params = array_merge([$nomeInstrutor, $dataStr, $momento, $observacao, $verificacaoSexta], array_values($valores));
                        $stmt->execute($params);
                        $inspecao_id = $pdo->lastInsertId();

                        // Inserir Relatório
                        $sqlRel = "INSERT INTO relatorios (inspecao_id, sala, data, periodo, momento, observacoes, data_geracao) 
                                   VALUES (?, ?, ?, ?, ?, ?, NOW())";
                        $stmtRel = $pdo->prepare($sqlRel);
                        $stmtRel->execute([$inspecao_id, $sala, $dataBase, $periodo, $momento, $observacao]);

                        $totalInsercoes++;
                    }
                }
            }
        }
    }
}

echo "<hr><strong>Inserção concluída com sucesso!</strong> Total de registros: $totalInsercoes inspeções.";
?>