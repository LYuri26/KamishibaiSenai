# 📘 Kamishibai – Sistema de Inspeção com Análise Preditiva

## 🎯 Objetivos de Aprendizagem

Ao utilizar este sistema, o usuário será capaz de:

- Realizar inspeções padronizadas em ambientes educacionais
- Registrar conformidades e não conformidades com evidências
- Interpretar relatórios operacionais e indicadores de qualidade
- Analisar tendências com base em dados históricos
- Antecipar problemas utilizando análise preditiva

---

## 🌎 Contexto Real

Em ambientes educacionais e industriais, falhas estruturais e operacionais muitas vezes não são identificadas a tempo, gerando riscos, custos e perda de produtividade.

O método **Kamishibai**, originado no Sistema Toyota de Produção, resolve esse problema por meio de inspeções visuais rápidas e frequentes.

Este sistema digitaliza esse processo e adiciona inteligência analítica, permitindo não apenas registrar falhas, mas **prever problemas antes que aconteçam**.

---

## 🧠 Visão Geral do Sistema

O **Kamishibai** é uma aplicação web que funciona via navegador e pode ser executada em:

- Servidor local (intranet)
- Servidor externo (internet)

O sistema permite:

- Controle de usuários (instrutor e líder)
- Inspeções por ambiente
- Registro com imagens
- Relatórios automáticos
- Análise preditiva com IA

---

## 🏗 Arquitetura do Sistema

A aplicação segue o modelo web tradicional:

- **Frontend**
  - HTML, CSS, JavaScript

- **Backend**
  - PHP (APIs)

- **Banco de Dados**
  - MySQL

Fluxo:

1. Usuário interage com a interface
2. JavaScript envia dados para o PHP
3. PHP processa e salva no MySQL
4. Dados retornam para visualização

---

## 🔐 Controle de Acesso

O sistema possui dois níveis:

### 👨‍🏫 Instrutor

- Acessa ambientes
- Realiza inspeções
- Registra problemas e imagens

### 👨‍💼 Líder (Administrador)

- Acesso completo
- Visualiza relatórios
- Analisa dados históricos
- Utiliza análise preditiva

---

## 📁 Estrutura do Projeto

```
KamishibaiSenai/
│
├── .github/
│
├── acesso/
│   ├── api/
│   │   ├── cadastrar_usuario.php
│   │   ├── dados_usuario.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── verificar_sessao.php
│   │
│   ├── cadastro.html
│   └── login.html
│
├── acessorios/
│   ├── encerramento.html
│   ├── footer.html
│   └── header.html
│
├── administrador/
│   ├── api/
│   │   ├── analise_ia.php
│   │   ├── get_inspecao.php
│   │   ├── listar_inspecoes.php
│   │   ├── listar_relatorios.php
│   │   └── verificar_alertas.php
│   │
│   ├── analise_ia.html
│   ├── index.html
│   ├── relatorios.html
│   └── visualizar.html
│
├── ambiente/
│   ├── api/
│   │   └── salvar_checklist.php
│   │
│   ├── laboratorio103d.html
│   ├── oficina102c.html
│   └── sala104a.html
│
├── assets/
│   ├── css/
│   │   ├── acesso/
│   │   │   ├── cadastro.css
│   │   │   └── login.css
│   │   │
│   │   ├── acessorios/
│   │   │   └── acessorios.css
│   │   │
│   │   ├── administrador/
│   │   │   ├── analise_ia.css
│   │   │   ├── index.css
│   │   │   ├── relatorios.css
│   │   │   └── visualizar.css
│   │   │
│   │   ├── ambiente/
│   │   │   ├── laboratorio103d.css
│   │   │   ├── oficina102c.css
│   │   │   └── sala104a.css
│   │   │
│   │   ├── geral.css
│   │   └── index.css
│   │
│   ├── js/
│   │   ├── acesso/
│   │   │   ├── cadastro.js
│   │   │   ├── dados_usuario.js
│   │   │   ├── login.js
│   │   │   ├── verificar_acesso.js
│   │   │   ├── verificar_sessao_admin.js
│   │   │   └── verificar_sessao_ambiente.js
│   │   │
│   │   ├── acessorios/
│   │   │   └── componentes.js
│   │   │
│   │   ├── administrador/
│   │   │   ├── analise_ia.js
│   │   │   ├── listar.js
│   │   │   ├── relatorios.js
│   │   │   └── visualizar.js
│   │   │
│   │   ├── ambiente/
│   │   │   ├── laboratorio103d.js
│   │   │   ├── oficina102c.js
│   │   │   └── sala104a.js
│   │   │
│   │
│   ├── images/
│   │   ├── ambiente/
│   │   │   ├── 104a/
│   │   │   │   └── 69d8923cb0588.jpg
│   │   │   │
│   │   │   ├── oficina102c/
│   │   │   │   └── 69c71518babd3.png
│   │   │   │
│   │   │   └── sala104a/
│   │   │       └── 69cc1583bf719.png
│   │   │
│   │   ├── 102c.png
│   │   ├── 103d.jpeg
│   │   ├── 104a.jpeg
│   │   └── logo.png
│
├── config/
│   ├── database/
│   │   └── backup.sql
│   │
│   ├── database.php
│   ├── gerar_relatorios.php
│   ├── inserir_dados_teste.php
│   └── install.php
│
├── .gitattributes
├── index.html
└── README.md
```

