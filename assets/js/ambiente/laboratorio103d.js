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

const sala = "103d";
let currentQuestion = 0;
const answers = {};
const observations = {};
const etapaProcedimento = Object.keys(perguntas).length;
let statusBackendProblema = false;
const images = {};

async function carregarStatusSala() {
  try {
    const response = await fetch("../administrador/api/listar_inspecoes.php");
    const data = await response.json();

    if (!Array.isArray(data)) return;

    const ultimaSala = data.find((item) => item.sala === sala);

    if (
      ultimaSala &&
      ultimaSala.observacoes &&
      ultimaSala.observacoes.trim() !== ""
    ) {
      statusBackendProblema = true;
    } else {
      statusBackendProblema = false;
    }

    atualizarStatusLuz();
  } catch (error) {
    console.error("Erro ao carregar status da sala:", error);
  }
}

// ================= STATUS VISUAL =================
function atualizarStatusLuz() {
  const statusEl = document.getElementById("statusLuz");
  if (!statusEl) return;

  const possuiProblemaAtual = Object.values(answers).includes("nao");

  const problemaFinal = possuiProblemaAtual || statusBackendProblema;

  if (problemaFinal) {
    statusEl.classList.remove("bg-success");
    statusEl.classList.add("bg-warning");
    statusEl.textContent = "Atenção";
  } else {
    statusEl.classList.remove("bg-warning");
    statusEl.classList.add("bg-success");
    statusEl.textContent = "Conforme";
  }
}

// Busca dados do usuário logado
async function carregarDadosUsuario() {
  try {
    const response = await fetch("../acesso/api/dados_usuario.php");
    const data = await response.json();
    return data.erro ? null : data;
  } catch (error) {
    console.error("Erro ao buscar dados do usuário:", error);
    return null;
  }
}

// Preenche o nome do instrutor automaticamente
async function preencherNomeInstrutor() {
  const nomeInput = document.getElementById("nome");
  if (!nomeInput) return;

  const dados = await carregarDadosUsuario();
  if (dados?.nome) {
    nomeInput.value =
      dados.nome + (dados.sobrenome ? " " + dados.sobrenome : "");
    nomeInput.disabled = true;
  } else {
    nomeInput.disabled = false;
    nomeInput.placeholder = "Digite seu nome completo (não autenticado)";
  }
}

// ================= REGISTRO DE RESPOSTA =================
function registrarResposta(key, valor) {
  answers[key] = valor;

  if (valor === "sim") {
    delete observations[key];
  }

  atualizarStatusLuz();

  if (valor === "sim") {
    renderQuestion();
    avancarPergunta();
  } else {
    renderQuestion();
  }
}

// Avança para a próxima pergunta (com validação)
function avancarPergunta() {
  const keys = Object.keys(perguntas);
  if (currentQuestion >= keys.length) return;

  const currentKey = keys[currentQuestion];
  if (!answers[currentKey]) {
    alert("Por favor, responda a pergunta antes de prosseguir.");
    return false;
  }

  if (answers[currentKey] === "nao" && !observations[currentKey]?.trim()) {
    alert("Descreva o problema no campo de observação.");
    return false;
  }

  currentQuestion++;
  renderQuestion();
  return true;
}

