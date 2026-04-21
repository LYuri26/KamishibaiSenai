document
  .getElementById("formCadastro")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const nome = document.getElementById("nome").value.trim();
    const sobrenome = document.getElementById("sobrenome").value.trim();
    let email = document.getElementById("email").value.trim();
    const cargo = document.getElementById("cargo").value;
    const senha = document.getElementById("senha").value;
    const confirmarSenha = document.getElementById("confirmarSenha").value;

    // Converter e-mail para minúsculas antes de enviar
    email = email.toLowerCase();

    if (senha !== confirmarSenha) {
      exibirMensagem("As senhas não coincidem", "danger");
      return;
    }

    // 🔹 Validação de nível de senha no frontend (opcional, melhora a experiência)
    if (senha.length < 6 || !/[A-Za-z]/.test(senha) || !/[0-9]/.test(senha)) {
      exibirMensagem(
        "A senha deve ter no mínimo 6 caracteres, com letras e números",
        "danger",
      );
      return;
    }

    const dados = { nome, sobrenome, email, cargo, senha };

    try {
      const response = await fetch("api/cadastrar_usuario.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados),
      });
      const result = await response.json();
      if (result.sucesso) {
        exibirMensagem("Cadastro realizado! Redirecionando...", "success");
        setTimeout(() => (window.location.href = "login.html"), 2000);
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
