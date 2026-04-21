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

// Perguntas extras para verificação de sexta-feira (responsável)
const perguntasExtras = {
  projetor_lab: "O projetor multimídia está funcionando?",
  caixas_som: "As caixas de som estão operacionais?",
  nobreak: "O nobreak está funcionando e com bateria ok?",
  rede_internet: "A conexão com a internet está estável?",
  softwares_instalados:
    "Os softwares necessários estão instalados e atualizados?",
};

const sala = "103d";
let currentQuestion = 0;
const answers = {};
const observations = {};
const etapaProcedimento = Object.keys(perguntas).length;
let statusBackendProblema = false;
const images = {};

// Variáveis para responsável e sexta-feira
let usuarioAtual = null;
let isResponsavel = false;
let isSexta = false;
let respostasExtras = {};

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

  const container = statusEl.closest(".status-container");

  if (problemaFinal) {
    statusEl.classList.remove("bg-success");
    statusEl.classList.add("bg-warning");
    statusEl.innerHTML = "⚠️ ATENÇÃO";

    container.classList.add("status-critico");
  } else {
    statusEl.classList.remove("bg-warning");
    statusEl.classList.add("bg-success");
    statusEl.innerHTML = "✅ CONFORME";

    container.classList.remove("status-critico");
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
    usuarioAtual = dados;
  } else {
    nomeInput.disabled = false;
    nomeInput.placeholder = "Digite seu nome completo (não autenticado)";
  }
}

// Verifica se o usuário é responsável pelo ambiente e se hoje é sexta-feira
async function verificarResponsavelESexta() {
  if (!usuarioAtual || !usuarioAtual.id) return;

  try {
    const response = await fetch("/administrador/api/lider.php?action=listar");
    const data = await response.json();
    if (data.sucesso && data.ambientes[sala]) {
      const responsavel = data.ambientes[sala];
      isResponsavel = responsavel && responsavel.id == usuarioAtual.id;
    }

    const hoje = new Date();
    isSexta = hoje.getDay() === 5;

    if (isResponsavel && isSexta) {
      document.getElementById("extra-questions").style.display = "block";
      carregarPerguntasExtras();
    } else {
      document.getElementById("extra-questions").style.display = "none";
    }
  } catch (error) {
    console.error("Erro ao verificar responsável:", error);
  }
}

function carregarPerguntasExtras() {
  const container = document.getElementById("extra-fields-container");
  if (!container) return;

  container.innerHTML = "";
  for (const [key, texto] of Object.entries(perguntasExtras)) {
    const div = document.createElement("div");
    div.className = "mb-3";
    div.innerHTML = `
      <label class="form-label fw-semibold">${texto}</label>
      <div class="btn-group w-100" role="group">
        <button type="button" class="btn btn-outline-success btn-extras" data-key="${key}" data-value="sim">✅ Sim</button>
        <button type="button" class="btn btn-outline-danger btn-extras" data-key="${key}" data-value="nao">❌ Não</button>
      </div>
      <textarea class="form-control mt-2 obs-extra" data-key="${key}" placeholder="Observação (se Não)" style="display: none;"></textarea>
    `;
    container.appendChild(div);
  }

  document.querySelectorAll(".btn-extras").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const key = btn.dataset.key;
      const valor = btn.dataset.value;
      respostasExtras[key] = { valor, observacao: "" };

      const group = btn.closest(".mb-3");
      group.querySelectorAll(".btn-extras").forEach((b) => {
        b.classList.remove("active", "btn-success", "btn-danger");
        if (b.dataset.value === "sim") b.classList.add("btn-outline-success");
        else b.classList.add("btn-outline-danger");
      });
      btn.classList.add("active");
      if (valor === "sim") btn.classList.add("btn-success");
      else btn.classList.add("btn-danger");

      const obsField = group.querySelector(".obs-extra");
      if (valor === "nao") {
        obsField.style.display = "block";
        obsField.addEventListener("input", (e) => {
          respostasExtras[key].observacao = e.target.value;
        });
      } else {
        obsField.style.display = "none";
        respostasExtras[key].observacao = "";
      }
    });
  });
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

    // Validação das perguntas extras se estiverem visíveis
    if (isResponsavel && isSexta) {
      for (let key of Object.keys(perguntasExtras)) {
        if (!respostasExtras[key]) {
          alert("Responda todas as perguntas da verificação de sexta-feira.");
          return;
        }
        if (
          respostasExtras[key].valor === "nao" &&
          !respostasExtras[key].observacao?.trim()
        ) {
          alert(
            "Preencha a observação para itens com problema na verificação de sexta-feira.",
          );
          return;
        }
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

    // Adicionar verificacao_sexta se for responsável e sexta
    if (isResponsavel && isSexta) {
      const extraObj = {};
      for (const [key, data] of Object.entries(respostasExtras)) {
        extraObj[key] = {
          valor: data.valor,
          observacao: data.observacao || "",
        };
      }
      formData.append("verificacao_sexta", JSON.stringify(extraObj));
    }

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
  await preencherNomeInstrutor();
  await verificarResponsavelESexta();
  renderQuestion();
  await carregarStatusSala();
  atualizarStatusLuz();
});
