// ================= PERGUNTAS OFICINA 102c =================
const perguntas = {
  portao_funciona: "O laboratório está trancado corretamente após o uso?",

  instrutor_epi: "Os EPIs do instrutor estão completos e em condições de uso?",

  box1_epi_completo: "Box 01 possui todos os EPIs em condições de uso?",
  box1_ferramentas_ok: "Box 01 possui ferramentas organizadas e completas?",
  box1_organizacao: "Box 01 está limpo e com cabos organizados?",

  box2_epi_completo: "Box 02 possui todos os EPIs em condições de uso?",
  box2_ferramentas_ok: "Box 02 possui ferramentas organizadas e completas?",
  box2_organizacao: "Box 02 está limpo e com cabos organizados?",

  box3_epi_completo: "Box 03 possui todos os EPIs em condições de uso?",
  box3_ferramentas_ok: "Box 03 possui ferramentas organizadas e completas?",
  box3_organizacao: "Box 03 está limpo e com cabos organizados?",

  box4_epi_completo: "Box 04 possui todos os EPIs em condições de uso?",
  box4_ferramentas_ok: "Box 04 possui ferramentas organizadas e completas?",
  box4_organizacao: "Box 04 está limpo e com cabos organizados?",

  box5_epi_completo: "Box 05 possui todos os EPIs em condições de uso?",
  box5_ferramentas_ok: "Box 05 possui ferramentas organizadas e completas?",
  box5_organizacao: "Box 05 está limpo e com cabos organizados?",

  box6_epi_completo: "Box 06 possui todos os EPIs em condições de uso?",
  box6_ferramentas_ok: "Box 06 possui ferramentas organizadas e completas?",

  box7_epi_completo: "Box 07 possui todos os EPIs em condições de uso?",
  box7_ferramentas_ok: "Box 07 possui ferramentas organizadas e completas?",
  box7_organizacao: "Box 07 está limpo e com cabos organizados?",

  box8_epi_completo: "Box 08 possui todos os EPIs em condições de uso?",
  box8_ferramentas_ok: "Box 08 possui ferramentas organizadas e completas?",
  box8_organizacao: "Box 08 está limpo e com cabos organizados?",

  box9_epi_completo: "Box 09 possui todos os EPIs em condições de uso?",
  box9_ferramentas_ok: "Box 09 possui ferramentas organizadas e completas?",
  box9_organizacao: "Box 09 está limpo e com cabos organizados?",

  box10_epi_completo: "Box 10 possui todos os EPIs em condições de uso?",
  box10_ferramentas_ok: "Box 10 possui ferramentas organizadas e completas?",
  box10_organizacao: "Box 10 está limpo e com cabos organizados?",

  area_limpa: "A área geral está limpa?",
  area_organizacao: "Os equipamentos estão organizados em seus locais?",
  equipamentos_local: "Todos os equipamentos estão nos locais corretos?",
  macarico_ok: "O maçarico está em condições de uso?",
  estufa_ok: "A estufa de eletrodos está em condições de uso?",
};

const sala = "102c";
let currentQuestion = 0;
const answers = {};
const observations = {};
const images = {};
const etapaProcedimento = Object.keys(perguntas).length;
let statusBackendProblema = false;

