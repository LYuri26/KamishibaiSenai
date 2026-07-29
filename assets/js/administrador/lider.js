/**
 * lider.js - Gestão e Vínculo de Líderes de Ambientes
 * Mapeia os 5 ambientes técnicos e gerencia atribuições de responsabilidade.
 */

document.addEventListener("DOMContentLoaded", () => {
  carregarAmbientesEResponsaveis();
});

async function carregarAmbientesEResponsaveis() {
  try {
    // Uso de caminhos relativos para garantir estabilidade em subdiretórios
    const response = await fetch("api/lider.php?action=listar");
    const data = await response.json();
    if (data.sucesso) {
      renderizarCards(data.ambientes, data.usuarios);
    } else {
      mostrarMensagem("Erro ao carregar dados: " + data.erro, "error");
    }
  } catch (error) {
    mostrarMensagem("Erro de conexão com a API: " + error.message, "error");
  }
}

// Renderiza a grade de ambientes com os respectivos dropdowns de atribuição
function renderizarCards(ambientes, usuarios) {
  const container = document.getElementById("responsaveis-list");
  container.innerHTML = "";

  for (const [ambiente, responsavelAtual] of Object.entries(ambientes)) {
    const card = document.createElement("div");
    card.className = "card-responsavel";

    // Mapeamento visual estrito para cada um dos 5 ambientes de rota do Kamishibai
    const icone = ambiente.includes("104a")
      ? "bi-tv"
      : ambiente.includes("103d")
        ? "bi-pc-display"
        : ambiente.includes("102c")
          ? "bi-tools"
          : ambiente.includes("102d")
            ? "bi-eyedropper" // Pipeta para o Lab de Química/Meio Ambiente
            : ambiente.includes("101d")
              ? "bi-droplet-half" // Gota de processo para a Microdestilaria
              : "bi-building";

    // Capitalização de string para o nome do ambiente técnico
    const nomeAmbienteFormatado = ambiente.toUpperCase();

    // Cria o conteúdo HTML estruturado do card com suporte a data-label no mobile
    card.innerHTML = `
      <div class="card-header-custom">
        <div class="sala-icon"><i class="bi ${icone}"></i></div>
        <h3>Ambiente ${nomeAmbienteFormatado}</h3>
      </div>
      <div class="responsavel-atual">
        <i class="bi bi-person-circle"></i>
        <span>Responsável:</span>
        <span class="nome ${!responsavelAtual ? "vazio" : ""}">
          ${responsavelAtual ? responsavelAtual.nome + " " + (responsavelAtual.sobrenome || "") : "Nenhum atribuído"}
        </span>
      </div>
      <select id="select-${ambiente}" data-ambiente="${ambiente}" aria-label="Selecione o líder para o ambiente ${ambiente}">
        <option value="">-- Atribuir Líder Coordenador --</option>
        ${usuarios
          .map((u) => {
            const cargoFormatado =
              u.cargo.charAt(0).toUpperCase() + u.cargo.slice(1);
            const selected =
              responsavelAtual && responsavelAtual.id == u.id ? "selected" : "";
            return `
                <option value="${u.id}" ${selected}>
                  ${u.nome} ${u.sobrenome} (${cargoFormatado})
                </option>
              `;
          })
          .join("")}
      </select>
      <button class="btn-salvar" onclick="atribuirResponsavel('${ambiente}')">
        <i class="bi bi-check2-circle-fill"></i> Salvar Atribuição
      </button>
    `;
    container.appendChild(card);
  }
}

// Função de salvamento de responsabilidade (Ativa no escopo global)
window.atribuirResponsavel = async function (ambiente) {
  const select = document.querySelector(`#select-${ambiente}`);
  const usuarioId = select.value;

  if (!usuarioId) {
    mostrarMensagem(
      "Por favor, selecione um usuário na lista antes de salvar.",
      "error",
    );
    return;
  }

  try {
    const response = await fetch("api/lider.php?action=atribuir", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ambiente, usuario_id: usuarioId }),
    });
    const data = await response.json();
    if (data.sucesso) {
      mostrarMensagem(
        `Coordenador de ${ambiente.toUpperCase()} atualizado com sucesso!`,
        "success",
      );
      carregarAmbientesEResponsaveis(); // Recarrega a grade em tempo real
    } else {
      mostrarMensagem("Erro ao processar: " + data.erro, "error");
    }
  } catch (error) {
    mostrarMensagem("Falha crítica de envio: " + error.message, "error");
  }
};

// Exibe feedbacks visuais temporizados de ação
function mostrarMensagem(msg, tipo) {
  const div = document.getElementById("message");
  if (!div) return;

  div.textContent = msg;
  div.className = `message ${tipo}`;

  // Limpa agendamento prévio se houver cliques sequenciais rápidos
  clearTimeout(div._timeout);
  div._timeout = setTimeout(() => {
    div.textContent = "";
    div.className = "message";
  }, 4000);
}