// Renderiza a pergunta atual ou a tela de procedimento
function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);

  // Tela de procedimento final
  if (currentQuestion === etapaProcedimento) {
    container.innerHTML = `
      <div class="question-card fade-in">
        <h5>📋 Procedimento Operacional do Laboratório</h5>
        <p><strong>Orientações conforme manual do aluno.</strong></p>
        <hr>
        <h6>🧥 Vestimentas e EPIs</h6>
        <ul>
          <li>Utilizar vestimenta adequada ao ambiente técnico.</li>
          <li>Evitar roupas que possam enroscar em equipamentos.</li>
          <li>Utilizar pulseira antiestática quando manipular hardware.</li>
          <li>Utilizar óculos ou luvas quando atividade exigir.</li>
        </ul>
        <h6>💻 Uso correto do laboratório</h6>
        <ul>
          <li>Utilizar computadores apenas para atividades do curso.</li>
          <li>Não instalar programas ou alterar configurações.</li>
          <li>Não consumir alimentos ou bebidas.</li>
          <li>Manter mochilas em local indicado.</li>
        </ul>
        <h6>🔒 Segurança e preservação</h6>
        <ul>
          <li>Não manipular equipamentos sem autorização.</li>
          <li>Comunicar imediatamente falhas ou danos.</li>
          <li>Evitar improvisações elétricas.</li>
        </ul>
        <h6>🔚 Encerramento das atividades</h6>
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
    atualizarStatusLuz();
    document.getElementById("progressBar").style.width = "100%";
    document.getElementById("progressBar").textContent = "100%";
    return;
  }

  const key = keys[currentQuestion];
  const pergunta = perguntas[key];
  const temObservacao = answers[key] === "nao";

  let html = `
    <div class="question-card fade-in">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">${pergunta}</h5>
        <span class="badge bg-primary rounded-pill">${currentQuestion + 1}/${keys.length}</span>
      </div>
      <div class="btn-group w-100" role="group">
  `;

  const simActive =
    answers[key] === "sim" ? "active btn-primary" : "btn-outline-primary";
  html += `<button type="button" class="btn ${simActive} btn-lg w-50" data-value="sim" data-key="${key}">✅ Sim (ok)</button>`;

  const naoActive =
    answers[key] === "nao" ? "active btn-secondary" : "btn-outline-secondary";
  html += `<button type="button" class="btn ${naoActive} btn-lg w-50" data-value="nao" data-key="${key}">❌ Não (problema)</button>`;
  html += `</div>`;

  if (temObservacao) {
    html += `
    <div class="observacao-field mt-3">
      <label class="form-label fw-semibold">Observação:</label>
      <textarea class="form-control" id="obs_${key}" rows="2">${observations[key] || ""}</textarea>

      <label class="form-label fw-semibold mt-2">Imagem:</label>
      <input type="file" class="form-control" id="img_${key}" accept="image/*">
    </div>
  `;
  }

  html += `</div>`;
  container.innerHTML = html;

  // Eventos dos botões de resposta
  document.querySelectorAll(`.btn[data-key="${key}"]`).forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const valor = e.currentTarget.dataset.value;
      answers[key] = valor;

      if (valor === "sim") {
        delete observations[key];
        renderQuestion(); // Atualiza a UI (remove campo de observação se houver)
        avancarPergunta(); // Avança automaticamente
      } else {
        renderQuestion(); // Mostra o campo de observação
      }
    });
  });

  // Evento do campo de observação
  if (temObservacao) {
    document.getElementById(`obs_${key}`).addEventListener("input", (e) => {
      observations[key] = e.target.value;
    });

    document.getElementById(`img_${key}`).addEventListener("change", (e) => {
      images[key] = e.target.files[0];
    });
  }
  atualizarStatusLuz();
  // Controle dos botões de navegação
  document.getElementById("prevBtn").disabled = currentQuestion === 0;
  document.getElementById("nextBtn").classList.remove("d-none");
  document.getElementById("submitBtn").classList.add("d-none");

  const progress = ((currentQuestion + 1) / (keys.length + 1)) * 100;
  document.getElementById("progressBar").style.width = progress + "%";
  document.getElementById("progressBar").textContent =
    Math.round(progress) + "%";
}

// Navegação manual
document.getElementById("prevBtn").addEventListener("click", () => {
  if (currentQuestion > 0) {
    currentQuestion--;
    renderQuestion();
  }
});

document.getElementById("nextBtn").addEventListener("click", () => {
  avancarPergunta();
});

// Envio final
document
  .getElementById("checklistForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    // ================= VALIDAÇÃO =================
    const keys = Object.keys(perguntas);

    for (let key of keys) {
      if (!answers[key]) {
        alert("Responda todas as perguntas.");
        return;
      }

      if (answers[key] === "nao" && !observations[key]?.trim()) {
        alert("Preencha a observação para itens com problema.");
        return;
      }
    }

    // ================= MONTAGEM =================
    let observacoesFinais = "";
    const formData = new FormData();

    keys.forEach((key) => {
      if (answers[key] === "nao") {
        observacoesFinais += `${perguntas[key]}: ${observations[key]}\n`;

        if (images[key]) {
          formData.append("imagens[]", images[key]);
        }
      }
    });

    formData.append("nome", document.getElementById("nome").value.trim());
    formData.append("respostas", JSON.stringify(answers));
    formData.append("observacoes", observacoesFinais);
    formData.append("sala", sala);

    // ================= ENVIO =================
    try {
      const response = await fetch("api/salvar_checklist.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error("Falha HTTP");
      }

      const result = await response.json();

      if (result.sucesso) {
        window.location.href = "../acessorios/encerramento.html";
      } else {
        document.getElementById("mensagem").innerHTML =
          `<div class="alert alert-danger">Erro: ${result.erro}</div>`;
      }
    } catch (error) {
      console.error(error);

      document.getElementById("mensagem").innerHTML =
        `<div class="alert alert-danger">Erro na comunicação com o servidor.</div>`;
    }
  });

// Inicialização
document.addEventListener("DOMContentLoaded", async () => {
  preencherNomeInstrutor();
  renderQuestion();
  await carregarStatusSala();
  atualizarStatusLuz();
});
