// Perguntas do checklist para o laboratório 102d (Laboratório de Química)
const perguntas = {
  // 1. ORGANIZAÇÃO
  org_bancadas_limpas: "As bancadas estão limpas?",
  org_bancadas_organizadas: "As bancadas estão organizadas?",
  org_cadeiras_organizadas: "As cadeiras estão organizadas?",
  org_materiais_guardados: "Os materiais estão guardados corretamente?",
  org_armarios_fechados: "Os armários estão fechados?",
  org_quadro_limpo: "O quadro está limpo?",
  org_piso_limpo: "O piso está limpo?",
  org_corredores_desobstruidos: "Os corredores estão desobstruídos?",

  // 2. SEGURANÇA
  seg_extintor_acessivel: "O extintor está acessível?",
  seg_chuveiro_emergencia_ok:
    "O chuveiro de emergência está em condições adequadas?",
  seg_lava_olhos_ok: "O lava-olhos está livre e em condições adequadas?",
  seg_kit_primeiros_socorros: "O kit de primeiros socorros está disponível?",
  seg_saidas_emergencia_livres: "As saídas de emergência estão livres?",
  seg_sinalizacao_visivel: "A sinalização está visível?",
  seg_produtos_quimicos_identificados:
    "Os produtos químicos estão identificados?",
  seg_fispqs_disponiveis: "As FISPQs estão disponíveis?",

  // 3. EQUIPAMENTOS
  eq_balanca_limpa: "A balança está limpa?",
  eq_balanca_desligada: "A balança está desligada?",
  eq_phmetro_limpo: "O pHmetro está limpo?",
  eq_condutivimetro_limpo: "O condutivímetro está limpo?",
  eq_espectrofotometro_limpo: "O espectrofotômetro está limpo?",
  eq_estufa_desligada: "A estufa está desligada?",
  eq_autoclave_desligada: "A autoclave está desligada?",
  eq_equipamentos_desligados: "Os equipamentos estão desligados?",
  eq_equipamentos_sem_avarias: "Os equipamentos estão sem avarias?",

  // 4. VIDRARIAS
  vid_vidrarias_limpas: "As vidrarias estão limpas?",
  vid_vidrarias_secas: "As vidrarias estão secas?",
  vid_vidrarias_guardadas: "As vidrarias estão guardadas corretamente?",
  vid_existe_vidraria_quebrada: "Existe alguma vidraria quebrada?",

  // 5. PRODUTOS QUÍMICOS
  pq_frascos_identificados: "Os frascos estão identificados?",
  pq_frascos_fechados: "Os frascos estão fechados?",
  pq_produtos_armazenados: "Os produtos estão armazenados corretamente?",
  pq_residuos_descartados: "Os resíduos estão sendo descartados corretamente?",

  // 6. ENCERRAMENTO DO LABORATÓRIO
  enc_pia_limpa: "A pia está limpa?",
  enc_torneiras_fechadas: "As torneiras estão fechadas?",
  enc_gas_fechado: "O gás está fechado?",
  enc_agua_fechada: "A água está fechada?",
  enc_equipamentos_desligados: "Os equipamentos estão desligados?",
  enc_ar_condicionado_desligado: "O ar-condicionado está desligado?",
  enc_luzes_apagadas: "As luzes estão apagadas?",
  enc_porta_trancada: "A porta está trancada?",
  enc_lixeiras_esvaziadas: "As lixeiras estão esvaziadas?",

  // 7. NÃO CONFORMIDADES
  nc_encontrada: "Foi encontrada alguma não conformidade?",
};

// Perguntas extras para verificação de sexta-feira específica para laboratório químico
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

// Auxiliar para identificar se a resposta dada representa uma Não Conformidade (Problema)
function eProblema(key, valor) {
  if (key === "vid_existe_vidraria_quebrada" || key === "nc_encontrada") {
    return valor === "sim"; // Nestas perguntas, responder 'Sim' indica falha/problema
  }
  return valor === "nao"; // Nas demais, responder 'Não' indica falha/problema
}

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

  const possuiProblemaAtual = Object.entries(answers).some(([key, val]) =>
    eProblema(key, val),
  );
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

