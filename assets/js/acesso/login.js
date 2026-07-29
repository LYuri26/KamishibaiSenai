document.getElementById("formLogin").addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const senha = document.getElementById("senha").value;

  try {
    // 1. AJUSTE CRÍTICO: Aponta para a API em PHP (api/login.php)
    const response = await fetch("api/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, senha }),
    });

    const result = await response.json();

    if (result.sucesso) {
      const nomeUsuario = result.nome ? `, ${result.nome}` : "";
      exibirMensagem(`Bem-vindo${nomeUsuario}! Redirecionando...`, "success");

      const redirect = sessionStorage.getItem("redirectAfterLogin");

      if (redirect) {
        sessionStorage.removeItem("redirectAfterLogin");
        window.location.href = redirect;
        return;
      }

      // 2. AJUSTE DE ROTA: Redireciona para os arquivos .php com proteção de sessão
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
