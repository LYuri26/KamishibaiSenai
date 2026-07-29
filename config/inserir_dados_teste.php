<?php
// =====================================================
// INSERIR DADOS COMPLETOS 2022-2026 (KAMISHIBAI)
// =====================================================

require_once __DIR__ . '/database.php';

// Configurando limites razoáveis de tempo e memória
set_time_limit(300);
ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');

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
    $pdo->exec("TRUNCATE TABLE `102d`");
    $pdo->exec("TRUNCATE TABLE `101d`"); // Limpar a nova tabela 101d

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Tabelas limpas com sucesso (usuarios, responsaveis, relatorios e salas).<br>";
} catch (PDOException $e) {
    echo "Erro ao limpar tabelas: " . $e->getMessage() . "<br>";
    exit;
}

// =====================================================
// GERAR USUÁRIOS E RESPONSÁVEIS
// =====================================================

$usuariosSistema = [
    // Lideres (Coordenadores/Supervisores)
    ['Lenon', 'Yuri', 'lider'],
    ['José', 'Ferreira', 'lider'],
    ['Patrícia', 'Mendes', 'lider'],
    ['Gisele', 'Nunes', 'lider'],       // Líder responsável pelo Lab 102D
    ['Alexandre', 'Barbosa', 'lider'],   // Novo líder responsável pela Microdestilaria 101D

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

// Atribuir responsáveis aos respectivos ambientes
$stmtResponsavel = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW())");
$stmtResponsavel->execute([$lideres[0], '104a']);
$stmtResponsavel->execute([$lideres[1], '103d']);
$stmtResponsavel->execute([$lideres[2], '102c']);
$stmtResponsavel->execute([$lideres[3], '102d']);
$stmtResponsavel->execute([$lideres[4], '101d']); // Alexandre responsável pela Oficina 101D

echo "Usuários e Responsáveis vinculados com sucesso.<br>";

// =====================================================
// DEFINIÇÃO DAS SALAS E SEUS RESPECTIVOS CAMPOS 
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
    ],
    '102d' => [
        'porta_janelas_ok',
        'ar_condicionado_ok',
        'bancadas_limpas',
        'tomadas_fios_ok',
        'microscopios_b1_quantidade',
        'microscopios_b1_integros',
        'estufa_incubadora_b2_ok',
        'blocos_digestores_b2_ok',
        'balancas_analiticas_b3_ok',
        'centrifugas_extracao_b3_ok',
        'destilador_b4_ok',
        'cabine_seguranca_csb_ok',
        'microscopio_camera_desktop_ok',
        'rotaevaporador_gerber_vortex_ok',
        'estufas_forno_mufla_ok',
        'refrigerador_microondas_ok',
        'armario1_medidores_agua_ok',
        'armario2_3_phgametros_banhos_ok',
        'armario5_6_aquecimento_agitacao_ok',
        'armario7_medidores_campo_ok',
        'epis_seguranca_ok',
        'descarte_residuos_ok'
    ],
    '101d' => [
        'porta_janelas_ok',
        'ar_condicionado_ok',
        'bancadas_limpas',
        'tomadas_fios_ok',
        'microscopios_b1_quantidade',
        'microscopios_b1_integros',
        'estufa_incubadora_b2_ok',
        'blocos_digestores_b2_ok',
        'balancas_analiticas_b3_ok',
        'centrifugas_extracao_b3_ok',
        'destilador_b4_ok',
        'cabine_seguranca_csb_ok',
        'microscopio_camera_desktop_ok',
        'rotaevaporador_gerber_vortex_ok',
        'estufas_forno_mufla_ok',
        'refrigerador_microondas_ok',
        'armario1_medidores_agua_ok',
        'armario2_3_phgametros_banhos_ok',
        'armario5_6_aquecimento_agitacao_ok',
        'armario7_medidores_campo_ok',
        'epis_seguranca_ok',
        'descarte_residuos_ok'
    ]
];

// Horários pedagógicos do SENAI
$horariosPorPeriodo = [
    'manha' => ['inicio' => '07:30:00', 'fim' => '11:30:00'],
    'tarde' => ['inicio' => '13:30:00', 'fim' => '17:30:00'],
    'noite' => ['inicio' => '19:00:00', 'fim' => '22:30:00']
];

