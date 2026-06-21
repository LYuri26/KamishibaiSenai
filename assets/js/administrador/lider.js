document.addEventListener("DOMContentLoaded", () => {
  carregarAmbientesEResponsaveis();
});

async function carregarAmbientesEResponsaveis() {
  try {
    const response = await fetch("/administrador/api/lider.php?action=listar");
    const data = await response.json();
    if (data.sucesso) {
      renderizarCards(data.ambientes, data.usuarios);
    } else {
      mostrarMensagem("Erro ao carregar dados: " + data.erro, "error");
    }
  } catch (error) {
    mostrarMensagem("Erro de conexão: " + error.message, "error");
  }
}

// Função renderizarCards – versão atualizada com novo layout
function renderizarCards(ambientes, usuarios) {
  const container = document.getElementById("responsaveis-list");
  container.innerHTML = "";

  for (const [ambiente, responsavelAtual] of Object.entries(ambientes)) {
    const card = document.createElement("div");
    card.className = "card-responsavel";

    // Ícone baseado no nome da sala
    const icone = ambiente.includes("104a")
      ? "bi-tv"
      : ambiente.includes("103d")
        ? "bi-pc-display"
        : ambiente.includes("102c")
          ? "bi-tools"
          : "bi-building";

    // Cria o conteúdo HTML do card
    card.innerHTML = `
      <div class="card-header-custom">
        <div class="sala-icon"><i class="bi ${icone}"></i></div>
        <h3>${ambiente}</h3>
      </div>
      <div class="responsavel-atual">
        <i class="bi bi-person-circle"></i>
        <span>Responsável:</span>
        <span class="nome ${!responsavelAtual ? "vazio" : ""}">
          ${responsavelAtual ? responsavelAtual.nome : "Nenhum"}
        </span>
      </div>
      <select id="select-${ambiente}" data-ambiente="${ambiente}">
        <option value="">-- Selecione --</option>
        ${usuarios
          .map(
            (u) => `
          <option value="${u.id}" ${responsavelAtual && responsavelAtual.id == u.id ? "selected" : ""}>
            ${u.nome} ${u.sobrenome} (${u.cargo})
          </option>
        `,
          )
          .join("")}
      </select>
      <button class="btn-salvar" onclick="atribuirResponsavel('${ambiente}')">
        <i class="bi bi-check2-circle"></i> Salvar
      </button>
    `;
    container.appendChild(card);
  }
}

// Função atribuirResponsavel – agora global
window.atribuirResponsavel = async function (ambiente) {
  const select = document.querySelector(`#select-${ambiente}`);
  const usuarioId = select.value;
  if (!usuarioId) {
    mostrarMensagem("Selecione um usuário antes de salvar.", "error");
    return;
  }
  try {
    const response = await fetch(
      "/administrador/api/lider.php?action=atribuir",
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ambiente, usuario_id: usuarioId }),
      },
    );
    const data = await response.json();
    if (data.sucesso) {
      mostrarMensagem(
        `Responsável por ${ambiente} atualizado com sucesso!`,
        "success",
      );
      carregarAmbientesEResponsaveis(); // recarrega a lista
    } else {
      mostrarMensagem("Erro: " + data.erro, "error");
    }
  } catch (error) {
    mostrarMensagem("Erro ao salvar: " + error.message, "error");
  }
};

// Função mostrarMensagem – aprimorada para usar o container com classe correta
function mostrarMensagem(msg, tipo) {
  const div = document.getElementById("message");
  // Limpa conteúdo anterior e define a mensagem
  div.textContent = msg;
  div.className = `message ${tipo}`;
  // Remove automaticamente após 4 segundos
  clearTimeout(div._timeout);
  div._timeout = setTimeout(() => {
    div.textContent = "";
    div.className = "message";
  }, 4000);
}
