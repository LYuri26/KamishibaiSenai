// Lista de perguntas (24 itens)
const perguntas = {
  carteiras_organizadas:
    "As carteiras estão organizadas em fileiras regulares?",
  carteiras_quantidade: "Há aproximadamente 40 carteiras disponíveis?",
  carteiras_danificadas:
    "Todas as carteiras estão em bom estado? Ou existe alguma avaria?",
  tv_presente: "A televisão está presente na sala?",
  tv_integra: "A televisão está em bom estado?",
  tv_hdmi: "O cabo HDMI está disponível e conectado?",
  tv_cabos_organizados: "Os cabos estão organizados e sem risco de queda?",
  tv_conectada: "A televisão está conectada à tomada?",
  tv_cabos_ok: "Todos os cabos necessários estão na sala e em bom estado?",
  ar_presentes: "Os dois aparelhos de ar-condicionado estão presentes?",
  ar_controle: "O controle remoto do ar-condicionado está disponível?",
  ar_danos: "Os aparelhos de ar-condicionado estão em bom estado?",
  quadro_limpo: "O quadro está limpo?",
  quadro_danos: "O quadro está em bom estado (sem riscos ou manchas)?",
  quadro_fixo: "O quadro está no local correto e firmemente fixado?",
  porta_funciona: "A porta abre e fecha normalmente?",
  janelas_intactas: "As janelas estão intactas (sem avarias na estrutura)?",
  janelas_vidros: "Todos os vidros das janelas estão inteiros?",
  tomadas_intactas: "As tomadas aparentes estão intactas?",
  tomadas_fios: "Não há fios expostos?",
  tomadas_adaptadores: "Não há adaptadores improvisados nas tomadas?",
  mesa_firme: "A mesa do instrutor está firme e organizada?",
  mesa_gavetas: "As gavetas da mesa estão fechadas e funcionais?",
  cadeira_integra: "A cadeira do instrutor está em bom estado?",
};

const sala = "104a";
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

function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);

  if (currentQuestion === etapaProcedimento) {
    container.innerHTML = `
        <div class="question-card fade-in">
          <h5>📋 Procedimento Operacional da Sala</h5>
          <p><strong>Orientações conforme manual do aluno.</strong></p>
          <hr>
          <h6>👕 Vestimentas e conduta</h6>
          <ul>
            <li>Utilizar vestimenta adequada ao ambiente educacional.</li>
            <li>Manter postura adequada durante as atividades.</li>
            <li>Seguir orientações do instrutor responsável.</li>
          </ul>
          <h6>📚 Uso correto do ambiente</h6>
          <ul>
            <li>Utilizar equipamentos apenas para atividades do curso.</li>
            <li>Não consumir alimentos ou bebidas.</li>
            <li>Não danificar mobiliário ou equipamentos.</li>
          </ul>
          <h6>🔒 Segurança e preservação</h6>
          <ul>
            <li>Comunicar imediatamente qualquer irregularidade.</li>
            <li>Não manipular instalações elétricas.</li>
          </ul>
          <h6>🔚 Encerramento da atividade</h6>
          <ul>
            <li>Organizar carteiras e materiais utilizados.</li>
            <li>Desligar equipamentos utilizados.</li>
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
        <div class="custom-file-upload">
  <label for="img_${key}" class="btn btn-outline-primary w-100">
    📷 Tirar ou anexar foto
  </label>
  <input 
  type="file" 
  id="img_${key}" 
  accept="image/*" 
  capture="environment"
  hidden
>
  <small id="file_name_${key}" class="text-muted"></small>
</div>
      </div>
    `;
  }

  html += `</div>`;
  container.innerHTML = html;

  document.querySelectorAll(`.btn[data-key="${key}"]`).forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const valor = e.currentTarget.dataset.value;
      answers[key] = valor;

      if (valor === "sim") {
        delete observations[key];
        renderQuestion();
        avancarPergunta();
      } else {
        renderQuestion();
      }
    });
  });

  if (temObservacao) {
    document.getElementById(`obs_${key}`).addEventListener("input", (e) => {
      observations[key] = e.target.value;
    });

    document.getElementById(`img_${key}`).addEventListener("change", (e) => {
      const file = e.target.files[0];

      // 🔴 VALIDAÇÃO DE TIPO
      if (file && !file.type.startsWith("image/")) {
        alert("Selecione apenas imagens.");
        e.target.value = "";
        return;
      }

      // 🔴 VALIDAÇÃO DE TAMANHO (5MB)
      if (file && file.size > 5 * 1024 * 1024) {
        alert("Imagem excede 5MB.");
        e.target.value = "";
        return;
      }

      // ✅ ARMAZENAMENTO SEGURO
      images[key] = file;

      const fileNameEl = document.getElementById(`file_name_${key}`);
      if (file && fileNameEl) {
        fileNameEl.textContent = file.name;
      }
    });
  }
  atualizarStatusLuz();
  document.getElementById("prevBtn").disabled = currentQuestion === 0;
  document.getElementById("nextBtn").classList.remove("d-none");
  document.getElementById("submitBtn").classList.add("d-none");

  const progress = ((currentQuestion + 1) / (keys.length + 1)) * 100;
  document.getElementById("progressBar").style.width = progress + "%";
  document.getElementById("progressBar").textContent =
    Math.round(progress) + "%";
}

document.getElementById("prevBtn").addEventListener("click", () => {
  if (currentQuestion > 0) {
    currentQuestion--;
    renderQuestion();
  }
});

document.getElementById("nextBtn").addEventListener("click", () => {
  avancarPergunta();
});

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

document.addEventListener("DOMContentLoaded", async () => {
  await carregarStatusSala();
  preencherNomeInstrutor();
  renderQuestion();
});
