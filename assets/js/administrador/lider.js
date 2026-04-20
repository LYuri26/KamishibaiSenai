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

function renderizarCards(ambientes, usuarios) {
  const container = document.getElementById("responsaveis-list");
  container.innerHTML = "";
  for (const [ambiente, responsavelAtual] of Object.entries(ambientes)) {
    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `
            <h3>${ambiente}</h3>
            <p><strong>Responsável atual:</strong> ${responsavelAtual ? responsavelAtual.nome : "Nenhum"}</p>
            <select id="select-${ambiente}" data-ambiente="${ambiente}">
                <option value="">-- Selecione um usuário --</option>
                ${usuarios.map((u) => `<option value="${u.id}" ${responsavelAtual && responsavelAtual.id == u.id ? "selected" : ""}>${u.nome} ${u.sobrenome} (${u.cargo})</option>`).join("")}
            </select>
            <button onclick="atribuirResponsavel('${ambiente}')">Salvar</button>
        `;
    container.appendChild(card);
  }
}

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

function mostrarMensagem(msg, tipo) {
  const div = document.getElementById("message");
  div.textContent = msg;
  div.className = `message ${tipo}`;
  setTimeout(() => {
    div.textContent = "";
    div.className = "message";
  }, 4000);
}
