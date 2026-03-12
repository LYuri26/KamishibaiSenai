// Perguntas para o laboratório 103d
const perguntas = {
  // Computadores e periféricos (20 baias)
  computadores_ligam: "Todos os 20 computadores ligam normalmente?",
  mouses_funcionam: "Todos os mouses estão funcionando?",
  teclados_funcionam: "Todos os teclados estão funcionando?",
  monitores_funcionam: "Todos os monitores estão funcionando?",
  gabinetes_estado: "Todos os gabinetes estão em bom estado (sem danos)?",
  cadeiras_baias: "Cada baia possui duas cadeiras em bom estado?",
  // Ar condicionado
  ar_condicionado_funciona: "O ar condicionado está funcionando corretamente?",
  // Quadro branco
  quadro_limpo: "O quadro branco está limpo e em bom estado?",
  // Mesa do instrutor
  mesa_instrutor: "A mesa do instrutor está organizada e em bom estado?",
  // Cadeira do instrutor
  cadeira_instrutor: "A cadeira do instrutor está em bom estado?",
  // Portão
  portao_funciona: "O portão de acesso abre e fecha corretamente?",
  // Janelas
  janelas_intactas: "As janelas estão intactas e fecham corretamente?",
  // Tomadas e fiação (opcional, similar ao 104a)
  tomadas_intactas: "As tomadas aparentes estão intactas?",
  fios_expostos: "Não há fios expostos ou improvisações?",
};

let currentQuestion = 0;
const answers = {};
const observations = {};

function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(perguntas);
  const key = keys[currentQuestion];
  const pergunta = perguntas[key];

  let html = `<div class="question-card">
        <h5>${pergunta}</h5>
        <div class="btn-group w-100" role="group">`;

  const simActive =
    answers[key] === "sim" ? "active btn-primary" : "btn-outline-primary";
  html += `<button type="button" class="btn ${simActive} btn-lg w-50" data-value="sim" data-key="${key}">Sim (ok)</button>`;

  const naoActive =
    answers[key] === "nao" ? "active btn-secondary" : "btn-outline-secondary";
  html += `<button type="button" class="btn ${naoActive} btn-lg w-50" data-value="nao" data-key="${key}">Não (problema)</button>`;

  html += `</div>`;

  if (answers[key] === "nao") {
    html += `<div class="observacao-field mt-3">
            <label class="form-label fw-semibold">Observação (descreva o problema):</label>
            <textarea class="form-control" id="obs_${key}" rows="2" placeholder="Ex: computador X não liga, cadeira quebrada...">${observations[key] || ""}</textarea>
        </div>`;
  }

  html += `</div>`;
  container.innerHTML = html;

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

  document.getElementById("prevBtn").disabled = currentQuestion === 0;
  if (currentQuestion === keys.length - 1) {
    document.getElementById("nextBtn").classList.add("d-none");
    document.getElementById("submitBtn").classList.remove("d-none");
  } else {
    document.getElementById("nextBtn").classList.remove("d-none");
    document.getElementById("submitBtn").classList.add("d-none");
  }

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
    };

    try {
      const response = await fetch("api/salvar_checklist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados),
      });
      const result = await response.json();
      if (result.sucesso) {
        document.getElementById("mensagem").innerHTML =
          '<div class="alert alert-success">Inspeção salva com sucesso!</div>';
        document.getElementById("checklistForm").reset();
        Object.keys(answers).forEach((k) => delete answers[k]);
        Object.keys(observations).forEach((k) => delete observations[k]);
        currentQuestion = 0;
        renderQuestion();
      } else {
        document.getElementById("mensagem").innerHTML =
          `<div class="alert alert-danger">Erro: ${result.erro}</div>`;
      }
    } catch (error) {
      document.getElementById("mensagem").innerHTML =
        '<div class="alert alert-danger">Erro na comunicação com o servidor.</div>';
    }
  });

renderQuestion();
