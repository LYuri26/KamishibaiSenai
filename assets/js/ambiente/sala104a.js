// Lista de itens na ordem correta (porta, piso, carteiras A1-E8, outros)
const itens = {
  porta: "Porta de entrada",
  piso: "Piso",
};

// Adiciona carteiras: fileiras A a E, colunas 1 a 8
const fileiras = ["A", "B", "C", "D", "E"];
for (let f of fileiras) {
  for (let c = 1; c <= 8; c++) {
    itens[`carteira_${f}${c}`] = `Carteira ${f}${c}`;
  }
}

// Outros itens
const outros = {
  janela: "Janela",
  mesa_professor: "Mesa do professor",
  cadeira_professor: "Cadeira do professor",
  ar_condicionado: "Ar condicionado",
  televisao: "Televisão",
  quadro: "Quadro",
};
Object.assign(itens, outros);

let currentQuestion = 0;
const answers = {};
const observations = {};

function renderQuestion() {
  const container = document.getElementById("questionsContainer");
  const keys = Object.keys(itens);
  const key = keys[currentQuestion];
  const label = itens[key];

  let html = `<div class="card p-4">
        <h5>${label}</h5>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="resp_${key}" id="${key}_sim" value="sim" ${answers[key] === "sim" ? "checked" : ""}>
            <label class="form-check-label" for="${key}_sim">Sim (avariado)</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="resp_${key}" id="${key}_nao" value="nao" ${answers[key] === "nao" ? "checked" : ""}>
            <label class="form-check-label" for="${key}_nao">Não (ok)</label>
        </div>`;

  if (answers[key] === "sim") {
    html += `<div class="mt-3">
            <label class="form-label">Observação (descreva o defeito):</label>
            <textarea class="form-control" id="obs_${key}">${observations[key] || ""}</textarea>
        </div>`;
  }

  html += `</div>`;
  container.innerHTML = html;

  // Eventos nos radios
  document.querySelectorAll(`input[name="resp_${key}"]`).forEach((radio) => {
    radio.addEventListener("change", (e) => {
      answers[key] = e.target.value;
      if (e.target.value === "nao") {
        delete observations[key];
      }
      renderQuestion();
    });
  });

  if (answers[key] === "sim") {
    document.getElementById(`obs_${key}`).addEventListener("input", (e) => {
      observations[key] = e.target.value;
    });
  }

  // Atualiza botões
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
  const keys = Object.keys(itens);
  const currentKey = keys[currentQuestion];

  if (!answers[currentKey]) {
    alert("Por favor, responda a pergunta antes de prosseguir.");
    return;
  }

  if (
    answers[currentKey] === "sim" &&
    (!observations[currentKey] || observations[currentKey].trim() === "")
  ) {
    alert("Descreva a avaria no campo de observação.");
    return;
  }

  currentQuestion += direction;
  renderQuestion();
}

document
  .getElementById("checklistForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const keys = Object.keys(itens);
    for (let key of keys) {
      if (!answers[key]) {
        alert("Responda todas as perguntas antes de finalizar.");
        return;
      }
      if (
        answers[key] === "sim" &&
        (!observations[key] || observations[key].trim() === "")
      ) {
        alert("Preencha a observação para o item com avaria.");
        return;
      }
    }

    let observacoesFinais = "";
    for (let key of keys) {
      if (answers[key] === "sim") {
        observacoesFinais += `${itens[key]}: ${observations[key]}\n`;
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
        // Reinicia
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
