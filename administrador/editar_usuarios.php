<?php
session_start();

// Acesso permitido EXCLUSIVAMENTE para o cargo 'lider'
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_cargo'] ?? '') !== 'lider') {
    header('Location: ../acesso/login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuários | Kamishibai</title>
    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Customizados -->
    <link rel="stylesheet" href="../assets/css/geral.css">
    <link rel="stylesheet" href="../assets/css/administrador/editar_usuarios.css">
</head>

<body class="bg-light">
    <!-- Cabeçalho Dinâmico -->
    <div id="header"></div>

    <!-- Barra de Ações Superior -->
    <div class="container d-flex justify-content-between align-items-center mt-3">
        <button class="btn btn-outline-secondary rounded-pill" onclick="history.back()">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </button>
        <h1 class="h4 mb-0 text-primary">
            <i class="bi bi-people-fill me-2"></i>Controle de Usuários
        </h1>
        <a href="../acesso/api/logout.php" class="btn btn-outline-danger btn-sm rounded-pill">
            <i class="bi bi-box-arrow-right me-1"></i>Sair
        </a>
    </div>

    <!-- Painel de Gerenciamento Principal -->
    <main class="container">
        <!-- Container Central de Mensagens de Erro/Sucesso -->
        <div id="alertasContainer" class="mb-4"></div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i>Lista de Contas
                </h5>
                <button class="btn btn-primary rounded-pill btn-sm d-flex align-items-center" id="btnAdicionarUsuario">
                    <i class="bi bi-plus-circle-fill me-1"></i>Adicionar Usuário
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive" id="tabelaUsuariosWrapper">
                    <table class="table table-hover align-middle" id="tabelaUsuarios">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Sobrenome</th>
                                <th>E-mail</th>
                                <th>Cargo (Nível)</th>
                                <th>Senha (Editar)</th>
                                <th>Sala Vinculada</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody id="listaUsuarios">
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-5">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    Buscando credenciais e ambientes cadastrados...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Botão Voltar Rodapé -->
        <div class="mt-4">
            <a href="index.php" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Voltar para listagem
            </a>
        </div>
    </main>

    <!-- Rodapé Dinâmico -->
    <div id="footer"></div>

    <!-- Scripts de Dependências e Sessão -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/acessorios/componentes.js"></script>
    <script src="../assets/js/administrador/editar_usuarios.js"></script>
    <script src="../assets/js/acesso/verificar_sessao_admin.js"></script>
</body>

</html>