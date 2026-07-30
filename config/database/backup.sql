-- Criação do banco
CREATE DATABASE IF NOT EXISTS `u196097154_kamishibai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `u196097154_kamishibai`;

-- ========================================================
-- TABELA DO LABORATÓRIO 102D (Laboratório de Química)
-- ========================================================
DROP TABLE IF EXISTS `102d`;

CREATE TABLE IF NOT EXISTS `102d` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    -- 1. ORGANIZAÇÃO
    `org_bancadas_limpas` ENUM('sim', 'nao') NOT NULL COMMENT 'As bancadas estão limpas?',
    `org_bancadas_organizadas` ENUM('sim', 'nao') NOT NULL COMMENT 'As bancadas estão organizadas?',
    `org_cadeiras_organizadas` ENUM('sim', 'nao') NOT NULL COMMENT 'As cadeiras estão organizadas?',
    `org_materiais_guardados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os materiais estão guardados corretamente?',
    `org_armarios_fechados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os armários estão fechados?',
    `org_quadro_limpo` ENUM('sim', 'nao') NOT NULL COMMENT 'O quadro está limpo?',
    `org_piso_limpo` ENUM('sim', 'nao') NOT NULL COMMENT 'O piso está limpo?',
    `org_corredores_desobstruidos` ENUM('sim', 'nao') NOT NULL COMMENT 'Os corredores estão desobstruídos?',
    -- 2. SEGURANÇA
    `seg_extintor_acessivel` ENUM('sim', 'nao') NOT NULL COMMENT 'O extintor está acessível?',
    `seg_chuveiro_emergencia_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'O chuveiro de emergência está em condições adequadas?',
    `seg_lava_olhos_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'O lava-olhos está livre e em condições adequadas?',
    `seg_kit_primeiros_socorros` ENUM('sim', 'nao') NOT NULL COMMENT 'O kit de primeiros socorros está disponível?',
    `seg_saidas_emergencia_livres` ENUM('sim', 'nao') NOT NULL COMMENT 'As saídas de emergência estão livres?',
    `seg_sinalizacao_visivel` ENUM('sim', 'nao') NOT NULL COMMENT 'A sinalização está visível?',
    `seg_produtos_quimicos_identificados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os produtos químicos estão identificados?',
    `seg_fispqs_disponiveis` ENUM('sim', 'nao') NOT NULL COMMENT 'As FISPQs estão disponíveis?',
    -- 3. EQUIPAMENTOS
    `eq_balanca_limpa` ENUM('sim', 'nao') NOT NULL COMMENT 'A balança está limpa?',
    `eq_balanca_desligada` ENUM('sim', 'nao') NOT NULL COMMENT 'A balança está desligada?',
    `eq_phmetro_limpo` ENUM('sim', 'nao') NOT NULL COMMENT 'O pHmetro está limpo?',
    `eq_condutivimetro_limpo` ENUM('sim', 'nao') NOT NULL COMMENT 'O condutivímetro está limpo?',
    `eq_espectrofotometro_limpo` ENUM('sim', 'nao') NOT NULL COMMENT 'O espectrofotômetro está limpo?',
    `eq_estufa_desligada` ENUM('sim', 'nao') NOT NULL COMMENT 'A estufa está desligada?',
    `eq_autoclave_desligada` ENUM('sim', 'nao') NOT NULL COMMENT 'A autoclave está desligada?',
    `eq_equipamentos_desligados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os equipamentos estão desligados?',
    `eq_equipamentos_sem_avarias` ENUM('sim', 'nao') NOT NULL COMMENT 'Os equipamentos estão sem avarias?',
    -- 4. VIDRARIAS
    `vid_vidrarias_limpas` ENUM('sim', 'nao') NOT NULL COMMENT 'As vidrarias estão limpas?',
    `vid_vidrarias_secas` ENUM('sim', 'nao') NOT NULL COMMENT 'As vidrarias estão secas?',
    `vid_vidrarias_guardadas` ENUM('sim', 'nao') NOT NULL COMMENT 'As vidrarias estão guardadas corretamente?',
    `vid_existe_vidraria_quebrada` ENUM('sim', 'nao') NOT NULL COMMENT 'Existe alguma vidraria quebrada?',
    -- 5. PRODUTOS QUÍMICOS
    `pq_frascos_identificados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os frascos estão identificados?',
    `pq_frascos_fechados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os frascos estão fechados?',
    `pq_produtos_armazenados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os produtos estão armazenados corretamente?',
    `pq_residuos_descartados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os resíduos estão sendo descartados corretamente?',
    -- 6. ENCERRAMENTO DO LABORATÓRIO
    `enc_pia_limpa` ENUM('sim', 'nao') NOT NULL COMMENT 'A pia está limpa?',
    `enc_torneiras_fechadas` ENUM('sim', 'nao') NOT NULL COMMENT 'As torneiras estão fechadas?',
    `enc_gas_fechado` ENUM('sim', 'nao') NOT NULL COMMENT 'O gás está fechado?',
    `enc_agua_fechada` ENUM('sim', 'nao') NOT NULL COMMENT 'A água está fechada?',
    `enc_equipamentos_desligados` ENUM('sim', 'nao') NOT NULL COMMENT 'Os equipamentos estão desligados?',
    `enc_ar_condicionado_desligado` ENUM('sim', 'nao') NOT NULL COMMENT 'O ar-condicionado está desligado?',
    `enc_luzes_apagadas` ENUM('sim', 'nao') NOT NULL COMMENT 'As luzes estão apagadas?',
    `enc_porta_trancada` ENUM('sim', 'nao') NOT NULL COMMENT 'A porta está trancada?',
    `enc_lixeiras_esvaziadas` ENUM('sim', 'nao') NOT NULL COMMENT 'As lixeiras estão esvaziadas?',
    -- 7. NÃO CONFORMIDADES
    `nc_encontrada` ENUM('sim', 'nao') NOT NULL COMMENT 'Foi encontrada alguma não conformidade?',
    `verificacao_sexta` JSON NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ========================================================
-- TABELA DA OFICINA 101D (Microdestilaria / Processos Químicos)
-- ========================================================
CREATE TABLE IF NOT EXISTS `101d` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    `porta_janelas_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Porta de acesso, exaustão e janelas funcionam corretamente?',
    `ar_condicionado_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Ar-condicionado e ventilação limpos e funcionando?',
    `bancadas_limpas` ENUM('sim', 'nao') NOT NULL COMMENT 'Bancadas e área da planta limpas, desobstruídas e sem resíduos?',
    `tomadas_fios_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Tomadas de alta potência intactas e sem fiação exposta?',
    `microscopios_b1_quantidade` ENUM('sim', 'nao') NOT NULL COMMENT 'Os 10 Microscópios Ópticos Binoculares estão presentes?',
    `microscopios_b1_integros` ENUM('sim', 'nao') NOT NULL COMMENT 'Microscópios de processo íntegros, limpos e com capa?',
    `estufa_incubadora_b2_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Estufa Incubadora íntegra e limpa?',
    `blocos_digestores_b2_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Bloco Digestor DQO e Bloco Microdigestor Kjehdahl em bom estado?',
    `balancas_analiticas_b3_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'As 3 Balanças Analíticas estão limpas, niveladas e calibradas?',
    `centrifugas_extracao_b3_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Centrífugas de Bancada (2x) e Bateria Sebelin desligadas e organizadas?',
    `destilador_b4_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Destilador Micro Kjehdahl da Bancada 04 higienizado e apto?',
    `cabine_seguranca_csb_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Cabine de Segurança Biológica (Bancada B) limpa e operacional?',
    `microscopio_camera_desktop_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Microscópio com câmera e Desktop ligados e íntegros?',
    `rotaevaporador_gerber_vortex_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Rotaevaporador, Centrífuga Gerber e Agitador Vortex organizados?',
    `estufas_forno_mufla_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Estufas de secagem e Forno Mufla desligados e sem resíduos?',
    `refrigerador_microondas_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Refrigerador e Micro-ondas do Espaço D limpos e em temperatura correta?',
    `armario1_medidores_agua_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Medidores multiparâmetros, Turbidímetros e Colorímetro guardados no Armário 01?',
    `armario2_3_phgametros_banhos_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'pHgâmetros, Refratômetros, Agitadores Mag. e Banhos-maria nos Armários 02 e 03?',
    `armario5_6_aquecimento_agitacao_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Mantas, Chapas, Agitadores mecânicos, Viscosímetro e Ultrassônico nos Armários 05/06?',
    `armario7_medidores_campo_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Decibelímetros, GPS, Câmera 20MP e Contadores de colônias no Armário 07?',
    `epis_seguranca_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'EPIs (óculos, luvas, jalecos) disponíveis e organizados?',
    `descarte_residuos_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Recipientes de descarte químico e de destilados devidamente identificados?',
    `verificacao_sexta` JSON NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ========================================================
