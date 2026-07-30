document.getElementById("formLogin").addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const senha = document.getElementById("senha").value;

  try {
    // Aponta para a API em PHP
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

        // CORREÇÃO CRÍTICA: Substitui qualquer referência .html por .php
        redirect = redirect.replace(/\.html$/i, ".php");
        window.location.href = redirect;
        return;
      }

      // REDIRECIONAMENTO PADRÃO APONTANDO PARA .PHP
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
    // Limpa redirecionamento antigo se for .html residual
    const redirectSalvo = sessionStorage.getItem("redirectAfterLogin");
    if (redirectSalvo && redirectSalvo.endsWith(".html")) {
      sessionStorage.setItem(
        "redirectAfterLogin",
        redirectSalvo.replace(/\.html$/i, ".php"),
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
