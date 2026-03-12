document.getElementById("formLogin").addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const senha = document.getElementById("senha").value;

  const dados = { email, senha };

  try {
    const response = await fetch("api/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(dados),
    });
    const result = await response.json();
    if (result.sucesso) {
      exibirMensagem(`Bem-vindo, ${result.nome}! Redirecionando...`, "success");
      // Redireciona conforme o cargo
      if (result.cargo === "gerencia") {
        window.location.href = "../administrador/index.html";
      } else {
        window.location.href = "../ambiente/sala104a.html";
      }
    } else {
      exibirMensagem(result.erro, "danger");
    }
  } catch (error) {
    exibirMensagem("Erro na comunicação com o servidor", "danger");
  }
});

function exibirMensagem(texto, tipo) {
  const div = document.getElementById("mensagem");
  div.innerHTML = `<div class="alert alert-${tipo}">${texto}</div>`;
}