// ================= STATUS BACKEND =================
async function carregarStatusSala() {
  try {
    const response = await fetch("../administrador/api/listar_inspecoes.php");
    const data = await response.json();

    if (!Array.isArray(data)) return;

    const ultimaSala = data.find((item) => item.sala === sala);

    statusBackendProblema = !!(
      ultimaSala &&
      ultimaSala.observacoes &&
      ultimaSala.observacoes.trim() !== ""
    );

    atualizarStatusLuz();
  } catch (error) {
    console.error("Erro ao carregar status:", error);
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

// ================= USUÁRIO =================
async function carregarDadosUsuario() {
  try {
    const response = await fetch("../acesso/api/dados_usuario.php");
    const data = await response.json();
    return data.erro ? null : data;
  } catch {
    return null;
  }
}

async function preencherNomeInstrutor() {
  const input = document.getElementById("nome");
  if (!input) return;

  const dados = await carregarDadosUsuario();

  if (dados?.nome) {
    input.value = dados.nome + (dados.sobrenome ? " " + dados.sobrenome : "");
    input.disabled = true;
  } else {
    input.placeholder = "Digite seu nome completo";
  }
}

// ================= CONTROLE =================
function avancarPergunta() {
  const keys = Object.keys(perguntas);

  if (currentQuestion >= keys.length) return;

  const key = keys[currentQuestion];

  if (!answers[key]) {
    alert("Responda antes de avançar.");
    return false;
  }

  if (answers[key] === "nao" && !observations[key]?.trim()) {
    alert("Descreva o problema.");
    return false;
  }

  currentQuestion++;
  renderQuestion();
}

// ================= RENDER =================
function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);

  // ===== PROCEDIMENTO FINAL =====
  if (currentQuestion === etapaProcedimento) {
    container.innerHTML = `
      <div class="question-card fade-in">
        <h5>📋 Procedimento Operacional - Soldagem</h5>
        <hr>
        <ul>
          <li>Utilizar EPIs obrigatórios.</li>
          <li>Verificar cabos e conexões.</li>
          <li>Manter área limpa e organizada.</li>
          <li>Desligar equipamentos após uso.</li>
        </ul>
      </div>
    `;

    document.getElementById("prevBtn").disabled = false;
    document.getElementById("nextBtn").classList.add("d-none");
    document.getElementById("submitBtn").classList.remove("d-none");

    document.getElementById("progressBar").style.width = "100%";
    document.getElementById("progressBar").textContent = "100%";

    atualizarStatusLuz();
    return;
  }

  const key = keys[currentQuestion];
  const pergunta = perguntas[key];
  const temObs = answers[key] === "nao";

  let html = `
    <div class="question-card fade-in">
      <div class="d-flex justify-content-between mb-3">
        <h5>${pergunta}</h5>
        <span class="badge bg-primary">${currentQuestion + 1}/${keys.length}</span>
      </div>

      <div class="btn-group w-100">
  `;

  const simClass =
    answers[key] === "sim" ? "btn-primary active" : "btn-outline-primary";
  const naoClass =
    answers[key] === "nao" ? "btn-secondary active" : "btn-outline-secondary";

  html += `<button class="btn ${simClass}" data-value="sim">✅ Sim</button>`;
  html += `<button class="btn ${naoClass}" data-value="nao">❌ Não</button>`;
  html += `</div>`;

  if (temObs) {
    html += `
      <div class="mt-3">
        <textarea id="obs_${key}" class="form-control">${observations[key] || ""}</textarea>

        <label class="btn btn-outline-primary w-100 mt-2">
          📷 Anexar imagem
          <input type="file" id="img_${key}" hidden accept="image/*">
        </label>

        <small id="file_${key}"></small>
      </div>
    `;
  }

  html += `</div>`;
  container.innerHTML = html;

  // ===== EVENTOS =====
  document.querySelectorAll("button[data-value]").forEach((btn) => {
    btn.onclick = () => {
      const val = btn.dataset.value;
      answers[key] = val;

      if (val === "sim") {
        delete observations[key];
        renderQuestion();
        avancarPergunta();
      } else {
        renderQuestion();
      }
    };
  });

  if (temObs) {
    document.getElementById(`obs_${key}`).oninput = (e) => {
      observations[key] = e.target.value;
    };

    document.getElementById(`img_${key}`).onchange = (e) => {
      const file = e.target.files[0];

      if (!file.type.startsWith("image/")) {
        alert("Somente imagens.");
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        alert("Máximo 5MB.");
        return;
      }

      images[key] = file;
      document.getElementById(`file_${key}`).textContent = file.name;
    };
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

// ================= NAVEGAÇÃO =================
document.getElementById("nextBtn").onclick = avancarPergunta;

document.getElementById("prevBtn").onclick = () => {
  if (currentQuestion > 0) {
    currentQuestion--;
    renderQuestion();
  }
};

// ================= ENVIO =================
document.getElementById("checklistForm").onsubmit = async (e) => {
  e.preventDefault();

  const formData = new FormData();
  let obsFinal = "";

  Object.keys(perguntas).forEach((key) => {
    if (answers[key] === "nao") {
      obsFinal += perguntas[key] + ": " + (observations[key] || "") + "\n";
      if (images[key]) formData.append("imagens[]", images[key]);
    }
  });

  formData.append("nome", document.getElementById("nome").value);
  formData.append("respostas", JSON.stringify(answers));
  formData.append("observacoes", obsFinal);
  formData.append("sala", sala);

  const res = await fetch("api/salvar_checklist.php", {
    method: "POST",
    body: formData,
  });

  const result = await res.json();

  if (result.sucesso) {
    window.location.href = "../acessorios/encerramento.html";
  } else {
    alert(result.erro);
  }
};

// ================= INIT =================
document.addEventListener("DOMContentLoaded", async () => {
  await preencherNomeInstrutor();
  await carregarStatusSala();
  renderQuestion();
});
