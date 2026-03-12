// Lista de perguntas consolidadas (24 itens) - formuladas para que "sim" = ok, "não" = problema
const perguntas = {
  // Carteiras
  carteiras_organizadas:
    "As carteiras estão organizadas em fileiras regulares?",
  carteiras_quantidade: "Há aproximadamente 40 carteiras disponíveis?",
  carteiras_danificadas:
    "Todas as carteiras estão em bom estado? Ou existe alguma avaria?",
  // Televisão
  tv_presente: "A televisão está presente na sala?",
  tv_integra: "A televisão está em bom estado?",
  tv_hdmi: "O cabo HDMI está disponível e conectado?",
  tv_cabos_organizados: "Os cabos estão organizados e sem risco de queda?",
  tv_conectada: "A televisão está conectada à tomada?",
  tv_cabos_ok: "Todos os cabos necessários estão na sala e em bom estado?",
  // Ar-condicionado
  ar_presentes: "Os dois aparelhos de ar-condicionado estão presentes?",
  ar_controle: "O controle remoto do ar-condicionado está disponível?",
  ar_danos: "Os aparelhos de ar-condicionado estão em bom estado?",
  // Quadro
  quadro_limpo: "O quadro está limpo?",
  quadro_danos: "O quadro está em bom estado (sem riscos ou manchas)?",
  quadro_fixo: "O quadro está no local correto e firmemente fixado?",
  // Porta e Janelas
  porta_funciona: "A porta abre e fecha normalmente?",
  janelas_intactas: "As janelas estão intactas (sem avarias na estrutura)?",
  janelas_vidros: "Todos os vidros das janelas estão inteiros?",
  // Tomadas
  tomadas_intactas: "As tomadas aparentes estão intactas?",
  tomadas_fios: "Não há fios expostos?",
  tomadas_adaptadores: "Não há adaptadores improvisados nas tomadas?",
  // Mesa e Cadeira do Instrutor
  mesa_firme: "A mesa do instrutor está firme e organizada?",
  mesa_gavetas: "As gavetas da mesa estão fechadas e funcionais?",
  cadeira_integra: "A cadeira do instrutor está em bom estado?",
};

// Identificador da sala (obrigatório para o PHP)
const sala = "104a";

let currentQuestion = 0;
const answers = {};
const observations = {};

// Função para buscar dados do usuário logado
async function carregarDadosUsuario() {
  try {
    const response = await fetch("../acesso/api/dados_usuario.php");
    const data = await response.json();
    if (data.erro) return null;
    return data;
  } catch (error) {
    console.error("Erro ao buscar dados do usuário:", error);
    return null;
  }
}

// Preencher nome do instrutor automaticamente e desabilitar edição
async function preencherNomeInstrutor() {
  const nomeInput = document.getElementById("nome");
  if (!nomeInput) return;

  const dados = await carregarDadosUsuario();
  if (dados && dados.nome) {
    nomeInput.value =
      dados.nome + (dados.sobrenome ? " " + dados.sobrenome : "");
    nomeInput.disabled = true;
  } else {
    nomeInput.disabled = false;
    nomeInput.placeholder = "Digite seu nome completo (não autenticado)";
  }
}

// Chama a função após o carregamento da página
document.addEventListener("DOMContentLoaded", preencherNomeInstrutor);

function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);
  const key = keys[currentQuestion];
  const pergunta = perguntas[key];

  let html = `<div class="question-card">
        <h5>${pergunta}</h5>
        <div class="btn-group w-100" role="group">`;

  // Botão Sim (ok)
  const simActive =
    answers[key] === "sim" ? "active btn-primary" : "btn-outline-primary";
  html += `<button type="button" class="btn ${simActive} btn-lg w-50" data-value="sim" data-key="${key}">Sim (ok)</button>`;

  // Botão Não (problema)
  const naoActive =
    answers[key] === "nao" ? "active btn-secondary" : "btn-outline-secondary";
  html += `<button type="button" class="btn ${naoActive} btn-lg w-50" data-value="nao" data-key="${key}">Não (problema)</button>`;

  html += `</div>`;

  // Se respondeu "não", aparece campo de observação
  if (answers[key] === "nao") {
    html += `<div class="observacao-field mt-3">
            <label class="form-label fw-semibold">Observação (descreva o problema):</label>
            <textarea class="form-control" id="obs_${key}" rows="2" placeholder="Ex: cadeira quebrada, TV sem cabo...">${observations[key] || ""}</textarea>
        </div>`;
  }

  html += `</div>`;
  container.innerHTML = html;

  // Eventos nos botões
  document.querySelectorAll(`.btn[data-key="${key}"]`).forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const value = e.currentTarget.dataset.value;
      answers[key] = value;
      if (value === "sim") {
        delete observations[key];
      }
      renderQuestion();
    });
  });

  if (answers[key] === "nao") {
    document.getElementById(`obs_${key}`).addEventListener("input", (e) => {
      observations[key] = e.target.value;
    });
  }

  // Atualiza botões de navegação
  document.getElementById("prevBtn").disabled = currentQuestion === 0;
  if (currentQuestion === keys.length - 1) {
    document.getElementById("nextBtn").classList.add("d-none");
    document.getElementById("submitBtn").classList.remove("d-none");
  } else {
    document.getElementById("nextBtn").classList.remove("d-none");
    document.getElementById("submitBtn").classList.add("d-none");
  }

  // Atualiza barra de progresso
  const progress = ((currentQuestion + 1) / keys.length) * 100;
  document.getElementById("progressBar").style.width = progress + "%";
  document.getElementById("progressBar").textContent =
    Math.round(progress) + "%";
}

document
  .getElementById("prevBtn")
  .addEventListener("click", () => changeQuestion(-1));
document
  .getElementById("nextBtn")
  .addEventListener("click", () => changeQuestion(1));

function changeQuestion(direction) {
  const keys = Object.keys(perguntas);
  const currentKey = keys[currentQuestion];

  if (!answers[currentKey]) {
    alert("Por favor, responda a pergunta antes de prosseguir.");
    return;
  }

  if (
    answers[currentKey] === "nao" &&
    (!observations[currentKey] || observations[currentKey].trim() === "")
  ) {
    alert("Descreva o problema no campo de observação.");
    return;
  }

  currentQuestion += direction;
  renderQuestion();
}

document
  .getElementById("checklistForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const keys = Object.keys(perguntas);
    for (let key of keys) {
      if (!answers[key]) {
        alert("Responda todas as perguntas antes de finalizar.");
        return;
      }
      if (
        answers[key] === "nao" &&
        (!observations[key] || observations[key].trim() === "")
      ) {
        alert("Preencha a observação para o item com problema.");
        return;
      }
    }

    // Monta observações finais apenas para os itens com "não"
    let observacoesFinais = "";
    for (let key of keys) {
      if (answers[key] === "nao") {
        observacoesFinais += `${perguntas[key]}: ${observations[key]}\n`;
      }
    }

    const dados = {
      nome: document.getElementById("nome").value,
      respostas: answers,
      observacoes: observacoesFinais,
      sala: sala,
    };

    try {
      const response = await fetch("api/salvar_checklist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados),
      });
      const result = await response.json();
      if (result.sucesso) {
        window.location.href = "../acessorios/encerramento.html";
      } else {
        document.getElementById("mensagem").innerHTML =
          `<div class="alert alert-danger">Erro: ${result.erro}</div>`;
      }
    } catch (error) {
      document.getElementById("mensagem").innerHTML =
        '<div class="alert alert-danger">Erro na comunicação com o servidor.</div>';
    }
  });

// Inicialização
renderQuestion();
