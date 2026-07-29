// Perguntas do checklist para o laboratório 102d (Análises, Meio Ambiente e Química)
const perguntas = {
  // 1. Infraestrutura e Geral
  porta_janelas_ok: "Porta de acesso e janelas funcionam corretamente?",
  ar_condicionado_ok: "Ar-condicionado limpo e funcionando?",
  bancadas_limpas: "Bancadas gerais limpas, desobstruídas e sem resíduos?",
  tomadas_fios_ok: "Tomadas intactas e sem fiação exposta?",

  // 2. Bancada 01 (Microscopia)
  microscopios_b1_quantidade:
    "Os 10 Microscópios Ópticos Binoculares estão presentes na Bancada 01?",
  microscopios_b1_integros:
    "Microscópios estão íntegros, limpos e com capa de proteção?",

  // 3. Bancada 02 (Digestão e Incubação)
  estufa_incubadora_b2_ok:
    "Estufa Incubadora (Bancada 02) está íntegra e limpa?",
  blocos_digestores_b2_ok:
    "Bloco Digestor DQO e Bloco Microdigestor Kjehdahl estão em bom estado?",

  // 4. Bancada 03 (Pesagem, Extração e Centrifugação)
  balancas_analiticas_b3_ok:
    "As 3 Balanças Analíticas estão limpas, niveladas e calibradas?",
  centrifugas_extracao_b3_ok:
    "Centrífugas de Bancada (2x) e Bateria Sebelin estão desligadas e organizadas?",

  // 5. Bancada 04 & Bancada B
  destilador_b4_ok:
    "Destilador Micro Kjehdahl (Bancada 04) está higienizado e apto?",
  cabine_seguranca_csb_ok:
    "Cabine de Segurança Biológica (CSB - Bancada B) está limpa e operacional?",

  // 6. Bancada D (Avançados e TI)
  microscopio_camera_desktop_ok:
    "Microscópio com câmera e Desktop (Bancada D) estão ligados e íntegros?",
  rotaevaporador_gerber_vortex_ok:
    "Rotaevaporador, Centrífuga Gerber e Agitador Vortex (Bancada D) organizados?",

  // 7. Espaço X & Espaço D (Térmicos e Frio)
  estufas_forno_mufla_ok:
    "Estufas de secagem e Forno Mufla (Espaço X) estão desligados e sem resíduos?",
  refrigerador_microondas_ok:
    "Refrigerador e Micro-ondas (Espaço D) limpos e em temperatura correta?",

  // 8. Armários (Instrumentação)
  armario1_medidores_agua_ok:
    "Medidores multiparâmetros, Turbidímetros e Colorímetro estão organizados no Armário 01?",
  armario2_3_phgametros_banhos_ok:
    "pHgâmetros, Refratômetros, Agitadores Mag. e Banhos-maria organizados nos Armários 02/03?",
  armario5_6_aquecimento_agitacao_ok:
    "Mantas, Chapas, Agitadores mecânicos, Viscosímetro e Ultrassônico organizados nos Armários 05/06?",
  armario7_medidores_campo_ok:
    "Decibelímetros, GPS, Câmera 20MP e Contadores de colônias organizados no Armário 07?",

  // 9. Segurança e Descarte (EPI/EPC)
  epis_seguranca_ok:
    "EPIs (óculos, luvas, jalecos) estão disponíveis e organizados?",
  descarte_residuos_ok:
    "Recipientes de descarte químico e biológico estão devidamente identificados?",
};

// Perguntas extras para verificação de sexta-feira específica para laboratório químico/quarentena
const perguntasExtras = {
  lava_olhos_chuveiro:
    "Lava-olhos e chuveiro de emergência foram testados e estão operacionais?",
  capela_exaustao:
    "A capela de exaustão de gases está limpa, desobstruída e desligada?",
  gas_glp:
    "As válvulas centrais de fornecimento de gás GLP foram fechadas com segurança?",
  reagentes_controlados:
    "Armários contendo reagentes químicos controlados ou perigosos estão trancados?",
  residuos_descarte:
    "Resíduos químicos gerados na semana foram rotulados e encaminhados ao depósito central?",
};

const sala = "102d";
let currentQuestion = 0;
const answers = {};
const observations = {};
const etapaProcedimento = Object.keys(perguntas).length;
let statusBackendProblema = false;
const images = {};

// Variáveis para controle de responsável e sexta-feira
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
    const response = await fetch("../administrador/api/listar_relatorios.php"); // Fallback se listar_relatorios possuir dados do lider ou direto do lider.php
    // Mantendo consistência com chamada lider.php conforme estrutura padrão de endpoints
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
    console.error("Erro ao verificar responsável e período:", error);
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
      <textarea class="form-control mt-2 obs-extra" data-key="${key}" placeholder="Descreva o problema" style="display: none;"></textarea>
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
    alert("Por favor, selecione uma resposta antes de continuar.");
    return false;
  }

  if (answers[currentKey] === "nao" && !observations[currentKey]?.trim()) {
    alert(
      "Preencha a observação apontando a falha/não conformidade encontrada.",
    );
    return false;
  }

  currentQuestion++;
  renderQuestion();
  return true;
}

