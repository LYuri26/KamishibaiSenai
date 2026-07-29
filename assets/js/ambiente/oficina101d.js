// Perguntas do checklist para a Oficina 101D (Microdestilaria e Planta Química)
const perguntas = {
  // 1. Infraestrutura e Geral
  porta_janelas_ok:
    "Porta de acesso, exaustores e janelas de segurança funcionam corretamente?",
  ar_condicionado_ok:
    "Climatização e ventilação exaustora limpos e operacionais?",
  bancadas_limpas:
    "Bancadas e piso da planta limpos, secos e livres de obstruções?",
  tomadas_fios_ok:
    "Tomadas elétricas blindadas industriais intactas e fiação isolada?",

  // 2. Bancada 01 (Microscopia de Controle)
  microscopios_b1_quantidade:
    "Os 10 Microscópios Ópticos Binoculares estão devidamente alocados na Bancada 01?",
  microscopios_b1_integros:
    "Os microscópios de acompanhamento estão íntegros, limpos e cobertos?",

  // 3. Bancada 02 (Digestão e Incubação)
  estufa_incubadora_b2_ok:
    "Estufa Incubadora de bancada limpa e operando de forma estável?",
  blocos_digestores_b2_ok:
    "Bloco Digestor DQO e o Bloco Microdigestor de Kjehdahl encontram-se em bom estado?",

  // 4. Bancada 03 (Pesagem, Extração e Centrifugação)
  balancas_analiticas_b3_ok:
    "As 3 Balanças Analíticas estão limpas, calibradas e apoiadas em base firme?",
  centrifugas_extracao_b3_ok:
    "Centrífugas de processo (2x) e a Bateria Sebelin estão desligadas e limpas?",

  // 5. Bancada 04 & Bancada B
  destilador_b4_ok:
    "Destilador Micro Kjehdahl de controle de nitrogênio higienizado e pronto para operação?",
  cabine_seguranca_csb_ok:
    "Cabine de Segurança Biológica (CSB - Bancada B) limpa e operacional?",

  // 6. Bancada D (Equipamentos Avançados e TI)
  microscopio_camera_desktop_ok:
    "O microscópio com câmera digital acoplada e o Desktop de controle estão íntegros?",
  rotaevaporador_gerber_vortex_ok:
    "Rotaevaporador, Centrífuga Gerber e Agitador de tubos Vortex limpos e alinhados?",

  // 7. Espaço X & Espaço D (Térmicos e Refrigeração)
  estufas_forno_mufla_ok:
    "Estufas de secagem e Forno Mufla de alta temperatura limpos, secos e desativados?",
  refrigerador_microondas_ok:
    "Refrigerador de amostras e Micro-ondas do Espaço D limpos e em temperatura adequada?",

  // 8. Armários (Instrumentação)
  armario1_medidores_agua_ok:
    "Medidores multiparâmetros, Turbidímetros e Colorímetro guardados organizados no Armário 01?",
  armario2_3_phgametros_banhos_ok:
    "pHgâmetros, Refratômetros, Agitadores e Banhos-maria organizados nos Armários 02 e 03?",
  armario5_6_aquecimento_agitacao_ok:
    "Mantas aquecedoras, Chapas de agitação, Viscosímetro e ultrassom organizados nos Armários 05/06?",
  armario7_medidores_campo_ok:
    "Decibelímetros, GPS, Câmera digital e Contador de colônias recolhidos ao Armário 07?",

  // 9. Segurança e Descarte (EPI/EPC)
  epis_seguranca_ok:
    "EPIs de proteção química (jalecos, luvas nitrílicas, óculos) organizados e disponíveis?",
  descarte_residuos_ok:
    "Bombonas de descarte de resíduos químicos e destilados tampadas e identificadas?",
};

// Perguntas de verificação de encerramento semanal de sexta-feira (Microdestilaria)
const perguntasExtras = {
  lava_olhos_chuveiro:
    "Chuveiro de emergência e lava-olhos da planta testados e desobstruídos?",
  capela_exaustao:
    "Capela de exaustão de gases industriais limpa, inativa e com janela corta-fogo abaixada?",
  gas_glp:
    "Registros e válvulas de segurança de gás GLP dos destiladores completamente fechados?",
  reagentes_controlados:
    "Armário de solventes orgânicos e reagentes ácidos trancado a chave?",
  residuos_descarte:
    "Bombonas de resíduos acumuladas na semana pesadas, identificadas e enviadas à quarentena?",
};

const sala = "101d";
let currentQuestion = 0;
const answers = {};
const observations = {};
const etapaProcedimento = Object.keys(perguntas).length;
let statusBackendProblema = false;
const images = {};