-- DEMAIS TABELAS DO SISTEMA
-- ========================================================
CREATE TABLE IF NOT EXISTS `104a` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    `carteiras_organizadas` ENUM('sim', 'nao') NOT NULL,
    `carteiras_quantidade` ENUM('sim', 'nao') NOT NULL,
    `carteiras_danificadas` ENUM('sim', 'nao') NOT NULL,
    `tv_presente` ENUM('sim', 'nao') NOT NULL,
    `tv_integra` ENUM('sim', 'nao') NOT NULL,
    `tv_hdmi` ENUM('sim', 'nao') NOT NULL,
    `tv_cabos_organizados` ENUM('sim', 'nao') NOT NULL,
    `tv_conectada` ENUM('sim', 'nao') NOT NULL,
    `tv_cabos_ok` ENUM('sim', 'nao') NOT NULL,
    `ar_presentes` ENUM('sim', 'nao') NOT NULL,
    `ar_controle` ENUM('sim', 'nao') NOT NULL,
    `ar_danos` ENUM('sim', 'nao') NOT NULL,
    `quadro_limpo` ENUM('sim', 'nao') NOT NULL,
    `quadro_danos` ENUM('sim', 'nao') NOT NULL,
    `quadro_fixo` ENUM('sim', 'nao') NOT NULL,
    `porta_funciona` ENUM('sim', 'nao') NOT NULL,
    `janelas_intactas` ENUM('sim', 'nao') NOT NULL,
    `janelas_vidros` ENUM('sim', 'nao') NOT NULL,
    `tomadas_intactas` ENUM('sim', 'nao') NOT NULL,
    `tomadas_fios` ENUM('sim', 'nao') NOT NULL,
    `tomadas_adaptadores` ENUM('sim', 'nao') NOT NULL,
    `mesa_firme` ENUM('sim', 'nao') NOT NULL,
    `mesa_gavetas` ENUM('sim', 'nao') NOT NULL,
    `cadeira_integra` ENUM('sim', 'nao') NOT NULL,
    `verificacao_sexta` JSON NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `103d` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    `computadores_ligam` ENUM('sim', 'nao') NOT NULL,
    `mouses_funcionam` ENUM('sim', 'nao') NOT NULL,
    `teclados_funcionam` ENUM('sim', 'nao') NOT NULL,
    `monitores_funcionam` ENUM('sim', 'nao') NOT NULL,
    `gabinetes_estado` ENUM('sim', 'nao') NOT NULL,
    `cadeiras_baias` ENUM('sim', 'nao') NOT NULL,
    `ar_condicionado_funciona` ENUM('sim', 'nao') NOT NULL,
    `quadro_limpo` ENUM('sim', 'nao') NOT NULL,
    `mesa_instrutor` ENUM('sim', 'nao') NOT NULL,
    `cadeira_instrutor` ENUM('sim', 'nao') NOT NULL,
    `portao_funciona` ENUM('sim', 'nao') NOT NULL,
    `janelas_intactas` ENUM('sim', 'nao') NOT NULL,
    `tomadas_intactas` ENUM('sim', 'nao') NOT NULL,
    `fios_expostos` ENUM('sim', 'nao') NOT NULL,
    `verificacao_sexta` JSON NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `102c` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    `portao_funciona` ENUM('sim', 'nao') NOT NULL,
    `instrutor_epi` ENUM('sim', 'nao') NOT NULL,
    `box1_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box1_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box1_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box2_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box2_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box2_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box3_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box3_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box3_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box4_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box4_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box4_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box5_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box5_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box5_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box6_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box6_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box7_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box7_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box7_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box8_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box8_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box8_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box9_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box9_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box9_organizacao` ENUM('sim', 'nao') NOT NULL,
    `box10_epi_completo` ENUM('sim', 'nao') NOT NULL,
    `box10_ferramentas_ok` ENUM('sim', 'nao') NOT NULL,
    `box10_organizacao` ENUM('sim', 'nao') NOT NULL,
    `area_limpa` ENUM('sim', 'nao') NOT NULL,
    `area_organizacao` ENUM('sim', 'nao') NOT NULL,
    `equipamentos_local` ENUM('sim', 'nao') NOT NULL,
    `macarico_ok` ENUM('sim', 'nao') NOT NULL,
    `estufa_ok` ENUM('sim', 'nao') NOT NULL,
    `verificacao_sexta` JSON NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `relatorios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inspecao_id` INT NOT NULL,
    `sala` VARCHAR(50) NOT NULL,
    `data` DATE NOT NULL,
    `periodo` ENUM('manha', 'tarde', 'noite') NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    `data_geracao` DATETIME NOT NULL,
    `imagens` TEXT NULL,
    UNIQUE KEY `unique_inspecao` (`inspecao_id`, `sala`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `sobrenome` VARCHAR(100) NOT NULL,
    `email_hash` VARCHAR(64) UNIQUE NOT NULL,
    `email_encrypted` TEXT NOT NULL,
    `cargo` ENUM('instrutor', 'lider') NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    `data_criacao` DATETIME NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `responsaveis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `ambiente` VARCHAR(50) NOT NULL,
    `data_atribuicao` DATETIME NOT NULL,
    UNIQUE KEY `unique_ambiente` (`ambiente`),
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;