// Avança para a próxima pergunta (com validação)
function avancarPergunta() {
  const keys = Object.keys(perguntas);
  if (currentQuestion >= keys.length) return;

  const currentKey = keys[currentQuestion];
  if (!answers[currentKey]) {
    alert("Por favor, selecione uma resposta antes de continuar.");
    return false;
  }

  if (
    eProblema(currentKey, answers[currentKey]) &&
    !observations[currentKey]?.trim()
  ) {
    alert(
      "Preencha a observação apontando a falha/não conformidade encontrada.",
    );
    return false;
  }

  currentQuestion++;
  renderQuestion();
  return true;
}

// Renderiza a pergunta ou as orientações finais do laboratório de química
function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);

  // Procedimento Operacional Padrão de Química (Última Etapa)
  if (currentQuestion === etapaProcedimento) {
    container.innerHTML = `
      <div class="question-card fade-in">
        <h5>📋 Manual e Boas Práticas - Laboratório 102D (Química)</h5>
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
          <li>Nunca manuseie ácidos concentrados ou solventes voláteis fora da capela de exaustão de gases.</li>
          <li>Descarte vidrarias trincadas ou quebradas na caixa coletora de perfurocortantes.</li>
          <li>Siga criteriosamente o descarte seletivo químico e biológico. Nunca descarte reagentes na pia.</li>
        </ul>
        <h6>🔒 Conservação e Segurança Patrimonial</h6>
        <ul>
          <li>Mantenha as balanças analíticas limpas e as portas protetoras fechadas pós-uso.</li>
          <li>Desconecte chapas aquecedoras, estufas e autoclaves após a utilização técnica.</li>
          <li>Mantenha as portas e janelas fechadas para estabilidade dos medidores.</li>
        </ul>
        <h6>🔚 Organização Final</h6>
        <ul>
          <li>Lave todas as vidrarias utilizadas com detergente neutro e água destilada.</li>
          <li>Limpe e desinfete a superfície das bancadas utilizadas.</li>
          <li>Garanta que as torneiras, gás, exaustores e luzes estejam devidamente desligados.</li>
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
  const temObservacao = eProblema(key, answers[key]);

  // Define rótulos de botão adaptados conforme o tipo de pergunta
  let rotuloSim = "✅ Sim (ok)";
  let rotuloNao = "❌ Não (falha)";

  if (key === "vid_existe_vidraria_quebrada" || key === "nc_encontrada") {
    rotuloSim = "❌ Sim (falha)";
    rotuloNao = "✅ Não (ok)";
  }

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
  html += `<button type="button" class="btn ${simActive} btn-lg w-50" data-value="sim" data-key="${key}">${rotuloSim}</button>`;

  const naoActive =
    answers[key] === "nao" ? "active btn-secondary" : "btn-outline-secondary";
  html += `<button type="button" class="btn ${naoActive} btn-lg w-50" data-value="nao" data-key="${key}">${rotuloNao}</button>`;
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

      if (!eProblema(key, valor)) {
        delete observations[key];
        delete images[key];
        renderQuestion();
        avancarPergunta();
      } else {
        renderQuestion();
      }
    });
  });

  // Ouvintes para área de texto e imagem
  if (temObservacao) {
    const obsElem = document.getElementById(`obs_${key}`);
    if (obsElem) {
      obsElem.addEventListener("input", (e) => {
        observations[key] = e.target.value;
      });
    }

    const imgElem = document.getElementById(`img_${key}`);
    if (imgElem) {
      imgElem.addEventListener("change", (e) => {
        const file = e.target.files[0];

        if (file && !file.type.startsWith("image/")) {
          alert("Formato inválido. Insira apenas arquivos de imagem.");
          e.target.value = "";
          return;
        }

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

      if (eProblema(key, answers[key]) && !observations[key]?.trim()) {
        alert("Descreva o problema encontrado em todas as não conformidades.");
        return;
      }
    }

    // Validação das perguntas extras de sexta-feira
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
      if (eProblema(key, answers[key])) {
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
