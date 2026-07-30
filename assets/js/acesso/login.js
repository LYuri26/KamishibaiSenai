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
      const nomeUsuario = result.nome ? `, ${result.nome}` : "";
      exibirMensagem(`Bem-vindo${nomeUsuario}! Redirecionando...`, "success");

      let redirect = sessionStorage.getItem("redirectAfterLogin");

      if (redirect) {
        sessionStorage.removeItem("redirectAfterLogin");

        // CONVERSÃO GLOBAL: Troca qualquer .html por .php na URL salva
        redirect = redirect.replace(/\.html/gi, ".php");
        window.location.href = redirect;
        return;
      }

      // REDIRECIONAMENTO PADRÃO
      if (result.cargo === "lider") {
        window.location.href = "../administrador/index.php";
      } else {
        window.location.href = "../index.php";
      }
    } else {
      exibirMensagem(result.erro, "danger");
    }
  } catch (error) {
    console.error("Erro no login:", error);
    exibirMensagem(
      "Erro na comunicação com o servidor. Tente novamente.",
      "danger",
    );
  }
});

function exibirMensagem(texto, tipo) {
  const msgElement = document.getElementById("mensagem");
  if (msgElement) {
    msgElement.innerHTML = `<div class="alert alert-${tipo}">${texto}</div>`;
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  try {
    // Garante que o redirect salvo no sessionStorage não seja .html
    let redirectSalvo = sessionStorage.getItem("redirectAfterLogin");
    if (redirectSalvo && redirectSalvo.includes(".html")) {
      sessionStorage.setItem(
        "redirectAfterLogin",
        redirectSalvo.replace(/\.html/gi, ".php"),
      );
    }

    // Executa verificação inicial da estrutura do banco
    const response = await fetch("../config/install.php");
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const text = await response.text();
    console.log("Instalação/Verificação do banco executada:", text);

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
