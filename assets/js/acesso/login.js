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

      const redirect = sessionStorage.getItem("redirectAfterLogin");

      if (redirect) {
        sessionStorage.removeItem("redirectAfterLogin");
        window.location.href = redirect;
        return;
      }

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

document.addEventListener("DOMContentLoaded", async () => {
  try {
    // Chama install.php para garantir que as tabelas existam
    const response = await fetch("../config/database/install.php");
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    // install.php retorna texto, não JSON. Apenas confirmamos que foi executado.
    const text = await response.text();
    console.log("Install executado:", text);
    exibirMensagem("Sistema configurado com sucesso!", "success");
    // Habilita o botão de login (já está habilitado, mas caso tenha sido desabilitado)
    const submitBtn = document.querySelector(
      "#formLogin button[type='submit']",
    );
    if (submitBtn) submitBtn.disabled = false;
  } catch (error) {
    console.error("Erro ao configurar sistema:", error);
    exibirMensagem(
      "Erro na configuração inicial. Contate o administrador.",
      "danger",
    );
    const submitBtn = document.querySelector(
      "#formLogin button[type='submit']",
    );
    if (submitBtn) submitBtn.disabled = true;
  }
});
