// Perguntas para o laboratório 103d
const perguntas = {
  computadores_ligam: "Todos os 20 computadores ligam normalmente?",
  mouses_funcionam: "Todos os mouses estão funcionando?",
  teclados_funcionam: "Todos os teclados estão funcionando?",
  monitores_funcionam: "Todos os monitores estão funcionando?",
  gabinetes_estado: "Todos os gabinetes estão em bom estado (sem danos)?",
  cadeiras_baias: "Cada baia possui duas cadeiras em bom estado?",
  ar_condicionado_funciona: "O ar condicionado está funcionando corretamente?",
  quadro_limpo: "O quadro branco está limpo e em bom estado?",
  mesa_instrutor: "A mesa do instrutor está organizada e em bom estado?",
  cadeira_instrutor: "A cadeira do instrutor está em bom estado?",
  portao_funciona: "O portão de acesso abre e fecha corretamente?",
  janelas_intactas: "As janelas estão intactas e fecham corretamente?",
  tomadas_intactas: "As tomadas aparentes estão intactas?",
  fios_expostos: "Não há fios expostos ou improvisações?",
};

// Identificador da sala
const sala = "103d";

let currentQuestion = 0;
const answers = {};
const observations = {};

// etapa final após última pergunta
const etapaProcedimento = Object.keys(perguntas).length;

// Buscar dados do usuário logado
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

// Preencher nome automaticamente
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

document.addEventListener("DOMContentLoaded", preencherNomeInstrutor);

// Renderização da pergunta
function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);

  // ETAPA FINAL – PROCEDIMENTO OPERACIONAL
  if (currentQuestion === etapaProcedimento) {
    container.innerHTML = `
      <div class="question-card">

        <h5>Procedimento Operacional do Laboratório</h5>

        <p><strong>Orientações conforme manual do aluno.</strong></p>

        <hr>

        <h6>Vestimentas e EPIs</h6>
        <ul>
          <li>Utilizar vestimenta adequada ao ambiente técnico.</li>
          <li>Evitar roupas que possam enroscar em equipamentos.</li>
          <li>Utilizar pulseira antiestática quando manipular hardware.</li>
          <li>Utilizar óculos ou luvas quando atividade exigir.</li>
        </ul>

        <h6>Uso correto do laboratório</h6>
        <ul>
          <li>Utilizar computadores apenas para atividades do curso.</li>
          <li>Não instalar programas ou alterar configurações.</li>
          <li>Não consumir alimentos ou bebidas.</li>
          <li>Manter mochilas em local indicado.</li>
        </ul>

        <h6>Segurança e preservação</h6>
        <ul>
          <li>Não manipular equipamentos sem autorização.</li>
          <li>Comunicar imediatamente falhas ou danos.</li>
          <li>Evitar improvisações elétricas.</li>
        </ul>

        <h6>Encerramento das atividades</h6>
        <ul>
          <li>Salvar atividades realizadas.</li>
          <li>Encerrar sistemas corretamente.</li>
          <li>Organizar cadeira e bancada.</li>
          <li>Deixar o ambiente pronto para a próxima turma.</li>
        </ul>

      </div>
    `;

    document.getElementById("prevBtn").disabled = false;
    document.getElementById("nextBtn").classList.add("d-none");
    document.getElementById("submitBtn").classList.remove("d-none");

    document.getElementById("progressBar").style.width = "100%";
    document.getElementById("progressBar").textContent = "100%";

    return;
  }

  const key = keys[currentQuestion];
  const pergunta = perguntas[key];

  let html = `<div class="question-card">
        <h5>${pergunta}</h5>
        <div class="btn-group w-100" role="group">`;

  const simActive =
    answers[key] === "sim" ? "active btn-primary" : "btn-outline-primary";

  html += `<button type="button" class="btn ${simActive} btn-lg w-50"
           data-value="sim" data-key="${key}">
           Sim (ok)
           </button>`;

  const naoActive =
    answers[key] === "nao" ? "active btn-secondary" : "btn-outline-secondary";

  html += `<button type="button" class="btn ${naoActive} btn-lg w-50"
           data-value="nao" data-key="${key}">
           Não (problema)
           </button>`;

  html += `</div>`;

  if (answers[key] === "nao") {
    html += `
      <div class="observacao-field mt-3">

        <label class="form-label fw-semibold">
          Observação (descreva o problema):
        </label>

        <textarea class="form-control"
          id="obs_${key}" rows="2"
          placeholder="Ex: computador não liga, cadeira quebrada...">
          ${observations[key] || ""}
        </textarea>

      </div>
    `;
  }

  html += `</div>`;

  container.innerHTML = html;

  // Eventos dos botões
  document.querySelectorAll(`.btn[data-key="${key}"]`).forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const value = e.currentTarget.dataset.value;

      answers[key] = value;

      if (value === "sim") delete observations[key];

      renderQuestion();
    });
  });

  if (answers[key] === "nao") {
    document.getElementById(`obs_${key}`).addEventListener("input", (e) => {
      observations[key] = e.target.value;
    });
  }

  document.getElementById("prevBtn").disabled = currentQuestion === 0;

  document.getElementById("nextBtn").classList.remove("d-none");
  document.getElementById("submitBtn").classList.add("d-none");

  const progress = ((currentQuestion + 1) / (keys.length + 1)) * 100;

  document.getElementById("progressBar").style.width = progress + "%";

  document.getElementById("progressBar").textContent =
    Math.round(progress) + "%";
}

// Navegação
document.getElementById("prevBtn").addEventListener("click", () => {
  if (currentQuestion > 0) {
    currentQuestion--;
    renderQuestion();
  }
});

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

// Envio
document
  .getElementById("checklistForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const keys = Object.keys(perguntas);

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
        `<div class="alert alert-danger">
        Erro na comunicação com o servidor.
        </div>`;
    }
  });

// Inicialização
renderQuestion();
