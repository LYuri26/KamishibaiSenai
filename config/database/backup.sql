-- Criação do banco
CREATE DATABASE IF NOT EXISTS `u196097154_kamishibai` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `u196097154_kamishibai`;

-- ========================================================
-- TABELA DO LABORATÓRIO 102D (Análises / Química / Meio Ambiente)
-- ========================================================
CREATE TABLE IF NOT EXISTS `102d` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    -- 1. INFRAESTRUTURA E GERAL
    `porta_janelas_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Porta de acesso e janelas funcionam corretamente?',
    `ar_condicionado_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Ar-condicionado limpo e funcionando?',
    `bancadas_limpas` ENUM('sim', 'nao') NOT NULL COMMENT 'Bancadas gerais limpas, desobstruídas e sem resíduos?',
    `tomadas_fios_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Tomadas intactas e sem fiação exposta?',
    -- 2. BANCADA 01 (Microscopia)
    `microscopios_b1_quantidade` ENUM('sim', 'nao') NOT NULL COMMENT 'Os 10 Microscópios Ópticos Binoculares estão presentes?',
    `microscopios_b1_integros` ENUM('sim', 'nao') NOT NULL COMMENT 'Microscópios íntegros, limpos e com capa de proteção?',
    -- 3. BANCADA 02 (Digestão e Incubação)
    `estufa_incubadora_b2_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Estufa Incubadora íntegra e limpa?',
    `blocos_digestores_b2_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Bloco Digestor DQO e Bloco Microdigestor Kjehdahl em bom estado?',
    -- 4. BANCADA 03 (Pesagem, Extração e Centrifugação)
    `balancas_analiticas_b3_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'As 3 Balanças Analíticas estão limpas, niveladas e calibradas?',
    `centrifugas_extracao_b3_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Centrífugas de Bancada (2x) e Bateria Sebelin desligadas e organizadas?',
    -- 5. BANCADA 04 & BANCADA B
    `destilador_b4_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Destilador Micro Kjehdahl da Bancada 04 higienizado e apto?',
    `cabine_seguranca_csb_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Cabine de Segurança Biológica (Bancada B) limpa e operacional?',
    -- 6. BANCADA D (Equipamentos Avançados e Informática)
    `microscopio_camera_desktop_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Microscópio com câmera e Desktop ligados e íntegros?',
    `rotaevaporador_gerber_vortex_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Rotaevaporador, Centrífuga Gerber e Agitador Vortex organizados?',
    -- 7. ESPAÇO X & ESPAÇO D (Térmicos e Refrigeração)
    `estufas_forno_mufla_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Estufas de secagem e Forno Mufla desligados e sem resíduos?',
    `refrigerador_microondas_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Refrigerador e Micro-ondas do Espaço D limpos e em temperatura correta?',
    -- 8. ARMÁRIOS 01 A 07 (Instrumentação e Acessórios)
    `armario1_medidores_agua_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Medidores multiparâmetros, Turbidímetros e Colorímetro guardados no Armário 01?',
    `armario2_3_phgametros_banhos_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'pHgâmetros, Refratômetros, Agitadores Mag. e Banhos-maria nos Armários 02 e 03?',
    `armario5_6_aquecimento_agitacao_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Mantas, Chapas, Agitadores mecânicos, Viscosímetro e Ultrassônico nos Armários 05/06?',
    `armario7_medidores_campo_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Decibelímetros, GPS, Câmera 20MP e Contadores de colônias no Armário 07?',
    -- 9. SEGURANÇA E DESCARTE (EPI / EPC)
    `epis_seguranca_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'EPIs (óculos, luvas, jalecos) disponíveis e organizados?',
    `descarte_residuos_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Recipientes de descarte químico e biológico devidamente identificados?',
    -- 10. VERIFICAÇÃO DE SEXTA-FEIRA
    `verificacao_sexta` JSON NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ========================================================
-- TABELA DA OFICINA 101D (Microdestilaria / Processos Químicos)
-- ========================================================
CREATE TABLE IF NOT EXISTS `101d` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `data` DATETIME NOT NULL,
    `momento` ENUM('inicio', 'fim') NOT NULL,
    `observacoes` TEXT,
    -- 1. INFRAESTRUTURA E GERAL
    `porta_janelas_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Porta de acesso, exaustão e janelas funcionam corretamente?',
    `ar_condicionado_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Ar-condicionado e ventilação limpos e funcionando?',
    `bancadas_limpas` ENUM('sim', 'nao') NOT NULL COMMENT 'Bancadas e área da planta limpas, desobstruídas e sem resíduos?',
    `tomadas_fios_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Tomadas de alta potência intactas e sem fiação exposta?',
    -- 2. BANCADA 01 (Microscopia de Controle)
    `microscopios_b1_quantidade` ENUM('sim', 'nao') NOT NULL COMMENT 'Os 10 Microscópios Ópticos Binoculares estão presentes?',
    `microscopios_b1_integros` ENUM('sim', 'nao') NOT NULL COMMENT 'Microscópios de processo íntegros, limpos e com capa?',
    -- 3. BANCADA 02 (Digestão e Incubação)
    `estufa_incubadora_b2_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Estufa Incubadora íntegra e limpa?',
    `blocos_digestores_b2_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Bloco Digestor DQO e Bloco Microdigestor Kjehdahl em bom estado?',
    -- 4. BANCADA 03 (Pesagem, Extração e Centrifugação)
    `balancas_analiticas_b3_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'As 3 Balanças Analíticas estão limpas, niveladas e calibradas?',
    `centrifugas_extracao_b3_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Centrífugas de Bancada (2x) e Bateria Sebelin desligadas e organizadas?',
    -- 5. BANCADA 04 & BANCADA B
    `destilador_b4_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Destilador Micro Kjehdahl da Bancada 04 higienizado e apto?',
    `cabine_seguranca_csb_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Cabine de Segurança Biológica (Bancada B) limpa e operacional?',
    -- 6. BANCADA D (Equipamentos Avançados e Informática)
    `microscopio_camera_desktop_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Microscópio com câmera e Desktop ligados e íntegros?',
    `rotaevaporador_gerber_vortex_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Rotaevaporador, Centrífuga Gerber e Agitador Vortex organizados?',
    -- 7. ESPAÇO X & ESPAÇO D (Térmicos e Refrigeração)
    `estufas_forno_mufla_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Estufas de secagem e Forno Mufla desligados e sem resíduos?',
    `refrigerador_microondas_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Refrigerador e Micro-ondas do Espaço D limpos e em temperatura correta?',
    -- 8. ARMÁRIOS 01 A 07 (Instrumentação e Acessórios)
    `armario1_medidores_agua_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Medidores multiparâmetros, Turbidímetros e Colorímetro guardados no Armário 01?',
    `armario2_3_phgametros_banhos_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'pHgâmetros, Refratômetros, Agitadores Mag. e Banhos-maria nos Armários 02 e 03?',
    `armario5_6_aquecimento_agitacao_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Mantas, Chapas, Agitadores mecânicos, Viscosímetro e Ultrassônico nos Armários 05/06?',
    `armario7_medidores_campo_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Decibelímetros, GPS, Câmera 20MP e Contadores de colônias no Armário 07?',
    -- 9. SEGURANÇA E DESCARTE (EPI / EPC)
    `epis_seguranca_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'EPIs (óculos, luvas, jalecos) disponíveis e organizados?',
    `descarte_residuos_ok` ENUM('sim', 'nao') NOT NULL COMMENT 'Recipientes de descarte químico e de destilados devidamente identificados?',
    -- 10. VERIFICAÇÃO DE SEXTA-FEIRA
    `verificacao_sexta` JSON NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ========================================================
-- DEMAIS TABELAS DO SISTEMA
-- ========================================================
-- Tabela da sala 104a
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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Tabela do laboratório 103d
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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Tabela da oficina de soldagem 102c
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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Tabela de relatórios
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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `sobrenome` VARCHAR(100) NOT NULL,
    `email_hash` VARCHAR(64) UNIQUE NOT NULL,
    `email_encrypted` TEXT NOT NULL,
    `cargo` ENUM('instrutor', 'lider') NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    `data_criacao` DATETIME NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Tabela de responsáveis
CREATE TABLE IF NOT EXISTS `responsaveis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `ambiente` VARCHAR(50) NOT NULL,
    `data_atribuicao` DATETIME NOT NULL,
    UNIQUE KEY `unique_ambiente` (`ambiente`),
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;