// Função que modela sazonalidade e tendências para alimentar previsões estatísticas
function getProbabilidadeProblema($ano, $mes)
{
    // Sazonalidade: Elevação de problemas em épocas quentes ou finais de semestre (Dezembro e Junho)
    $sazonal = 0.20 * (1 + cos(2 * M_PI * ($mes - 6) / 12));

    // Tendência: Lenta diminuição de ocorrências ao longo dos anos devido a ações preventivas da coordenação
    $tendencia = -0.015 * ($ano - 2022);

    $base = 0.15 + $tendencia + $sazonal;
    return min(0.60, max(0.04, $base));
}

$anoAtual = (int) date('Y');
$mesAtual = (int) date('m');
$totalInsercoes = 0;

echo "Gerando dados históricos estruturados de 2022 até $anoAtual-$mesAtual...<br>";

// =====================================================
// INÍCIO DA TRANSAÇÃO PARA OTIMIZAÇÃO DE PERFORMANCE
// =====================================================
try {
    $pdo->beginTransaction();

    for ($ano = 2022; $ano <= $anoAtual; $ano++) {
        $mesFim = ($ano == $anoAtual) ? $mesAtual : 12;

        for ($mes = 1; $mes <= $mesFim; $mes++) {
            $prob = getProbabilidadeProblema($ano, $mes);
            $mesStr = sprintf('%02d', $mes);

            // Coleta datas representativas para evitar inflar desnecessariamente o banco
            $datasBase = [
                date('Y-m-d', strtotime("first friday of $ano-$mesStr")),
                date('Y-m-d', strtotime("third wednesday of $ano-$mesStr"))
            ];

            foreach ($datasBase as $dataBase) {
                // Se a conversão falhar ou retornar fora do intervalo, ignora
                if (empty($dataBase) || strpos($dataBase, "$ano-$mesStr") === false) {
                    continue;
                }

                $isSexta = (date('N', strtotime($dataBase)) == 5);

                foreach (['104a', '103d', '102c', '102d', '101d'] as $sala) {
                    $campos = $camposPorSala[$sala];

                    foreach (['manha', 'tarde', 'noite'] as $periodo) {
                        foreach (['inicio', 'fim'] as $momento) {
                            $hora = $horariosPorPeriodo[$periodo][$momento];
                            $dataStr = "$dataBase $hora";

                            $valores = [];
                            $problemas = [];

                            // Definição das conformidades
                            foreach ($campos as $campo) {
                                $var = mt_rand(-12, 12) / 100;
                                $probCampo = max(0.01, min(0.99, $prob + $var));

                                // 'sim' = Sem problemas, 'nao' = Não conformidade
                                $resultado = (mt_rand(1, 100) <= $probCampo * 100) ? 'nao' : 'sim';
                                $valores[$campo] = $resultado;

                                if ($resultado === 'nao') {
                                    $problemas[] = $campo;
                                }
                            }

                            // Texto descritivo das observações
                            $observacao = (count($problemas) > 0)
                                ? "Não conformidades apontadas: " . implode(', ', $problemas)
                                : "Inspeção realizada com sucesso. Nada a declarar.";

                            // Verificação estruturada de Sexta-feira
                            $verificacaoSexta = null;
                            if ($isSexta && $momento === 'fim') {
                                $verificacaoSexta = json_encode([
                                    'limpeza_pesada_realizada' => (mt_rand(1, 10) <= 9) ? 'sim' : 'nao',
                                    'equipamentos_desligados_fds' => (mt_rand(1, 10) <= 8) ? 'sim' : 'nao'
                                ]);
                            }

                            // Montagem dinâmica da instrução preparada
                            $camposList = implode(', ', array_keys($valores));
                            $placeholders = implode(', ', array_fill(0, count($valores), '?'));
                            $sql = "INSERT INTO `$sala` (nome, data, momento, observacoes, verificacao_sexta, $camposList) 
                                    VALUES (?, ?, ?, ?, ?, $placeholders)";

                            $stmt = $pdo->prepare($sql);
                            $nomeInstrutor = $instrutores[array_rand($instrutores)];

                            $params = array_merge(
                                [$nomeInstrutor, $dataStr, $momento, $observacao, $verificacaoSexta],
                                array_values($valores)
                            );

                            $stmt->execute($params);
                            $inspecao_id = $pdo->lastInsertId();

                            // Registro complementar na central de relatórios
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

    // Comita tudo em lote
    $pdo->commit();
    echo "<hr><strong>Processamento concluído com sucesso!</strong> Foram inseridos $totalInsercoes registros no banco de dados.";

} catch (Exception $e) {
    // Desfaz alterações em caso de falha crítica
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<hr><strong>Falha crítica ao preencher lote:</strong> " . $e->getMessage();
}
?>