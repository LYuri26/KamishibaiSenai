document.addEventListener("DOMContentLoaded", () => {
  carregarUsuarios();
  carregarSalas();
  document
    .getElementById("btnAdicionarUsuario")
    .addEventListener("click", adicionarUsuario);
});

let salasDisponiveis = [];

async function carregarSalas() {
  try {
    const response = await fetch("api/editar_usuarios.php?action=listar_salas");
    const data = await response.json();
    if (data.sucesso) {
      salasDisponiveis = data.salas;
    } else {
      console.error("Erro ao carregar salas:", data.erro);
    }
  } catch (error) {
    console.error("Erro:", error);
  }
}
async function carregarUsuarios() {
  const tbody = document.getElementById("listaUsuarios");
  tbody.innerHTML = `<tr><td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Carregando...</td></tr>`;

  try {
    const response = await fetch("api/editar_usuarios.php?action=listar");
    const data = await response.json();
    if (!data.sucesso) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-danger text-center">${data.erro}</td></tr>`;
      return;
    }
    const usuarios = data.usuarios;
    if (usuarios.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-secondary">Nenhum usuário encontrado.</td></tr>`;
      return;
    }
    tbody.innerHTML = "";
    usuarios.forEach((usuario) => {
      const tr = document.createElement("tr");
      tr.dataset.id = usuario.id;
      const cargoOptions = ["instrutor", "lider"]
        .map(
          (cargo) =>
            `<option value="${cargo}" ${usuario.cargo === cargo ? "selected" : ""}>${cargo.charAt(0).toUpperCase() + cargo.slice(1)}</option>`,
        )
        .join("");

      // Opções de sala
      let salaOptions = `<option value="">Nenhuma</option>`;
      salasDisponiveis.forEach((sala) => {
        const selected = usuario.ambiente === sala ? "selected" : "";
        salaOptions += `<option value="${sala}" ${selected}>${sala}</option>`;
      });

      tr.innerHTML = `
                <td data-label="ID">${usuario.id}</td>
                <td data-label="Nome"><input type="text" class="form-control form-control-sm" value="${escapeHtml(usuario.nome)}" data-campo="nome"></td>
                <td data-label="Sobrenome"><input type="text" class="form-control form-control-sm" value="${escapeHtml(usuario.sobrenome)}" data-campo="sobrenome"></td>
                <td data-label="Email"><input type="email" class="form-control form-control-sm" value="${escapeHtml(usuario.email)}" data-campo="email"></td>
                <td data-label="Cargo">
                    <select class="form-select form-select-sm" data-campo="cargo">${cargoOptions}</select>
                </td>
                <td data-label="Senha"><input type="password" class="form-control form-control-sm" placeholder="Nova senha (opcional)" data-campo="senha"></td>
                <td data-label="Sala Responsável">
                    <select class="form-select form-select-sm" data-campo="sala">${salaOptions}</select>
                </td>
                <td data-label="Ações">
                    <button class="btn btn-sm btn-success rounded-pill salvar-usuario"><i class="bi bi-check2"></i> Salvar</button>
                    <button class="btn btn-sm btn-danger rounded-pill excluir-usuario"><i class="bi bi-trash"></i></button>
                </td>
            `;
      tbody.appendChild(tr);
    });

    // Adicionar eventos
    document.querySelectorAll(".salvar-usuario").forEach((btn) => {
      btn.addEventListener("click", salvarUsuario);
    });
    document.querySelectorAll(".excluir-usuario").forEach((btn) => {
      btn.addEventListener("click", excluirUsuario);
    });
  } catch (error) {
    console.error("Erro ao carregar usuários:", error);
    tbody.innerHTML = `<tr><td colspan="8" class="text-danger text-center">Erro ao carregar dados: ${error.message}</td></tr>`;
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function obterDadosLinha(tr) {
  const id = tr.dataset.id;
  const nome = tr.querySelector('input[data-campo="nome"]').value.trim();
  const sobrenome = tr
    .querySelector('input[data-campo="sobrenome"]')
    .value.trim();
  const email = tr.querySelector('input[data-campo="email"]').value.trim();
  const cargo = tr.querySelector('select[data-campo="cargo"]').value;
  const senha = tr.querySelector('input[data-campo="senha"]').value;
  const sala = tr.querySelector('select[data-campo="sala"]').value;
  return { id, nome, sobrenome, email, cargo, senha, sala };
}

async function salvarUsuario(event) {
  const btn = event.currentTarget;
  const tr = btn.closest("tr");
  const dados = obterDadosLinha(tr);

  // Validação básica
  if (!dados.nome || !dados.sobrenome || !dados.email) {
    mostrarAlerta("Preencha nome, sobrenome e email.", "warning");
    return;
  }
  if (!dados.email.includes("@")) {
    mostrarAlerta("Email inválido.", "warning");
    return;
  }

  try {
    const response = await fetch("api/editar_usuarios.php?action=atualizar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(dados),
    });
    const result = await response.json();
    if (result.sucesso) {
      mostrarAlerta("Usuário atualizado com sucesso!", "success");
      // Limpar campo senha
      tr.querySelector('input[data-campo="senha"]').value = "";
    } else {
      mostrarAlerta("Erro: " + result.erro, "danger");
    }
  } catch (error) {
    console.error(error);
    mostrarAlerta("Erro ao salvar: " + error.message, "danger");
  }
}

