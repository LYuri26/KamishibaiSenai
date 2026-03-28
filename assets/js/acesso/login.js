document.getElementById("formLogin").addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const senha = document.getElementById("senha").value;

  try {
    const response = await fetch("api/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, senha }),
    });

    const result = await response.json();

    if (result.sucesso) {
      exibirMensagem(`Bem-vindo, ${result.nome}! Redirecionando...`, "success");

      // Recupera URL salva
      const redirect = sessionStorage.getItem("redirectAfterLogin");

      if (redirect) {
        sessionStorage.removeItem("redirectAfterLogin");
        window.location.href = redirect;
        return;
      }

      // Fallback padrão
      if (result.cargo === "lider") {
        window.location.href = "../administrador/index.html";
      } else {
        window.location.href = "../ambiente/sala104a.html";
      }
    } else {
      exibirMensagem(result.erro, "danger");
    }
  } catch {
    exibirMensagem("Erro na comunicação com o servidor", "danger");
  }
});

function exibirMensagem(texto, tipo) {
  document.getElementById("mensagem").innerHTML =
    `<div class="alert alert-${tipo}">${texto}</div>`;
}
