# HidroControl

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PHPUnit](https://img.shields.io/badge/PHPUnit-11-366488?style=for-the-badge)
![OpenAPI](https://img.shields.io/badge/OpenAPI-3.0-6BA539?style=for-the-badge&logo=openapiinitiative&logoColor=white)
![Swagger UI](https://img.shields.io/badge/Swagger_UI-85EA2D?style=for-the-badge&logo=swagger&logoColor=black)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white)
![Laragon](https://img.shields.io/badge/Laragon-0E83CD?style=for-the-badge)

Protótipo de **sistema web** em **PHP** para o monitoramento simplificado de uma usina hidrelétrica. O painel mostra ao operador o nível do reservatório, a vazão, a potência, a temperatura e o estado das turbinas, e **classifica cada parâmetro** como normal, em atenção ou crítico, de acordo com limites definidos. O operador também pode ligar e desligar turbinas, e cada ação fica registrada. Por trás do painel, uma **API HTTP que troca dados em JSON**, documentada em OpenAPI, alimenta a tela.

Os dados são **simulados** (três cenários de teste) e as alterações do operador são guardadas em **arquivo JSON**, sem banco de dados e sem sensores reais. O foco do projeto é a **Programação Orientada a Objetos**, a modelagem em UML e a qualidade do código (testes automatizados e documentação da API).

---

## Sumário

- [HidroControl](#hidrocontrol)
  - [Sumário](#sumário)
  - [1. Visão geral](#1-visão-geral)
  - [2. Funcionalidades](#2-funcionalidades)
  - [3. Tecnologias](#3-tecnologias)
  - [4. Instalação](#4-instalação)
  - [5. Como executar a aplicação](#5-como-executar-a-aplicação)
    - [Abrir o painel](#abrir-o-painel)
    - [Credenciais de demonstração](#credenciais-de-demonstração)
    - [Roteiro de uso](#roteiro-de-uso)
    - [Atalhos de terminal](#atalhos-de-terminal)
  - [6. Testes automatizados (PHPUnit)](#6-testes-automatizados-phpunit)
  - [7. Documentação da API (Swagger)](#7-documentação-da-api-swagger)
  - [8. Testando a API pelo terminal (curl)](#8-testando-a-api-pelo-terminal-curl)
  - [9. Estrutura de pastas](#9-estrutura-de-pastas)
  - [10. Arquitetura](#10-arquitetura)
  - [11. Regras de decisão](#11-regras-de-decisão)
  - [12. Cenários de simulação](#12-cenários-de-simulação)
  - [13. Persistência em arquivo JSON](#13-persistência-em-arquivo-json)
  - [14. Segurança e limitações](#14-segurança-e-limitações)
  - [15. Documentação do projeto](#15-documentação-do-projeto)
  - [16. Solução de problemas](#16-solução-de-problemas)
  - [17. Trabalhos futuros](#17-trabalhos-futuros)
  - [18. Autores](#18-autores)
  - [19.Licença](#19licença)

---

## 1. Visão geral

| Item | Descrição |
|---|---|
| Problema | Uma pequena central hidrelétrica precisa de um painel que reúna os parâmetros da operação e avise quando algum estiver fora dos limites |
| Usuário | O **operador** da usina, que precisa ler rápido, inclusive no turno da noite |
| Decisões do sistema | Classificar nível, temperatura e estado das turbinas; gerar alertas; determinar a situação geral (prevalece o pior estado) |
| Dados | Simulados no código (cenários A, B e C), sem sensores reais |
| Persistência | Arquivos JSON em `backend/storage/` |

## 2. Funcionalidades

- **Painel único** com a situação geral da usina (NORMAL, ATENÇÃO ou CRÍTICO), sempre indicada por **cor, forma e texto**.
- **Reservatório** com nível, vazão e um tanque com as marcas dos limites (30% e 90%).
- **Turbinas** com estado, temperatura e barra de temperatura com as marcas dos limites (70 °C e 85 °C).
- **Alertas** com parâmetro, gravidade, mensagem e ação recomendada, ou "Nenhum alerta ativo".
- **Toasts** (avisos na tela) para os alertas novos: o crítico fica até ser fechado, o de atenção some sozinho.
- **Cenários de simulação** A (normal), B (atenção) e C (crítico).
- **Ligar e desligar turbinas**, com o erro 409 quando a turbina já está no estado pedido.
- **Histórico de ações** ("Últimos eventos"): quem fez o quê, em qual turbina e quando.
- **Restaurar a simulação**, que desfaz as alterações e reinicia a demonstração.
- **Login** com sessão (usuários fixos para demonstração).
- **API documentada** em OpenAPI, com Swagger UI interativo.
- **Testes automatizados** com PHPUnit, incluindo os valores de fronteira das regras.

## 3. Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | ![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white)<br>PHP 8.2 ou superior (testado no PHP 8.3), Programação Orientada a Objetos, sem framework |
| Frontend | ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white) ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-ES%20Modules-F7DF1E?style=flat&logo=javascript&logoColor=black)<br>HTML, CSS e JavaScript puros (módulos ES), sem biblioteca |
| Testes | ![PHPUnit](https://img.shields.io/badge/PHPUnit-11-366488?style=flat&logo=php&logoColor=white)<br>PHPUnit 11 (arquivo `phpunit.phar`, sem Composer) |
| Documentação da API | ![OpenAPI](https://img.shields.io/badge/OpenAPI-3.0-6BA539?style=flat&logo=openapiinitiative&logoColor=white) ![Swagger](https://img.shields.io/badge/Swagger%20UI-85EA2D?style=flat&logo=swagger&logoColor=black)<br>OpenAPI 3.0 e Swagger UI |
| Ambiente | ![Laragon](https://img.shields.io/badge/Laragon-0E83CD?style=flat) ![Apache](https://img.shields.io/badge/Apache-D22128?style=flat&logo=apache&logoColor=white)<br>Laragon (Apache + PHP) |
| Diagramas | ![UML](https://img.shields.io/badge/UML-diagramas-0b3a4f?style=flat)<br>UML (caso de uso, classes, sequência) e fluxograma de processo |

## 4. Instalação

**Pré-requisitos**

- [Laragon](https://laragon.org/) com Apache e PHP 8.2 ou superior.
- Um navegador atual (Chrome, Edge ou Firefox).
- Acesso à internet **só** para baixar o PHPUnit e para abrir a página do Swagger (veja a [seção 7](#7-documentação-da-api-swagger)).

**Passo a passo**

1. Coloque a pasta do projeto em `C:\laragon\www\hidrocontrol`.
2. Abra o Laragon e clique em **Iniciar tudo** (o Apache precisa estar ligado).
3. Abra o terminal do Laragon (botão **Terminal**), vá até a pasta do projeto e confira o PHP:
   ```
   cd C:\laragon\www\hidrocontrol
   php -v
   ```
4. Baixe o PHPUnit (arquivo único, sem Composer):
   ```
   curl -L -o phpunit.phar https://phar.phpunit.de/phpunit-11.phar
   php phpunit.phar --version
   ```
   O esperado é `PHPUnit 11.x`. O PHPUnit 11 exige PHP 8.2 ou superior.
5. Confirme que a API responde (abre no navegador padrão):
   ```
   start http://localhost/hidrocontrol/backend/index.php?rota=status
   ```
   Deve aparecer `{"sistema":"HidroControl","status":"ok"}`.

> A pasta `backend/storage/` precisa aceitar escrita. No Laragon isso já vale por padrão.

## 5. Como executar a aplicação

### Abrir o painel

Com o Apache ligado, no terminal do Laragon:

```
start http://localhost/hidrocontrol/frontend/login.html
```

Ou digite o endereço no navegador. O painel (`frontend/index.html`) só abre com login: sem sessão, ele redireciona para a tela de entrada.

> O frontend usa módulos JavaScript, então precisa ser aberto por `http://localhost/...`. Abrir o arquivo direto (`file://`) não funciona.

### Credenciais de demonstração

| Operador | E-mail | Senha |
|---|---|---|
| João Silva | `joao@hidrocontrol.test` | `senha123` |
| Maria Souza | `maria@hidrocontrol.test` | `senha123` |

Os usuários são fixos no código e todos os dados são fictícios.

### Roteiro de uso

1. Entre com um dos operadores.
2. No painel, escolha o **cenário** (A, B ou C) e veja a situação geral, os alertas e os toasts mudarem.
3. No cenário A, clique em **Desligar** na Turbina 02: a situação vira ATENÇÃO, surge um alerta e o evento aparece em "Últimos eventos".
4. Clique em **Ligar** para voltar ao normal.
5. Clique em **Restaurar simulação** para recomeçar a demonstração.
6. Clique em **Sair** para encerrar a sessão.

### Atalhos de terminal

| Objetivo | Comando |
|---|---|
| Abrir o painel | `start http://localhost/hidrocontrol/frontend/login.html` |
| Abrir a documentação da API | `start http://localhost/hidrocontrol/docs/docs.html` |
| Conferir se a API está no ar | `start http://localhost/hidrocontrol/backend/index.php?rota=status` |
| Rodar os testes | `php phpunit.phar --testdox` |

> **Alternativa sem Apache (opcional, não testada neste projeto):** na pasta do projeto, `php -S localhost:8000` e abrir `http://localhost:8000/frontend/login.html`. O Swagger UI aponta para `/hidrocontrol/backend`, então o "Try it out" só funciona na instalação do Laragon.

## 6. Testes automatizados (PHPUnit)

A suíte cobre o domínio, os repositories, os services e a autenticação, com **158 testes**. As regras de decisão são testadas nos **valores de fronteira** (29.9, 30, 90, 90.1, 69.9, 70, 85 e 85.1).

Rode sempre na **pasta raiz do projeto**, onde estão o `phpunit.xml` e o `phpunit.phar`.

| Objetivo | Comando |
|---|---|
| Rodar todos os testes | `php phpunit.phar` |
| Mostrar cada teste com ✔ ou ✘ (recomendado) | `php phpunit.phar --testdox` |
| Rodar uma classe de testes | `php phpunit.phar backend/tests/UsinaTest.php` |
| Rodar testes pelo nome | `php phpunit.phar --filter testVerificarNivel` |
| Rodar só os de uma classe pelo nome | `php phpunit.phar --filter UsinaServiceTest` |
| Parar no primeiro erro | `php phpunit.phar --stop-on-failure` |
| Listar os testes sem executar | `php phpunit.phar --list-tests` |
| Ver a versão do PHPUnit | `php phpunit.phar --version` |

**Resultado esperado**

```
PHPUnit 11.x by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.x
Configuration: ...\hidrocontrol\phpunit.xml

...............................................................  63 / 158 ( 39%)
...                                                             158 / 158 (100%)

OK (158 tests, ... assertions)
```

**Observações**

- Os testes **nunca tocam** em `backend/storage/`: usam arquivos temporários, apagados ao final.
- O PHP do terminal pode ser diferente do PHP do Apache (por exemplo, 8.2 no VS Code e 8.3 no Laragon). Rode também no terminal do Laragon, que usa o mesmo PHP do servidor.
- O frontend (JavaScript) não tem testes automatizados: ele é conferido pelo [roteiro manual](docs/testes.md#9-conferência-manual).
- A tabela completa dos valores de fronteira está em [`docs/testes.md`](docs/testes.md).

## 7. Documentação da API (Swagger)

A API é documentada em **OpenAPI 3.0** (`docs/openapi.yaml`) e exibida com o **Swagger UI** (`docs/docs.html`), que permite **executar** as chamadas pelo navegador ("Try it out").

**Abrir**

```
start http://localhost/hidrocontrol/docs/docs.html
```

**Como usar**

1. Abra a operação **entrar** (grupo "Autenticação"), clique em **Try it out** e depois em **Execute**. Ela já vem preenchida com o João.
2. O navegador guarda o cookie da sessão, e as demais operações passam a funcionar. Não é preciso usar o botão "Authorize".
3. Teste **leitura** (cenário A, B ou C), **desligar** e **ligar** turbina (informe o `codigo`, por exemplo `T-02`), **registros** e **restaurar**.
4. Uma chamada sem login mostra o erro **401**.

**Operações documentadas**

| Método | Rota | Descrição | Login |
|---|---|---|---|
| GET | `?rota=status` | Verifica se a API está no ar | Não |
| POST | `?rota=auth&acao=entrar` | Entra no sistema (e-mail e senha) | Não |
| POST | `?rota=auth&acao=sair` | Encerra a sessão | Não |
| GET | `?rota=auth&acao=eu` | Informa quem está logado | Não (401 sem sessão) |
| GET | `?rota=leitura&cenario=A` | Lê a usina no cenário A, B ou C | Sim |
| GET | `?rota=registros` | Últimas 10 ações dos operadores | Sim |
| POST | `?rota=turbina&acao=ligar&codigo=T-02&cenario=A` | Liga uma turbina | Sim |
| POST | `?rota=turbina&acao=desligar&codigo=T-02&cenario=A` | Desliga uma turbina | Sim |
| POST | `?rota=restaurar` | Restaura a simulação | Sim |

**Códigos de resposta**

| Código | Quando |
|---|---|
| 200 | Sucesso |
| 400 | Cenário ou ação inválidos |
| 401 | Sem login, ou e-mail e senha inválidos |
| 404 | Turbina inexistente ou rota desconhecida |
| 405 | A rota exige o método POST |
| 409 | A turbina já está no estado pedido (nada é alterado nem registrado) |
| 500 | Erro interno (resposta genérica; o detalhe vai para o log) |

**Sem internet?** A página carrega o Swagger UI (versão 5.17.14) do CDN jsDelivr. Se a rede do local da apresentação for instável, baixe `swagger-ui.css` e `swagger-ui-bundle.js` desse pacote, coloque em `docs/` e troque as duas URLs do `docs.html` por caminhos locais.

**Postman (opcional):** em *Import*, selecione `docs/openapi.yaml` e a coleção é montada sozinha.

## 8. Testando a API pelo terminal (curl)

As rotas de ligar, desligar e restaurar exigem **POST**, então use o `curl`. O `-c` e o `-b` guardam e reenviam o cookie da sessão (o arquivo `cookies.txt` está no `.gitignore`).

**Sem login, a leitura é barrada (401)**
```
curl -i "http://localhost/hidrocontrol/backend/index.php?rota=leitura&cenario=A"
```

**Entrar e guardar o cookie**
```
curl -i -c cookies.txt -X POST "http://localhost/hidrocontrol/backend/index.php?rota=auth&acao=entrar" -d "email=joao@hidrocontrol.test&senha=senha123"
```

**Quem sou eu, e leitura com a sessão**
```
curl -b cookies.txt "http://localhost/hidrocontrol/backend/index.php?rota=auth&acao=eu"
curl -b cookies.txt "http://localhost/hidrocontrol/backend/index.php?rota=leitura&cenario=B"
```

**Desligar e ligar uma turbina**
```
curl -b cookies.txt -X POST "http://localhost/hidrocontrol/backend/index.php?rota=turbina&acao=desligar&codigo=T-02&cenario=A"
curl -b cookies.txt -X POST "http://localhost/hidrocontrol/backend/index.php?rota=turbina&acao=ligar&codigo=T-02&cenario=A"
```

**Histórico de ações**
```
curl -b cookies.txt "http://localhost/hidrocontrol/backend/index.php?rota=registros"
```

**Restaurar a simulação**
```
curl -b cookies.txt -X POST "http://localhost/hidrocontrol/backend/index.php?rota=restaurar"
```

**Sair**
```
curl -b cookies.txt -c cookies.txt -X POST "http://localhost/hidrocontrol/backend/index.php?rota=auth&acao=sair"
```

**Respostas de erro esperadas**

| Chamada | Resposta |
|---|---|
| Desligar uma turbina já desligada | 409 |
| `codigo=T-99` | 404 |
| `cenario=Z` | 400 |
| `acao=explodir` | 400 |
| Rota de turbina com GET (sem `-X POST`) | 405 |
| Qualquer rota protegida sem sessão | 401 |

Para ver só o código HTTP: `curl -s -o NUL -w "%{http_code}" -X POST "URL"`. No PowerShell, use `curl.exe` (o nome `curl` é outro comando).

## 9. Estrutura de pastas

```
hidrocontrol/
├── backend/
│   ├── domain/                  # classes do negócio e regras de decisão
│   │   ├── Usuario.php          # abstrata
│   │   ├── Operador.php
│   │   ├── Usina.php
│   │   ├── Reservatorio.php
│   │   ├── Turbina.php
│   │   ├── Alerta.php
│   │   └── RegistroAcao.php
│   ├── repository/              # fontes de dados (único lugar que conhece a origem)
│   │   ├── UsinaRepositoryInterface.php
│   │   ├── UsinaRepositorySimulado.php
│   │   ├── UsuarioRepositoryInterface.php
│   │   ├── UsuarioRepositorySimulado.php
│   │   ├── EstadoRepositoryInterface.php
│   │   ├── EstadoRepositoryJson.php
│   │   ├── RegistroRepositoryInterface.php
│   │   ├── RegistroRepositoryJson.php
│   │   └── ArmazenamentoJson.php
│   ├── services/                # orquestram o domínio
│   │   ├── UsinaService.php
│   │   ├── AuthService.php
│   │   └── RegistroService.php
│   ├── controller/              # recebem a requisição e devolvem JSON
│   │   ├── LeituraController.php
│   │   ├── TurbinaController.php
│   │   └── AuthController.php
│   ├── routes/api.php           # mapeia ?rota=... para o controller
│   ├── startup/
│   │   ├── autoload.php         # carrega as classes (usado também pelos testes)
│   │   └── bootstrap.php        # fuso, erros, header JSON e sessão
│   ├── storage/                 # estado gerado em execução (ignorado pelo Git)
│   ├── tests/                   # testes PHPUnit
│   └── index.php                # ponto de entrada da API
├── frontend/
│   ├── login.html
│   ├── index.html               # painel
│   ├── style.css
│   └── js/
│       ├── api.js               # único lugar com fetch()
│       ├── ui.js                # único lugar que mexe no DOM
│       ├── alertas.js           # decide quando mostrar um toast
│       ├── painel.js            # orquestra o painel
│       └── login.js
├── docs/
│   ├── openapi.yaml             # documentação da API (OpenAPI 3.0)
│   ├── docs.html                # Swagger UI
│   ├── rastreabilidade.md       # requisito → caso de uso → código → teste
│   ├── testes.md                # tabela de testes e roteiro manual
│   └── diagramas/               # diagramas UML e fluxograma
├── phpunit.xml
├── phpunit.phar                 # baixado na instalação
└── README.md
```

## 10. Arquitetura

A aplicação segue uma **arquitetura em camadas**. As chamadas só descem, e o domínio não depende de ninguém.

```
frontend  →  controller  →  services  →  repository  →  storage (JSON)
                                │
                                └──────→  domain
```

| Camada | Responsabilidade | Regra de ouro |
|---|---|---|
| **frontend** | Exibir a informação e receber os comandos do operador | Só conversa com a API |
| **controller** | Receber a requisição, devolver JSON e traduzir erros em códigos HTTP | Sem regra de negócio |
| **services** | Orquestrar o domínio e montar a resposta | Não sabe de onde vêm os dados |
| **repository** | Entregar e guardar dados (cenários simulados, usuários fixos, arquivos JSON) | Único lugar que conhece a fonte |
| **domain** | Dados e regras de decisão (limites 30, 90, 70 e 85) | Não conhece API, sessão, arquivo nem HTTP |

**Decisões de projeto**

- **Interfaces de repository:** trocar a fonte de dados (por exemplo, um banco ou sensores reais) exige apenas uma nova implementação da interface, **sem alterar** services, controllers nem regras.
- **Delegação:** a `Usina` não repete as regras. Ela pergunta ao `Reservatorio` e às `Turbina`s, e a regra fica junto do dado que avalia.
- **Retorno em vez de `echo`:** os métodos de verificação devolvem o texto, e quem chama decide como exibir.
- **Limites em um lugar só:** o domínio os envia na leitura (`limites`), e o painel desenha as marcas das escalas sem repetir os números.
- **Registro guarda o nome do operador** (texto), e não o objeto, para o histórico não depender de o usuário continuar existindo nem expor dados pessoais.

**Classes do domínio**

| Classe | Papel |
|---|---|
| `Usina` | Reúne reservatório e turbinas, gera os alertas e consolida a situação geral |
| `Reservatorio` | Nível, vazão e classificação do nível |
| `Turbina` | Estado, temperatura, ligar e desligar, classificação |
| `Alerta` | Aviso emitido ao operador (imutável) |
| `Usuario` (abstrata) e `Operador` | Pessoa cadastrada; o operador liga e desliga turbinas |
| `RegistroAcao` | Registro imutável de uma ação do operador |

Os diagramas (caso de uso, classes, sequência e fluxograma) estão em [`docs/diagramas/`](docs/diagramas/).

## 11. Regras de decisão

**Nível do reservatório** (`Reservatorio::verificarNivel`)

| Condição | Resultado |
|---|---|
| nível < 30 | ALERTA: nível baixo |
| 30 ≤ nível ≤ 90 | NORMAL |
| nível > 90 | ALERTA: nível muito alto |

**Temperatura da turbina** (`Turbina::verificarTemperatura`)

| Condição | Resultado |
|---|---|
| temperatura < 70 | NORMAL |
| 70 ≤ temperatura ≤ 85 | ATENÇÃO |
| temperatura > 85 | CRÍTICO |

**Estado da turbina** (`Turbina::verificarEstado`)

| Condição | Resultado |
|---|---|
| ligada | EM OPERAÇÃO |
| desligada | DESLIGADA |

**Situação geral da usina** (`Usina::verificarSituacaoGeral`): prevalece o pior estado encontrado.

| Condição | Resultado |
|---|---|
| Existe alerta CRÍTICO (temperatura acima de 85 °C) | CRÍTICO |
| Existe ao menos um alerta (nível fora da faixa, temperatura em ATENÇÃO ou turbina desligada) | ATENÇÃO |
| Nenhum alerta | NORMAL |

**Alertas** (`Usina::gerarAlertas`): no máximo um por parâmetro (nível, temperatura e turbinas), portanto de 0 a 3. A temperatura considerada é a da turbina **mais quente**, e "turbina desligada" gera um só alerta, mesmo com várias desligadas.

> Os limites e as ações recomendadas são **didáticos** e não substituem procedimentos operacionais reais de uma usina.

## 12. Cenários de simulação

| Cenário | Nível | Temperaturas (T-01, T-02, T-03) | Turbinas | Alertas | Situação geral |
|---|---|---|---|---|---|
| **A** | 70% | 65, 62 e 60 °C | Todas ligadas | 0 | NORMAL |
| **B** | 25% | 78, 70 e 65 °C | Todas ligadas | 2 (nível e temperatura) | ATENÇÃO |
| **C** | 95% | 92, 70 e 65 °C | T-02 desligada | 3 (nível, temperatura e turbina) | CRÍTICO |

Cada cenário guarda as suas próprias alterações de turbinas. Desligar a T-02 no cenário A não afeta o B nem o C.

## 13. Persistência em arquivo JSON

Não há banco de dados. O que o operador faz é guardado em dois arquivos, criados automaticamente na primeira ação:

| Arquivo | Conteúdo |
|---|---|
| `backend/storage/estado.json` | Turbinas ligadas ou desligadas pelo operador, separadas por cenário |
| `backend/storage/registros.json` | Histórico de ações (os últimos 200 registros) |

- Os dois estão no `.gitignore`.
- A leitura é tolerante: arquivo ausente, vazio ou corrompido vira lista vazia, sem erro na tela.
- **Restaurar simulação** apaga as alterações das turbinas (de todos os cenários), mas **não** apaga o histórico.
- Para zerar tudo, apague os dois arquivos.
- O estado e o histórico são **compartilhados** por todos os operadores (não são por usuário).

## 14. Segurança e limitações

**O que existe**

- Senhas guardadas como **hash** (`password_hash`) e conferidas por `password_verify`. O hash não tem getter e nunca sai da classe `Usuario`.
- O construtor recusa uma senha em texto puro.
- A resposta de login é a mesma para e-mail inexistente e senha errada.
- Sessão com `session_regenerate_id` no login e no logout, cookie `HttpOnly` e `SameSite=Lax`.
- A API só expõe do operador o **nome e o setor** (nunca CPF, e-mail, telefone ou hash).
- Todo texto vindo da API é inserido na tela com `textContent` (nunca `innerHTML`).
- Erros não tratados viram uma resposta JSON genérica; o detalhe vai para o log, e nenhum erro PHP aparece na tela.
- O JSON em arquivo é gravado com bloqueio (`LOCK_EX`).

**Limitações assumidas (projeto didático)**

- Os usuários são **fixos no código**: não há cadastro, recuperação de senha nem perfis de permissão.
- Não há bloqueio por tentativas excessivas de login nem token CSRF (só o `SameSite`).
- O estado e o histórico são **compartilhados** e guardados em arquivo, sem controle de concorrência além do bloqueio de escrita.
- A potência gerada é o valor do cenário e **não é recalculada** quando uma turbina é ligada ou desligada.
- Os dados são simulados: não há sensores, atualização automática nem alertas reais.
- Os avisos **não usam som**: o toast, a cor, a forma e o texto bastam e evitam depender do áudio do equipamento.
- O frontend usa recursos modernos de CSS e JavaScript (módulos ES, `color-mix`), então precisa de um navegador atual.

## 15. Documentação do projeto

| Documento | Conteúdo |
|---|---|
| [`docs/openapi.yaml`](docs/openapi.yaml) e [Swagger](#7-documentação-da-api-swagger) | Descrição completa da API |
| [`docs/rastreabilidade.md`](docs/rastreabilidade.md) | Matriz requisito → caso de uso → classe e método → teste |
| [`docs/testes.md`](docs/testes.md) | Tabelas dos valores de fronteira, validações e roteiro de conferência manual |
| [`docs/diagramas/`](docs/diagramas/) | Diagramas de caso de uso, classes, sequência e fluxograma |
| Relatório técnico | Situação-problema, abstração de dados, requisitos, diagramas e reflexão final |

## 16. Solução de problemas

| Sintoma | Causa provável e solução |
|---|---|
| `php` não é reconhecido no terminal | Use o **Terminal do Laragon** (ele já configura o PATH) ou chame o PHP pelo caminho completo, em `C:\laragon\bin\php\` |
| O terminal do VS Code usa outro PHP | Confira com `php -v`. Rode os testes também no terminal do Laragon, que usa o mesmo PHP do Apache |
| `Could not resolve host: curl` | Dois comandos foram colados juntos. Cole um por vez |
| Os testes não são encontrados ("No tests executed") | Rode na pasta raiz (onde está o `phpunit.xml`). Os arquivos de teste terminam em `Test.php` |
| O PHPUnit reclama de extensão faltando | Rode `php -m` e ative a extensão pelo menu do Laragon (PHP → Extensões). O PHPUnit usa `dom`, `mbstring`, `xml`, entre outras |
| O painel redireciona sempre para o login | A sessão expirou ou não foi criada. Entre de novo. Use `http://localhost/...`, e não `file://` |
| A tela fica em branco ou os botões não respondem | Abra o console do navegador (F12). Os módulos JavaScript exigem `http://`. Confira o Apache |
| Resposta `{"erro":"Erro interno do servidor."}` | O detalhe foi para o log de erros do PHP (veja a diretiva `error_log` no `php.ini` do Laragon) |
| O estado das turbinas "ficou preso" | Clique em **Restaurar simulação** ou apague `backend/storage/estado.json` |
| O Swagger fica em branco | A página precisa de internet para carregar o Swagger UI. Veja a [seção 7](#7-documentação-da-api-swagger) |
| O "Try it out" devolve 401 | Execute **entrar** primeiro, no mesmo navegador |
| `curl` no PowerShell se comporta de forma estranha | Use `curl.exe` |

## 17. Trabalhos futuros

- Integrar **sensores reais** no ponto onde hoje os dados do cenário são montados (basta uma nova implementação de `UsinaRepositoryInterface`).
- Trocar o arquivo JSON por um **banco de dados**, também por meio das interfaces de repository.
- **Relatório** da leitura em PDF ou CSV.
- **Atualização automática** do painel e avisos sonoros opcionais.
- **Cadastro de usuários**, perfis de permissão, bloqueio por tentativas e token CSRF.
- Recalcular a potência gerada conforme as turbinas em operação.
- Ampliar o sistema para **múltiplas usinas**.
- Rodar os testes automaticamente a cada commit (GitHub Actions).

## 18. Autores

**Manuella Maia Lopes**


## 19.Licença

![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.