async function excluirUsuario(event) {
  const btn = event.currentTarget;
  const tr = btn.closest("tr");
  const id = tr.dataset.id;
  if (!confirm("Tem certeza que deseja excluir este usuário?")) return;

  try {
    const response = await fetch("api/editar_usuarios.php?action=excluir", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id }),
    });
    const result = await response.json();
    if (result.sucesso) {
      mostrarAlerta("Usuário excluído com sucesso.", "success");
      tr.remove();
    } else {
      mostrarAlerta("Erro: " + result.erro, "danger");
    }
  } catch (error) {
    console.error(error);
    mostrarAlerta("Erro ao excluir: " + error.message, "danger");
  }
}

function adicionarUsuario() {
  // Cria uma nova linha vazia com campos para preencher
  const tbody = document.getElementById("listaUsuarios");
  const tr = document.createElement("tr");
  tr.dataset.id = "new";
  const cargoOptions = ["instrutor", "lider"]
    .map(
      (cargo) =>
        `<option value="${cargo}">${cargo.charAt(0).toUpperCase() + cargo.slice(1)}</option>`,
    )
    .join("");
  let salaOptions = `<option value="">Nenhuma</option>`;
  salasDisponiveis.forEach((sala) => {
    salaOptions += `<option value="${sala}">${sala}</option>`;
  });

  tr.innerHTML = `
        <td data-label="ID">Novo</td>
        <td data-label="Nome"><input type="text" class="form-control form-control-sm" placeholder="Nome" data-campo="nome"></td>
        <td data-label="Sobrenome"><input type="text" class="form-control form-control-sm" placeholder="Sobrenome" data-campo="sobrenome"></td>
        <td data-label="Email"><input type="email" class="form-control form-control-sm" placeholder="Email" data-campo="email"></td>
        <td data-label="Cargo">
            <select class="form-select form-select-sm" data-campo="cargo">${cargoOptions}</select>
        </td>
        <td data-label="Senha"><input type="password" class="form-control form-control-sm" placeholder="Senha" data-campo="senha"></td>
        <td data-label="Sala Responsável">
            <select class="form-select form-select-sm" data-campo="sala">${salaOptions}</select>
        </td>
        <td data-label="Ações">
            <button class="btn btn-sm btn-success rounded-pill salvar-usuario"><i class="bi bi-check2"></i> Salvar</button>
            <button class="btn btn-sm btn-secondary rounded-pill cancelar-novo"><i class="bi bi-x"></i> Cancelar</button>
        </td>
    `;
  tbody.prepend(tr);
  tr.querySelector(".salvar-usuario").addEventListener(
    "click",
    salvarNovoUsuario,
  );
  tr.querySelector(".cancelar-novo").addEventListener("click", () =>
    tr.remove(),
  );
}

async function salvarNovoUsuario(event) {
  const btn = event.currentTarget;
  const tr = btn.closest("tr");
  const dados = obterDadosLinha(tr);
  // Para novo, id não é necessário, mas enviaremos como null
  dados.id = null;
  if (!dados.nome || !dados.sobrenome || !dados.email || !dados.senha) {
    mostrarAlerta(
      "Preencha todos os campos incluindo senha para novo usuário.",
      "warning",
    );
    return;
  }
  if (!dados.email.includes("@")) {
    mostrarAlerta("Email inválido.", "warning");
    return;
  }

  try {
    const response = await fetch("api/editar_usuarios.php?action=criar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(dados),
    });
    const result = await response.json();
    if (result.sucesso) {
      mostrarAlerta("Usuário criado com sucesso!", "success");
      // Recarregar a lista para mostrar com ID e atualizado
      carregarUsuarios();
    } else {
      mostrarAlerta("Erro: " + result.erro, "danger");
    }
  } catch (error) {
    console.error(error);
    mostrarAlerta("Erro ao criar: " + error.message, "danger");
  }
}

function mostrarAlerta(mensagem, tipo = "info") {
  const container = document.getElementById("alertasContainer");
  const alerta = document.createElement("div");
  alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
  alerta.role = "alert";
  alerta.innerHTML = `${mensagem} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
  container.appendChild(alerta);
  // Auto-dismiss após 5 segundos
  setTimeout(() => {
    if (alerta.parentNode) alerta.remove();
  }, 5000);
}