---

## ⚙️ Requisitos do Sistema

- Servidor Web (Apache ou Nginx)
- PHP 7.4+
- MySQL
- Navegador moderno

---

## 🚀 Instalação

### 1. Clonar o projeto

```bash
git clone https://github.com/SEU-USUARIO/KamishibaiSenai.git
```

---

### 2. Configurar servidor local

Colocar em:

```
htdocs/
```

Iniciar:

- Apache
- MySQL

---

### 3. Banco de dados

- Criar banco: `kamishibai`
- Importar:

```
config/database/backup.sql
```

---

### 4. Configurar conexão

Arquivo:

```
config/database.php
```

---

### 5. Executar sistema

```
http://localhost/KamishibaiSenai/
```

---

## 🌐 Estrutura de Navegação

### Página inicial

```
/index.html
```

- Lista ambientes
- Acesso ao sistema
- Redirecionamento para login

---

### Autenticação

```
/acesso/login.html
/acesso/cadastro.html
```

---

## 🏫 Fluxo de Operação – Rota Kamishibai

### 1. Acesso ao ambiente

Exemplo:

```
/ambiente/sala104a.html
```

---

### 2. Execução da inspeção

O instrutor avalia:

- Estrutura
- Equipamentos
- Condições gerais

Cada item:

- Conforme
- Não conforme

---

### 3. Registro de problemas

Quando "Não conforme":

- Descrição obrigatória
- Foto obrigatória

---

### 4. Controle de turno (automático)

- Início → começo até metade da aula
- Fim → metade até encerramento

---

### 5. Armazenamento

Dados enviados para:

```
ambiente/api/salvar_checklist.php
```

Salvos em:

- Tabela do ambiente
- Tabela de relatórios

Incluindo:

- Dados
- Status
- Caminho da imagem

---

## 📊 Área Administrativa

### Painel

```
/administrador/index.html
```

- Lista de inspeções
- Monitoramento

---

### Visualização detalhada

```
/administrador/visualizar.html
```

- Problemas
- Descrições
- Status

---

### Relatórios

```
/administrador/relatorios.html
```

- Tabela completa
- Imagens com modal

---

## 🤖 Análise Preditiva (IA)

### Acesso

```
/administrador/analise_ia.html
```

---

## 🧠 Como funciona a IA do sistema

Existe um ponto específico do sistema onde toda a inteligência acontece: o arquivo de análise no servidor (`analise_ia.php`).

Ele funciona assim:

1. Analisa os últimos 12 meses de dados
2. Identifica padrões de falhas
3. Calcula tendências
4. Gera previsão do próximo mês

Na prática, o sistema observa o passado e projeta o futuro.

Se as falhas aumentam em certos períodos, ele aprende isso.
Se a situação melhora, ele se ajusta automaticamente.

---

## 📈 Modelo Utilizado: Holt-Winters

O sistema utiliza o modelo **Holt-Winters multiplicativo**, que trabalha com três elementos:

- **Nível** → média atual de falhas
- **Tendência** → crescimento ou redução
- **Sazonalidade** → padrões recorrentes

---

### 📊 O que o sistema gera

- Previsão do próximo mês
- Tendência (alta ou queda)
- Indicadores de qualidade

---

## 🧠 Por que isso é uma IA

O sistema é considerado uma **IA preditiva** porque:

- Aprende com dados históricos
- Se adapta automaticamente
- Atualiza previsões com novos dados
- Auxilia na tomada de decisão

Ele não apenas armazena dados — ele interpreta e projeta cenários futuros.

---

## 🧩 Expansão do Sistema

Para adicionar novos ambientes:

1. Criar página em `/ambiente/`
2. Criar JS em `/assets/js/ambiente/`
3. Usar API existente (`salvar_checklist.php`)
4. Registrar no banco com identificador

---

### ✔ Boa prática

Utilizar banco dinâmico com campo:

```
ambiente
```

---

## ⚠️ Boas Práticas

- password_hash() para senhas
- PDO (prepared statements)
- Validação de entrada
- Sanitização de dados
- Controle de sessão

---

## ❌ Erros Comuns

- Banco mal configurado
- Caminhos incorretos
- Sessão não iniciada
- JSON inválido
- Permissões do servidor

---

## 🌐 Modos de Uso

### Local

```
http://localhost:3000/
```

---

### Produção

```
http://seudominio.com/
```

---

## 🧪 Caso Prático

Situação:

Aumento de falhas em uma sala.

Ação:

1. Ver relatórios
2. Identificar padrão
3. Consultar previsão
4. Agir preventivamente

---

## 📚 Fontes de Consulta

- PHP: [https://www.php.net](https://www.php.net)
- MySQL: [https://dev.mysql.com/doc/](https://dev.mysql.com/doc/)
- Chart.js: [https://www.chartjs.org](https://www.chartjs.org)
- Bootstrap: [https://getbootstrap.com](https://getbootstrap.com)
- Forecasting: Hyndman

---

## 👨‍🏫 Autor

**Lenon Yuri**
Instrutor de Informática

GitHub: [https://github.com/LYuri26](https://github.com/LYuri26)
