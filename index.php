<?php
session_start();

// Obtém o cargo da sessão
$cargo = $_SESSION['usuario_cargo'] ?? '';

// Verifica se está logado e se o cargo é 'instrutor' ou 'lider'
if (!isset($_SESSION['usuario_id']) || !in_array($cargo, ['instrutor', 'lider'])) {
    header('Location: acesso/login.html'); // Redireciona para o login
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamishibai - Início</title>
    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CSS Customizados -->
    <link rel="stylesheet" href="assets/css/geral.css">
    <link rel="stylesheet" href="assets/css/acessorios/acessorios.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Cabeçalho -->
    <div id="header"></div>

    <main class="flex-grow-1">
        <!-- Hero Section com gradiente moderno e efeito geométrico -->
        <section class="hero text-white py-5 shadow-sm">
            <div class="container text-center">
                <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                    <i class="bi bi-clipboard-check-fill fs-1 text-light"></i>
                    <h1 class="display-4 fw-bold mb-0">Rota Kamishibai</h1>
                </div>
                <p class="lead fs-5 mx-auto" style="max-width: 650px;">
                    Plataforma analítica e preditiva de inspeção visual para ambientes educacionais e industriais
                </p>
                <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                    <span class="badge rounded-pill">
                        <i class="bi bi-shield-check me-1"></i> Padronizado
                    </span>
                    <span class="badge rounded-pill">
                        <i class="bi bi-lightning-charge me-1"></i> Operação Rápida
                    </span>
                    <span class="badge rounded-pill">
                        <i class="bi bi-graph-up-arrow me-1"></i> Preditivo (IA)
                    </span>
                </div>
            </div>
        </section>

        <!-- Cards de acesso em grid dinâmico -->
        <div class="container my-5">
            <!-- Título de Categoria: Ambientes -->
            <div class="d-flex align-items-center mb-4 border-bottom pb-2">
                <i class="bi bi-building me-2 fs-4 text-success"></i>
                <h2 class="h5 fw-bold text-dark mb-0">Roteiro de Ambientes Técnicos</h2>
            </div>

            <div class="row g-4 justify-content-center mb-5">
                <!-- Card Sala 104a -->
                <div class="col-md-6 col-lg-4">
                    <div class="card nav-card h-100 border-0 shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="icon-circle bg-gradient-success text-white mb-3 mx-auto">
                                <i class="bi bi-tv fs-2"></i>
                            </div>
                            <h3 class="card-title">Sala 104a</h3>
                            <p class="card-text">
                                Inspecione carteiras, TV, ar-condicionado, quadro, porta, janelas, tomadas e móveis do
                                instrutor.
                            </p>
                            <a href="ambiente/sala104a.php" class="btn btn-success rounded-pill px-4 mt-2">
                                Acessar <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card Laboratório 103d -->
                <div class="col-md-6 col-lg-4">
                    <div class="card nav-card h-100 border-0 shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="icon-circle bg-gradient-success text-white mb-3 mx-auto">
                                <i class="bi bi-pc-display fs-2"></i>
                            </div>
                            <h3 class="card-title">Laboratório 103d</h3>
                            <p class="card-text">
                                Inspecione computadores, mouses, teclados, monitores, infraestrutura de rede, portão e
                                janelas.
                            </p>
                            <a href="ambiente/laboratorio103d.php" class="btn btn-success rounded-pill px-4 mt-2">
                                Acessar <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card Oficina 102c -->
                <div class="col-md-6 col-lg-4">
                    <div class="card nav-card h-100 border-0 shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="icon-circle bg-gradient-success text-white mb-3 mx-auto">
                                <i class="bi bi-tools fs-2"></i>
                            </div>
                            <h3 class="card-title">Oficina 102c</h3>
                            <p class="card-text">
                                Inspecione os boxes de soldagem, organização de ferramentas, EPIs do setor e rede
                                pneumática.
                            </p>
                            <a href="ambiente/oficina102c.php" class="btn btn-success rounded-pill px-4 mt-2">
                                Acessar <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card Laboratório 102d -->
                <div class="col-md-6 col-lg-4">
                    <div class="card nav-card h-100 border-0 shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="icon-circle bg-gradient-success text-white mb-3 mx-auto">
                                <i class="bi bi-eyedropper fs-2"></i>
                            </div>
                            <h3 class="card-title">Laboratório 102d</h3>
                            <p class="card-text">
                                Inspecione microscópios, estufas analíticas, balanças, destiladores, cabine biológica e
                                reagentes.
                            </p>
                            <a href="ambiente/laboratorio102d.php" class="btn btn-success rounded-pill px-4 mt-2">
                                Acessar <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card Oficina 101d -->
                <div class="col-md-6 col-lg-4">
                    <div class="card nav-card h-100 border-0 shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="icon-circle bg-gradient-success text-white mb-3 mx-auto">
                                <i class="bi bi-droplet-half fs-2"></i>
                            </div>
                            <h3 class="card-title">Oficina 101d</h3>
                            <p class="card-text">
                                Inspecione a planta piloto de destilação, caldeiras de calor, condensadores, válvulas de
                                escape e armários de instrumentos.
                            </p>
                            <a href="ambiente/oficina101d.php" class="btn btn-success rounded-pill px-4 mt-2">
                                Acessar <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Título de Categoria: Gerenciamento e IA -->
            <div class="d-flex align-items-center mb-4 border-bottom pb-2">
                <i class="bi bi-sliders2 me-2 fs-4 text-primary"></i>
                <h2 class="h5 fw-bold text-dark mb-0">Controle, Governança e Relatórios</h2>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Card Responsáveis -->
                <div class="col-md-6 col-lg-4">
                    <div class="card nav-card h-100 border-0 shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="icon-circle bg-gradient-warning text-white mb-3 mx-auto">
                                <i class="bi bi-people-fill fs-2 text-dark"></i>
                            </div>
                            <h3 class="card-title">Responsáveis</h3>
                            <p class="card-text">
                                Gerencie atribuições e verifique o andamento de rotinas adicionais de sexta-feira.
                            </p>
                            <a href="administrador/lider.php" class="btn btn-outline-warning rounded-pill px-4 mt-2">
                                Gerenciar <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card Administrador -->
                <div class="col-md-6 col-lg-4">
                    <div class="card nav-card h-100 border-0 shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="icon-circle bg-gradient-secondary text-white mb-3 mx-auto">
                                <i class="bi bi-person-workspace fs-2"></i>
                            </div>
                            <h3 class="card-title">Administrador</h3>
                            <p class="card-text">
                                Acesse relatórios analíticos, gráficos históricos de conformidade e as previsões da
                                inteligência artificial.
                            </p>
                            <a href="administrador/index.php" class="btn btn-outline-secondary rounded-pill px-4 mt-2">
                                Painel <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Rodapé -->
    <div id="footer"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/acessorios/componentes.js"></script>
</body>

</html>