// Renderiza a pergunta ou as orientações finais de laboratório
function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);

  // Procedimento Operacional Padrão de Química/Análises (Última Etapa)
  if (currentQuestion === etapaProcedimento) {
    container.innerHTML = `
      <div class="question-card fade-in">
        <h5>📋 Manual e Boas Práticas - Laboratório 102D</h5>
        <p><strong>Certifique-se do cumprimento das regras de biossegurança química antes de sair:</strong></p>
        <hr>
        <h6>🧥 Vestimentas e Proteção Individual</h6>
        <ul>
          <li>O uso de jaleco 100% algodão de mangas longas é obrigatório durante todo o tempo de permanência.</li>
          <li>Uso indispensável de óculos de segurança e sapatos fechados.</li>
          <li>Mantenha cabelos compridos sempre presos e evite brincos/anéis soltos.</li>
        </ul>
        <h6>🧪 Manuseio de Reagentes e Vidrarias</h6>
        <ul>
          <li>Nunca manuseie ácidos concentrados fora da capela de exaustão de gases.</li>
          <li>Descarte vidrarias trincadas ou quebradas na caixa coletora de perfurocortantes.</li>
          <li>Siga criteriosamente o descarte seletivo químico e biológico. Nunca descarte reagentes na pia.</li>
        </ul>
        <h6>🔒 Conservação e Segurança Patrimonial</h6>
        <ul>
          <li>Mantenha as balanças analíticas limpas e as portas protetoras fechadas pós-uso.</li>
          <li>Desconecte chapas aquecedoras e destiladores após a utilização técnica.</li>
          <li>Mantenha as portas e janelas fechadas para manter a estabilidade térmica de calibração dos medidores.</li>
        </ul>
        <h6>🔚 Organização Final</h6>
        <ul>
          <li>Lave todas as vidrarias utilizadas com detergente neutro e água destilada.</li>
          <li>Limpe e desinfecte a superfície das bancadas utilizadas.</li>
          <li>Assegure o correto desligamento de equipamentos de refrigeração e segurança elétrica.</li>
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
      <label class="form-label fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Detalhamento:</label>
      <textarea class="form-control" id="obs_${key}" rows="2" placeholder="Explique resumidamente a não conformidade encontrada...">${observations[key] || ""}</textarea>

      <label class="form-label fw-semibold mt-2"><i class="bi bi-camera-fill me-1"></i>Evidência Fotográfica:</label>
      <div class="custom-file-upload">
        <label for="img_${key}" class="btn btn-outline-primary w-100">
          📷 Tirar ou anexar foto do problema
        </label>
        <input type="file" id="img_${key}" accept="image/*" capture="environment" hidden>
        <small id="file_name_${key}" class="text-muted d-block mt-1 text-center"></small>
      </div>
    </div>
  `;
  }

  html += `</div>`;
  container.innerHTML = html;

  // Ouvintes dos botões de resposta
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

  // Ouvintes para área de texto
  if (temObservacao) {
    document.getElementById(`obs_${key}`).addEventListener("input", (e) => {
      observations[key] = e.target.value;
    });

    document.getElementById(`img_${key}`).addEventListener("change", (e) => {
      const file = e.target.files[0];

      // Validação de formato
      if (file && !file.type.startsWith("image/")) {
        alert("Formato inválido. Insira apenas arquivos de imagem.");
        e.target.value = "";
        return;
      }

      // Validação de limite de tamanho (5MB)
      if (file && file.size > 5 * 1024 * 1024) {
        alert("Arquivo muito pesado. O limite de imagem é de 5MB.");
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

  // Ajuste do estado de navegação
  document.getElementById("prevBtn").disabled = currentQuestion === 0;
  document.getElementById("nextBtn").classList.remove("d-none");
  document.getElementById("submitBtn").classList.add("d-none");

  const progress = ((currentQuestion + 1) / (keys.length + 1)) * 100;
  document.getElementById("progressBar").style.width = progress + "%";
  document.getElementById("progressBar").textContent =
    Math.round(progress) + "%";
}

// Navegação manual de retorno
document.getElementById("prevBtn").addEventListener("click", () => {
  if (currentQuestion > 0) {
    currentQuestion--;
    renderQuestion();
  }
});

document.getElementById("nextBtn").addEventListener("click", () => {
  avancarPergunta();
});

// Envio definitivo do formulário
document
  .getElementById("checklistForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const keys = Object.keys(perguntas);

    // Validação geral antes do envio
    for (let key of keys) {
      if (!answers[key]) {
        alert(
          "Por favor, preencha todas as perguntas do checklist antes de finalizar.",
        );
        return;
      }

      if (answers[key] === "nao" && !observations[key]?.trim()) {
        alert("Descreva o problema encontrado em todas as não conformidades.");
        return;
      }
    }

    // Validação das perguntas extras
    if (isResponsavel && isSexta) {
      for (let key of Object.keys(perguntasExtras)) {
        if (!respostasExtras[key]) {
          alert("Preencha todas as perguntas adicionais de sexta-feira.");
          return;
        }
        if (
          respostasExtras[key].valor === "nao" &&
          !respostasExtras[key].observacao?.trim()
        ) {
          alert(
            "Descreva as pendências da verificação semanal de sexta-feira.",
          );
          return;
        }
      }
    }

    // Empacotamento de dados
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

    // Adiciona a rotina de encerramento semanal como JSON se ativada
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

    // Envio ao endpoint do servidor PHP
    try {
      const response = await fetch("api/salvar_checklist.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error("Erro de resposta do servidor.");
      }

      const result = await response.json();

      if (result.sucesso) {
        window.location.href = "../acessorios/encerramento.html";
      } else {
        document.getElementById("mensagem").innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-x-circle me-1"></i>Erro ao salvar: ${result.erro}
        </div>`;
      }
    } catch (error) {
      console.error("Erro no envio:", error);
      document.getElementById("mensagem").innerHTML = `
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-octagon me-1"></i>Não foi possível conectar ao servidor. Verifique a internet e tente novamente.
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
