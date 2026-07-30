<?php
// =====================================================
// INSERIR DADOS COMPLETOS 2022-2026 (KAMISHIBAI)
// DELETA E RECRIA TODAS AS TABELAS DO BANCO DE DADOS
// COM USUÁRIOS ESPECÍFICOS SOLICITADOS
// =====================================================

date_default_timezone_set('America/Sao_Paulo');

// Tenta carregar o banco e a criptografia do sistema
if (file_exists(__DIR__ . '/../../config/database.php')) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/encryption.php';
} elseif (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/encryption.php';
} else {
    require_once __DIR__ . '/database.php';
    if (file_exists(__DIR__ . '/encryption.php')) {
        require_once __DIR__ . '/encryption.php';
    }
}

// Configurando limites de tempo e memória
set_time_limit(300);
ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');

// =====================================================
// 1. DELETAR E RECRIAR TABELAS (RESET COMPLETO DE ESTRUTURA E DADOS)
// =====================================================

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Deleta tabelas existentes
    $pdo->exec("DROP TABLE IF EXISTS responsaveis");
    $pdo->exec("DROP TABLE IF EXISTS usuarios");
    $pdo->exec("DROP TABLE IF EXISTS relatorios");
    $pdo->exec("DROP TABLE IF EXISTS `104a`");
    $pdo->exec("DROP TABLE IF EXISTS `103d`");
    $pdo->exec("DROP TABLE IF EXISTS `102c`");
    $pdo->exec("DROP TABLE IF EXISTS `102d`");
    $pdo->exec("DROP TABLE IF EXISTS `101d`");

    // Recria a Tabela 104a
    $pdo->exec("CREATE TABLE `104a` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `carteiras_organizadas` ENUM('sim','nao') NOT NULL,
        `carteiras_quantidade` ENUM('sim','nao') NOT NULL,
        `carteiras_danificadas` ENUM('sim','nao') NOT NULL,
        `tv_presente` ENUM('sim','nao') NOT NULL,
        `tv_integra` ENUM('sim','nao') NOT NULL,
        `tv_hdmi` ENUM('sim','nao') NOT NULL,
        `tv_cabos_organizados` ENUM('sim','nao') NOT NULL,
        `tv_conectada` ENUM('sim','nao') NOT NULL,
        `tv_cabos_ok` ENUM('sim','nao') NOT NULL,
        `ar_presentes` ENUM('sim','nao') NOT NULL,
        `ar_controle` ENUM('sim','nao') NOT NULL,
        `ar_danos` ENUM('sim','nao') NOT NULL,
        `quadro_limpo` ENUM('sim','nao') NOT NULL,
        `quadro_danos` ENUM('sim','nao') NOT NULL,
        `quadro_fixo` ENUM('sim','nao') NOT NULL,
        `porta_funciona` ENUM('sim','nao') NOT NULL,
        `janelas_intactas` ENUM('sim','nao') NOT NULL,
        `janelas_vidros` ENUM('sim','nao') NOT NULL,
        `tomadas_intactas` ENUM('sim','nao') NOT NULL,
        `tomadas_fios` ENUM('sim','nao') NOT NULL,
        `tomadas_adaptadores` ENUM('sim','nao') NOT NULL,
        `mesa_firme` ENUM('sim','nao') NOT NULL,
        `mesa_gavetas` ENUM('sim','nao') NOT NULL,
        `cadeira_integra` ENUM('sim','nao') NOT NULL,
        `verificacao_sexta` JSON NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Recria a Tabela 103d
    $pdo->exec("CREATE TABLE `103d` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `computadores_ligam` ENUM('sim','nao') NOT NULL,
        `mouses_funcionam` ENUM('sim','nao') NOT NULL,
        `teclados_funcionam` ENUM('sim','nao') NOT NULL,
        `monitores_funcionam` ENUM('sim','nao') NOT NULL,
        `gabinetes_estado` ENUM('sim','nao') NOT NULL,
        `cadeiras_baias` ENUM('sim','nao') NOT NULL,
        `ar_condicionado_funciona` ENUM('sim','nao') NOT NULL,
        `quadro_limpo` ENUM('sim','nao') NOT NULL,
        `mesa_instrutor` ENUM('sim','nao') NOT NULL,
        `cadeira_instrutor` ENUM('sim','nao') NOT NULL,
        `portao_funciona` ENUM('sim','nao') NOT NULL,
        `janelas_intactas` ENUM('sim','nao') NOT NULL,
        `tomadas_intactas` ENUM('sim','nao') NOT NULL,
        `fios_expostos` ENUM('sim','nao') NOT NULL,
        `verificacao_sexta` JSON NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Recria a Tabela 102c
    $pdo->exec("CREATE TABLE `102c` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `portao_funciona` ENUM('sim','nao') NOT NULL,
        `instrutor_epi` ENUM('sim','nao') NOT NULL,
        `box1_epi_completo` ENUM('sim','nao') NOT NULL,
        `box1_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box1_organizacao` ENUM('sim','nao') NOT NULL,
        `box2_epi_completo` ENUM('sim','nao') NOT NULL,
        `box2_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box2_organizacao` ENUM('sim','nao') NOT NULL,
        `box3_epi_completo` ENUM('sim','nao') NOT NULL,
        `box3_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box3_organizacao` ENUM('sim','nao') NOT NULL,
        `box4_epi_completo` ENUM('sim','nao') NOT NULL,
        `box4_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box4_organizacao` ENUM('sim','nao') NOT NULL,
        `box5_epi_completo` ENUM('sim','nao') NOT NULL,
        `box5_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box5_organizacao` ENUM('sim','nao') NOT NULL,
        `box6_epi_completo` ENUM('sim','nao') NOT NULL,
        `box6_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box7_epi_completo` ENUM('sim','nao') NOT NULL,
        `box7_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box7_organizacao` ENUM('sim','nao') NOT NULL,
        `box8_epi_completo` ENUM('sim','nao') NOT NULL,
        `box8_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box8_organizacao` ENUM('sim','nao') NOT NULL,
        `box9_epi_completo` ENUM('sim','nao') NOT NULL,
        `box9_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box9_organizacao` ENUM('sim','nao') NOT NULL,
        `box10_epi_completo` ENUM('sim','nao') NOT NULL,
        `box10_ferramentas_ok` ENUM('sim','nao') NOT NULL,
        `box10_organizacao` ENUM('sim','nao') NOT NULL,
        `area_limpa` ENUM('sim','nao') NOT NULL,
        `area_organizacao` ENUM('sim','nao') NOT NULL,
        `equipamentos_local` ENUM('sim','nao') NOT NULL,
        `macarico_ok` ENUM('sim','nao') NOT NULL,
        `estufa_ok` ENUM('sim','nao') NOT NULL,
        `verificacao_sexta` JSON NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Recria a Tabela 102d (Checklist Química - 7 Seções)
    $pdo->exec("CREATE TABLE `102d` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `org_bancadas_limpas` ENUM('sim','nao') NOT NULL,
        `org_bancadas_organizadas` ENUM('sim','nao') NOT NULL,
        `org_cadeiras_organizadas` ENUM('sim','nao') NOT NULL,
        `org_materiais_guardados` ENUM('sim','nao') NOT NULL,
        `org_armarios_fechados` ENUM('sim','nao') NOT NULL,
        `org_quadro_limpo` ENUM('sim','nao') NOT NULL,
        `org_piso_limpo` ENUM('sim','nao') NOT NULL,
        `org_corredores_desobstruidos` ENUM('sim','nao') NOT NULL,
        `seg_extintor_acessivel` ENUM('sim','nao') NOT NULL,
        `seg_chuveiro_emergencia_ok` ENUM('sim','nao') NOT NULL,
        `seg_lava_olhos_ok` ENUM('sim','nao') NOT NULL,
        `seg_kit_primeiros_socorros` ENUM('sim','nao') NOT NULL,
        `seg_saidas_emergencia_livres` ENUM('sim','nao') NOT NULL,
        `seg_sinalizacao_visivel` ENUM('sim','nao') NOT NULL,
        `seg_produtos_quimicos_identificados` ENUM('sim','nao') NOT NULL,
        `seg_fispqs_disponiveis` ENUM('sim','nao') NOT NULL,
        `eq_balanca_limpa` ENUM('sim','nao') NOT NULL,
        `eq_balanca_desligada` ENUM('sim','nao') NOT NULL,
        `eq_phmetro_limpo` ENUM('sim','nao') NOT NULL,
        `eq_condutivimetro_limpo` ENUM('sim','nao') NOT NULL,
        `eq_espectrofotometro_limpo` ENUM('sim','nao') NOT NULL,
        `eq_estufa_desligada` ENUM('sim','nao') NOT NULL,
        `eq_autoclave_desligada` ENUM('sim','nao') NOT NULL,
        `eq_equipamentos_desligados` ENUM('sim','nao') NOT NULL,
        `eq_equipamentos_sem_avarias` ENUM('sim','nao') NOT NULL,
        `vid_vidrarias_limpas` ENUM('sim','nao') NOT NULL,
        `vid_vidrarias_secas` ENUM('sim','nao') NOT NULL,
        `vid_vidrarias_guardadas` ENUM('sim','nao') NOT NULL,
        `vid_existe_vidraria_quebrada` ENUM('sim','nao') NOT NULL,
        `pq_frascos_identificados` ENUM('sim','nao') NOT NULL,
        `pq_frascos_fechados` ENUM('sim','nao') NOT NULL,
        `pq_produtos_armazenados` ENUM('sim','nao') NOT NULL,
        `pq_residuos_descartados` ENUM('sim','nao') NOT NULL,
        `enc_pia_limpa` ENUM('sim','nao') NOT NULL,
        `enc_torneiras_fechadas` ENUM('sim','nao') NOT NULL,
        `enc_gas_fechado` ENUM('sim','nao') NOT NULL,
        `enc_agua_fechada` ENUM('sim','nao') NOT NULL,
        `enc_equipamentos_desligados` ENUM('sim','nao') NOT NULL,
        `enc_ar_condicionado_desligado` ENUM('sim','nao') NOT NULL,
        `enc_luzes_apagadas` ENUM('sim','nao') NOT NULL,
        `enc_porta_trancada` ENUM('sim','nao') NOT NULL,
        `enc_lixeiras_esvaziadas` ENUM('sim','nao') NOT NULL,
        `nc_encontrada` ENUM('sim','nao') NOT NULL,
        `verificacao_sexta` JSON NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Recria a Tabela 101d
    $pdo->exec("CREATE TABLE `101d` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `data` DATETIME NOT NULL,
        `momento` ENUM('inicio','fim') NOT NULL,
        `observacoes` TEXT,
        `porta_janelas_ok` ENUM('sim','nao') NOT NULL,
        `ar_condicionado_ok` ENUM('sim','nao') NOT NULL,
        `bancadas_limpas` ENUM('sim','nao') NOT NULL,
        `tomadas_fios_ok` ENUM('sim','nao') NOT NULL,
        `microscopios_b1_quantidade` ENUM('sim','nao') NOT NULL,
        `microscopios_b1_integros` ENUM('sim','nao') NOT NULL,
        `estufa_incubadora_b2_ok` ENUM('sim','nao') NOT NULL,
        `blocos_digestores_b2_ok` ENUM('sim','nao') NOT NULL,
        `balancas_analiticas_b3_ok` ENUM('sim','nao') NOT NULL,
        `centrifugas_extracao_b3_ok` ENUM('sim','nao') NOT NULL,
        `destilador_b4_ok` ENUM('sim','nao') NOT NULL,
        `cabine_seguranca_csb_ok` ENUM('sim','nao') NOT NULL,
        `microscopio_camera_desktop_ok` ENUM('sim','nao') NOT NULL,
        `rotaevaporador_gerber_vortex_ok` ENUM('sim','nao') NOT NULL,
        `estufas_forno_mufla_ok` ENUM('sim','nao') NOT NULL,
        `refrigerador_microondas_ok` ENUM('sim','nao') NOT NULL,
        `armario1_medidores_agua_ok` ENUM('sim','nao') NOT NULL,
        `armario2_3_phgametros_banhos_ok` ENUM('sim','nao') NOT NULL,
        `armario5_6_aquecimento_agitacao_ok` ENUM('sim','nao') NOT NULL,
        `armario7_medidores_campo_ok` ENUM('sim','nao') NOT NULL,
        `epis_seguranca_ok` ENUM('sim','nao') NOT NULL,
        `descarte_residuos_ok` ENUM('sim','nao') NOT NULL,
        `verificacao_sexta` JSON NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Recria a Tabela relatorios
    $pdo->exec("CREATE TABLE `relatorios` (
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

    // Recria a Tabela usuarios
    $pdo->exec("CREATE TABLE `usuarios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nome` VARCHAR(100) NOT NULL,
        `sobrenome` VARCHAR(100) NOT NULL,
        `email_hash` VARCHAR(64) UNIQUE NOT NULL,
        `email_encrypted` TEXT NOT NULL,
        `cargo` ENUM('instrutor','lider') NOT NULL,
        `senha` VARCHAR(255) NOT NULL,
        `data_criacao` DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Recria a Tabela responsaveis
    $pdo->exec("CREATE TABLE `responsaveis` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `usuario_id` INT NOT NULL,
        `ambiente` VARCHAR(50) NOT NULL,
        `data_atribuicao` DATETIME NOT NULL,
        UNIQUE KEY `unique_ambiente` (`ambiente`),
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "✅ Tabelas deletadas e recriadas com sucesso.<br>";
} catch (PDOException $e) {
    echo "❌ Erro ao recriar tabelas: " . $e->getMessage() . "<br>";
    exit;
}

// =====================================================
// 2. GERAR USUÁRIOS E RESPONSÁVEIS (PADRÃO FIEMG E CRIPTOGRAFADO)
// =====================================================

$usuariosSistema = [
    // Líderes
    ['Lenon', 'Yuri', 'lider', 'lenon.yuri@fiemg.com.br'],
    ['José', 'Ferreira', 'lider', 'jose.ferreira@fiemg.com.br'],
    ['Patrícia', 'Mendes', 'lider', 'patricia.mendes@fiemg.com.br'],
    ['Gisele', 'Nunes', 'lider', 'gisele.nunes@fiemg.com.br'],
    ['Alexandre', 'Barbosa', 'lider', 'alexandre.barbosa@fiemg.com.br'],

    // USUÁRIOS ESPECÍFICOS SOLICITADOS
    ['Bianca', 'Borges', 'instrutor', 'bianca.borges@fiemg.com.br'],
    ['Bruna', 'Fernanda', 'instrutor', 'bfernanda@fiemg.com.br'],
    ['Priscila', 'Vitorino', 'instrutor', 'priscila.vitorino@fiemg.com.br'],

    // Demais Instrutores do Sistema
    ['Carlos', 'Silva', 'instrutor', 'carlos.silva@fiemg.com.br'],
    ['Mariana', 'Souza', 'instrutor', 'mariana.souza@fiemg.com.br'],
    ['João', 'Pereira', 'instrutor', 'joao.pereira@fiemg.com.br'],
    ['Ana', 'Lima', 'instrutor', 'ana.lima@fiemg.com.br'],
    ['Roberto', 'Alves', 'instrutor', 'roberto.alves@fiemg.com.br'],
    ['Fernanda', 'Costa', 'instrutor', 'fernanda.costa@fiemg.com.br'],
    ['Lucas', 'Mendes', 'instrutor', 'lucas.mendes@fiemg.com.br'],
    ['Juliana', 'Rocha', 'instrutor', 'juliana.rocha@fiemg.com.br'],
    ['Paulo', 'Henrique', 'instrutor', 'paulo.henrique@fiemg.com.br'],
    ['Cristina', 'Oliveira', 'instrutor', 'cristina.oliveira@fiemg.com.br'],
    ['Ricardo', 'Santos', 'instrutor', 'ricardo.santos@fiemg.com.br'],
    ['Bruno', 'Carvalho', 'instrutor', 'bruno.carvalho@fiemg.com.br'],
    ['Tatiane', 'Martins', 'instrutor', 'tatiane.martins@fiemg.com.br'],
    ['Gustavo', 'Almeida', 'instrutor', 'gustavo.almeida@fiemg.com.br'],
    ['Camila', 'Ferreira', 'instrutor', 'camila.ferreira@fiemg.com.br']
];

$instrutores = [];
$lideres = [];

$senhaPadraoRaw = 'senai123';
$senhaPadraoHash = password_hash($senhaPadraoRaw, PASSWORD_DEFAULT);

$stmtUsuario = $pdo->prepare("
    INSERT INTO usuarios (nome, sobrenome, email_hash, email_encrypted, cargo, senha, data_criacao)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

foreach ($usuariosSistema as $usuario) {
    $nome = $usuario[0];
    $sobrenome = $usuario[1];
    $cargo = $usuario[2];
    $email = isset($usuario[3]) ? strtolower(trim($usuario[3])) : strtolower($nome . "." . $sobrenome) . "@fiemg.com.br";

    $emailHash = hash('sha256', $email);
    $emailEncrypted = function_exists('encryptEmail') ? encryptEmail($email) : $email;

    $stmtUsuario->execute([
        $nome,
        $sobrenome,
        $emailHash,
        $emailEncrypted,
        $cargo,
        $senhaPadraoHash,
        date('Y-m-d H:i:s')
    ]);

    $usuarioId = $pdo->lastInsertId();

    if ($cargo === 'lider') {
        $lideres[] = $usuarioId;
    } else {
        $instrutores[] = "$nome $sobrenome";
    }
}

// Vincula responsáveis aos ambientes
$stmtResponsavel = $pdo->prepare("INSERT INTO responsaveis (usuario_id, ambiente, data_atribuicao) VALUES (?, ?, NOW())");
$stmtResponsavel->execute([$lideres[0], '104a']);
$stmtResponsavel->execute([$lideres[1], '103d']);
$stmtResponsavel->execute([$lideres[2], '102c']);
$stmtResponsavel->execute([$lideres[3], '102d']);
$stmtResponsavel->execute([$lideres[4], '101d']);

echo "✅ Usuários e Responsáveis vinculados com e-mails criptografados.<br>";

// =====================================================
// 3. DEFINIÇÃO DAS SALAS E CAMPOS
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
        'org_bancadas_limpas',
        'org_bancadas_organizadas',
        'org_cadeiras_organizadas',
        'org_materiais_guardados',
        'org_armarios_fechados',
        'org_quadro_limpo',
        'org_piso_limpo',
        'org_corredores_desobstruidos',
        'seg_extintor_acessivel',
        'seg_chuveiro_emergencia_ok',
        'seg_lava_olhos_ok',
        'seg_kit_primeiros_socorros',
        'seg_saidas_emergencia_livres',
        'seg_sinalizacao_visivel',
        'seg_produtos_quimicos_identificados',
        'seg_fispqs_disponiveis',
        'eq_balanca_limpa',
        'eq_balanca_desligada',
        'eq_phmetro_limpo',
        'eq_condutivimetro_limpo',
        'eq_espectrofotometro_limpo',
        'eq_estufa_desligada',
        'eq_autoclave_desligada',
        'eq_equipamentos_desligados',
        'eq_equipamentos_sem_avarias',
        'vid_vidrarias_limpas',
        'vid_vidrarias_secas',
        'vid_vidrarias_guardadas',
        'vid_existe_vidraria_quebrada',
        'pq_frascos_identificados',
        'pq_frascos_fechados',
        'pq_produtos_armazenados',
        'pq_residuos_descartados',
        'enc_pia_limpa',
        'enc_torneiras_fechadas',
        'enc_gas_fechado',
        'enc_agua_fechada',
        'enc_equipamentos_desligados',
        'enc_ar_condicionado_desligado',
        'enc_luzes_apagadas',
        'enc_porta_trancada',
        'enc_lixeiras_esvaziadas',
        'nc_encontrada'
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

$horariosPorPeriodo = [
    'manha' => ['inicio' => '07:30:00', 'fim' => '11:30:00'],
    'tarde' => ['inicio' => '13:30:00', 'fim' => '17:30:00'],
    'noite' => ['inicio' => '19:00:00', 'fim' => '22:30:00']
];

function getProbabilidadeProblema($ano, $mes)
{
    $sazonal = 0.20 * (1 + cos(2 * M_PI * ($mes - 6) / 12));
    $tendencia = -0.015 * ($ano - 2022);
    $base = 0.15 + $tendencia + $sazonal;
    return min(0.60, max(0.04, $base));
}

$anoAtual = (int) date('Y');
$mesAtual = (int) date('m');
$totalInsercoes = 0;

echo "🔄 Gerando histórico de inspeções...<br>";

// =====================================================
// 4. TRANSAÇÃO DE INSERÇÃO DOS CHECKLISTS E RELATÓRIOS
// =====================================================

try {
    $pdo->beginTransaction();

    for ($ano = 2022; $ano <= $anoAtual; $ano++) {
        $mesFim = ($ano == $anoAtual) ? $mesAtual : 12;

        for ($mes = 1; $mes <= $mesFim; $mes++) {
            $prob = getProbabilidadeProblema($ano, $mes);
            $mesStr = sprintf('%02d', $mes);

            $datasBase = [
                date('Y-m-d', strtotime("first friday of $ano-$mesStr")),
                date('Y-m-d', strtotime("third wednesday of $ano-$mesStr"))
            ];

            foreach ($datasBase as $dataBase) {
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

                            foreach ($campos as $campo) {
                                if ($campo === 'nc_encontrada') {
                                    continue;
                                }

                                $var = mt_rand(-12, 12) / 100;
                                $probCampo = max(0.01, min(0.99, $prob + $var));

                                // Para vidraria quebrada: 'sim' indica não conformidade
                                if ($campo === 'vid_existe_vidraria_quebrada') {
                                    $resultado = (mt_rand(1, 100) <= ($probCampo * 100)) ? 'sim' : 'nao';
                                    if ($resultado === 'sim') {
                                        $problemas[] = $campo;
                                    }
                                } else {
                                    $resultado = (mt_rand(1, 100) <= ($probCampo * 100)) ? 'nao' : 'sim';
                                    if ($resultado === 'nao') {
                                        $problemas[] = $campo;
                                    }
                                }

                                $valores[$campo] = $resultado;
                            }

                            // Define 'nc_encontrada' para a tabela 102d
                            if (in_array('nc_encontrada', $campos)) {
                                $valores['nc_encontrada'] = (count($problemas) > 0) ? 'sim' : 'nao';
                            }

                            $observacao = (count($problemas) > 0)
                                ? "Não conformidades apontadas: " . implode(', ', $problemas)
                                : "Inspeção realizada com sucesso. Nada a declarar.";

                            $verificacaoSexta = null;
                            if ($isSexta && $momento === 'fim') {
                                $verificacaoSexta = json_encode([
                                    'limpeza_pesada_realizada' => (mt_rand(1, 10) <= 9) ? 'sim' : 'nao',
                                    'equipamentos_desligados_fds' => (mt_rand(1, 10) <= 8) ? 'sim' : 'nao'
                                ]);
                            }

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

    $pdo->commit();
    echo "<hr><strong>🎉 Processamento concluído com sucesso!</strong><br>";
    echo "Foram inseridos <b>$totalInsercoes</b> registros no banco recriado.<br><br>";
    echo "<b>Credenciais de Acesso de Teste:</b><br>";
    echo "• Líder Exemplo: <code>lenon.yuri@fiemg.com.br</code> | Senha: <code>senai123</code><br>";
    echo "• Usuário Solicitado 1: <code>bianca.borges@fiemg.com.br</code> | Senha: <code>senai123</code><br>";
    echo "• Usuário Solicitado 2: <code>bfernanda@fiemg.com.br</code> | Senha: <code>senai123</code><br>";
    echo "• Usuário Solicitado 3: <code>priscila.vitorino@fiemg.com.br</code> | Senha: <code>senai123</code>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<hr>❌ <strong>Falha crítica ao preencher lote:</strong> " . $e->getMessage();
}
?>