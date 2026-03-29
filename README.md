# Kamishibai SENAI – Sistema de Inspeção com Análise Preditiva

O **Kamishibai SENAI** é uma plataforma digital que transforma o tradicional método de inspeção _Kamishibai_ (checklist visual) em uma ferramenta inteligente de gestão da qualidade. Automatiza a coleta de dados das inspeções das salas e laboratórios e utiliza um modelo de previsão baseado em **Holt‑Winters multiplicativo** para antecipar tendências, auxiliando líderes e instrutores na tomada de decisões proativas.

---

## 🧠 O que é o Kamishibai?

Kamishibai é uma técnica de gestão visual originária do Sistema Toyota de Produção. Consiste em realizar verificações rápidas e periódicas em pontos‑chave do ambiente de trabalho, registrando conformidades e não conformidades. Tradicionalmente feito com cartões, o sistema digitaliza esse processo e o enriquece com inteligência artificial.

---

## ✨ Funcionalidades principais

- **Cadastro e login de usuários** com controle de perfil (instrutor / líder).
- **Ambientes de inspeção** para três tipos de espaços:
  - Sala de aula 104a (carteiras, TV, ar‑condicionado, tomadas etc.)
  - Laboratório 103d (computadores, periféricos, rede elétrica)
  - Oficina de soldagem 102c (boxes, EPIs, ferramentas, áreas)
- **Registro de inspeções** por período (manhã/tarde) e momento (início/fim).
- **Geração automática de relatórios** consolidados por sala e data.
- \*\*Painel# Kamishibai SENAI – Sistema de Inspeção com Análise Preditiva## 📄 Licença

Este projeto é de código aberto e pode ser utilizado livremente para fins de aprendizado e treinament
administrativo\*\* para visualização e busca de todas as inspeções.

- **Análise Preditiva com IA**: gráficos dinâmicos e previsões usando o modelo **Holt‑Winters multiplicativo**, que decompõe a série temporal em **nível**, **tendência** e **sazonalidade**, além de calcular o erro médio absoluto (MAE) para avaliar a precisão das previsões.
- **Exportação de relatório em PDF** diretamente do navegador.

---

## 🛠 Tecnologias utilizadas

- **Backend:** PHP 7.4+ (PDO MySQL)
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript (ES6)
- **Gráficos:** Chart.js
- **Banco de Dados:** MySQL
- **Servidor:** Apache / Nginx (ambiente local)

---

## 📁 Estrutura do projeto

```
KamishibaiSenai/
├── acesso/               # Páginas de login e cadastro
├── acessorios/           # Header, footer e componentes comuns
├── administrador/        # Área do líder (listagem, relatórios, análise IA)
├── ambiente/             # Páginas de inspeção (104a, 103d, 102c)
├── assets/
│   ├── css/              # Folhas de estilo por área
│   ├── images/           # Imagens (logos, fotos das salas)
│   └── js/               # Scripts organizados por módulo
├── config/
│   ├── database.php      # Conexão com o banco
│   ├── install.php       # Criação automática das tabelas
│   └── gerar_relatorios.php  # Processo de consolidação
└── index.html            # Página inicial pública
```

## 🧪 Como usar

### Para instrutores

- Acesse uma das páginas de ambiente (ex.: `sala104a.html`).
- Preencha o formulário com as informações solicitadas (campos com "sim"/"não").
- Ao final, a inspeção é registrada e um relatório é gerado automaticamente.

### Para líderes

- Faça login com perfil de **líder**.
- No painel principal (`administrador/index.html`) você terá:
  - Alertas de períodos sem inspeção no dia atual.
  - Listagem completa de todas as inspeções, com filtro e detalhamento.
- Acesse **Relatórios Consolidados** para visualizar inspeções por data.
- Explore a **Análise Preditiva (IA)**:
  - Escolha o período (mensal ou anual) e a sala.
  - Veja a evolução histórica da taxa de problemas.
  - A previsão para os próxim

## 🧪 Como usar

### Para instrutores

- Acesse uma das páginas de ambiente (ex.: `sala104a.html`).
- Preencha o formulário com as informações solicitadas (campos com "sim"/"não").
- Ao final, a inspeção é registrada e um relatório é gerado automaticamente.

### Para líderes

- Faça login com perfil de **líder**.
- No painel principal (`administrador/index.html`) você terá:
  - Alertas de períodos sem inspeção no dia atual.
  - Listagem completa de todas as inspeções, com filtro e detalhamento.
- Acesse **Relatórios Consolidados** para visualizar inspeções por data.
- Explore a **Análise Preditiva (IA)**:
  - Escolha o período (mensal ou anual) e a sala.
  - Veja a evolução histórica da taxa de problemas.
  - A previsão para os próximos 3 meses é gerada automaticamente.
  - Entenda os componentes do modelo: **nível**, **tendência** e **sazonalidade**.

---

os 3 meses é gerada automaticamente.

- Entenda os componentes do modelo: **nível**, **tendência** e **sazonalidade**.

---

## 🤖 Análise Preditiva com IA – Modelo Holt‑Winters Multiplicativo

O sistema utiliza um modelo de séries temporais chamado **Holt‑Winters multiplicativo**, especialmente adequado para dados com sazonalidade e tendência. Ele decompõe a série em três componentes interpretáveis:

- **Nível (level)**: valor médio da taxa de problemas, suavizado exponencialmente.
- **Tendência (trend)**: direção e magnitude da mudança ao longo do tempo (aumento, redução ou estabilidade).
- **Sazonalidade (seasonal)**: padrões que se repetem em intervalos fixos (por exemplo, picos em determinados meses do ano).

A partir desses componentes, o modelo projeta os próximos valores e ainda calcula o **Erro Médio Absoluto (MAE)** para indicar a precisão da previsão.

### 📊 Exemplo de saída no painel

```
📊 Nível atual (suavizado): 41.66%
    – Valor médio da taxa de problemas.

📈 Tendência atual: estável (sem tendência significativa)
    – A taxa não mostra aumento nem redução significativa nos últimos meses.
```

Além disso, o gráfico de previsão exibe um intervalo de confiança de ±5% e a série histórica, permitindo ao líder identificar rapidamente se a qualidade está melhorando ou piorando.

### Por que Holt‑Winters?

- **Leve e interpretável** – não requer bibliotecas externas de machine learning.
- **Funciona bem com dados mensais** (12 períodos de sazonalidade).
- **Fornece informações acionáveis** – o líder sabe se deve agir para conter uma tendência de alta ou se os esforços estão gerando melhoria.

---

## 👨‍🏫 Autor

**Lenon Yuri** – Instrutor de Informática
[![GitHub](https://img.shields.io/badge/GitHub-LYuri26-181717?style=flat-square&logo=github)](https://github.com/LYuri26)

---