// Variáveis de controle de acesso
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

// ================= ESTADO VISUAL DA PLANTA =================
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

// Busca dados de sessão
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

// Autocompleta o instrutor logado
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
    nomeInput.placeholder = "Digite seu nome completo";
  }
}

// Verificação de liderança e dia da semana
async function verificarResponsavelESexta() {
  if (!usuarioAtual || !usuarioAtual.id) return;

  try {
    const responseLider = await fetch(
      "/administrador/api/lider.php?action=listar",
    );
    if (responseLider.ok) {
      const data = await responseLider.json();
      if (data.sucesso && data.ambientes[sala]) {
        const responsavel = data.ambientes[sala];
        isResponsavel = responsavel && responsavel.id == usuarioAtual.id;
      }
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
    console.error("Erro ao verificar líder de ambiente:", error);
  }
}

function carregarPerguntasExtras() {
  const container = document.getElementById("extra-fields-container");
  if (!container) return;

  container.innerHTML = "";
  for (const [key, texto] of Object.entries(perguntasExtras)) {
    const div = document.createElement("div");
    div.className = "mb-3 col-md-6";
    div.innerHTML = `
      <label class="form-label fw-semibold">${texto}</label>
      <div class="btn-group w-100" role="group">
        <button type="button" class="btn btn-outline-success btn-extras" data-key="${key}" data-value="sim">✅ Sim</button>
        <button type="button" class="btn btn-outline-danger btn-extras" data-key="${key}" data-value="nao">❌ Não</button>
      </div>
      <textarea class="form-control mt-2 obs-extra" data-key="${key}" placeholder="Explique qual o problema" style="display: none;"></textarea>
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

function avancarPergunta() {
  const keys = Object.keys(perguntas);
  if (currentQuestion >= keys.length) return;

  const currentKey = keys[currentQuestion];
  if (!answers[currentKey]) {
    alert("Responda o item antes de ir para a próxima etapa.");
    return false;
  }

  if (answers[currentKey] === "nao" && !observations[currentKey]?.trim()) {
    alert("Descreva o problema encontrado para registrar a não conformidade.");
    return false;
  }

  currentQuestion++;
  renderQuestion();
  return true;
}

// Renderiza o painel de perguntas
function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);

  // Manual de Boas Práticas da Microdestilaria (Passo Final)
  if (currentQuestion === etapaProcedimento) {
    container.innerHTML = `
      <div class="question-card fade-in">
        <h5>📋 Regras Operacionais e Procedimentos - Planta 101D</h5>
        <p><strong>Leia atentamente e confirme as boas práticas industriais:</strong></p>
        <hr>
        <h6>🧥 Vestimentas e Proteção Química</h6>
        <ul>
          <li>O uso de óculos de segurança de ampla visão e calçado industrial fechado é obrigatório na área de processos.</li>
          <li>Utilize jaleco de proteção contra respingos químicos (manga longa abotoada).</li>
          <li>Uso obrigatório de protetor auricular em ensaios operacionais com compressores ligados.</li>
        </ul>
        <h6>🧪 Controle de Processos e Purificação</h6>
        <ul>
          <li>Familiarize-se com a localização da válvula de alívio rápido de pressão e do botão de emergência da destilaria.</li>
          <li>Antes de iniciar qualquer destilação, teste o fluxo de circulação de água do condensador de resfriamento.</li>
          <li>Vidrarias e reatores de alta pressão trincados devem ser imediatamente descartados.</li>
        </ul>
        <h6>🔒 Prevenção contra Riscos e Incêndios</h6>
        <ul>
          <li>Mantenha solventes inflamáveis e produtos voláteis distantes de fontes de ignição ou aquecedores abertos.</li>
          <li>Conheça a posição física exata do extintor CO2 de incêndio mais próximo.</li>
          <li>Em caso de vazamento, feche a linha principal de fornecimento de fluídos ou vapor imediatamente.</li>
        </ul>
        <h6>🔚 Finalização de Atividades na Planta</h6>
        <ul>
          <li>Drene completamente o conteúdo do refervedor da planta piloto de destilação.</li>
          <li>Garanta que as chapas térmicas, manta, e caldeira elétrica de vapor estejam completamente desativadas.</li>
          <li>Mantenha as bancadas organizadas e encaminhe as vidrarias lavadas para a secadora.</li>
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
        <h5 class="mb-0 text-dark" style="font-size: 1.15rem;">${pergunta}</h5>
        <span class="badge bg-primary rounded-pill">${currentQuestion + 1}/${keys.length}</span>
      </div>
      <div class="btn-group w-100" role="group">
  `;

  const simActive =
    answers[key] === "sim" ? "active btn-primary" : "btn-outline-primary";
  html += `<button type="button" class="btn ${simActive} btn-lg w-50" data-value="sim" data-key="${key}">✅ Sim (ok)</button>`;

  const naoActive =
    answers[key] === "nao" ? "active btn-secondary" : "btn-outline-secondary";
  html += `<button type="button" class="btn ${naoActive} btn-lg w-50" data-value="nao" data-key="${key}">❌ Não (falha)</button>`;
  html += `</div>`;

  if (temObservacao) {
    html += `
    <div class="observacao-field mt-3">
      <label class="form-label fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Observação detalhada:</label>
      <textarea class="form-control" id="obs_${key}" rows="2" placeholder="O que está inconforme...">${observations[key] || ""}</textarea>

      <label class="form-label fw-semibold mt-2"><i class="bi bi-camera-fill me-1"></i>Evidência Fotográfica:</label>
      <div class="custom-file-upload">
        <label for="img_${key}" class="btn btn-outline-primary w-100">
          📷 Registrar ou anexar foto
        </label>
        <input type="file" id="img_${key}" accept="image/*" capture="environment" hidden>
        <small id="file_name_${key}" class="text-muted d-block mt-1 text-center"></small>
      </div>
    </div>
  `;
  }

  html += `</div>`;
  container.innerHTML = html;

  // Ouvintes de clique de botões
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

  // Ouvintes de digitação da observação
  if (temObservacao) {
    document.getElementById(`obs_${key}`).addEventListener("input", (e) => {
      observations[key] = e.target.value;
    });

    document.getElementById(`img_${key}`).addEventListener("change", (e) => {
      const file = e.target.files[0];

      if (file && !file.type.startsWith("image/")) {
        alert("Insira apenas arquivos de imagem.");
        e.target.value = "";
        return;
      }

      if (file && file.size > 5 * 1024 * 1024) {
        alert("Tamanho da imagem excede o limite de 5MB.");
        e.target.value = "";
        return;
      }

      images[key] = file;

      const fileNameEl = document.getElementById(`file_name_${key}`);
      if (file && fileNameEl) {
        fileNameEl.textContent = "✓ " + file.name;
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

// Envio final dos dados
document
  .getElementById("checklistForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const keys = Object.keys(perguntas);

    for (let key of keys) {
      if (!answers[key]) {
        alert("Por favor, responda todas as questões.");
        return;
      }

      if (answers[key] === "nao" && !observations[key]?.trim()) {
        alert("Preencha o detalhamento para itens marcados com problema.");
        return;
      }
    }

    // Validação das perguntas de sexta-feira
    if (isResponsavel && isSexta) {
      for (let key of Object.keys(perguntasExtras)) {
        if (!respostasExtras[key]) {
          alert(
            "Preencha todas as perguntas de verificação semanal de sexta-feira.",
          );
          return;
        }
        if (
          respostasExtras[key].valor === "nao" &&
          !respostasExtras[key].observacao?.trim()
        ) {
          alert(
            "Forneça a observação para problemas na rotina de sexta-feira.",
          );
          return;
        }
      }
    }

    // Empacotamento
    let observacoesFinais = "";
    const formData = new FormData();

    keys.forEach((key) => {
      if (answers[key] === "nao") {
        observacoesFinais += `[${perguntas[key]}]: ${observations[key]}\n`;

        if (images[key]) {
          formData.append("imagens[]", images[key]);
        }
      }
    });

    formData.append("nome", document.getElementById("nome").value.trim());
    formData.append("respostas", JSON.stringify(answers));
    formData.append("observacoes", observacoesFinais);
    formData.append("sala", sala);

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

    try {
      const response = await fetch("api/salvar_checklist.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error("Erro de conexão com servidor.");
      }

      const result = await response.json();

      if (result.sucesso) {
        window.location.href = "../acessorios/encerramento.html";
      } else {
        document.getElementById("mensagem").innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-x-circle me-1"></i>Erro ao salvar dados: ${result.erro}
        </div>`;
      }
    } catch (error) {
      console.error(error);
      document.getElementById("mensagem").innerHTML = `
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-octagon me-1"></i>Falha crítica de comunicação com o servidor. Verifique sua rede e tente novamente.
      </div>`;
    }
  });

// Inicialização automática das rotinas
document.addEventListener("DOMContentLoaded", async () => {
  await preencherNomeInstrutor();
  await verificarResponsavelESexta();
  renderQuestion();
  await carregarStatusSala();
  atualizarStatusLuz();
});
