# Matriz de rastreabilidade

Esta matriz liga cada requisito ao **caso de uso** que o cobre, ao **código** que o implementa e ao **teste** que o verifica. Ela mostra que nada do relatório ficou só no papel e que o código não tem funcionalidade sem requisito.

Os nomes de classes, métodos e testes são os reais do projeto (conferidos na saída de `php phpunit.phar --testdox`).

## Sumário

1. [Como ler](#1-como-ler)
2. [Requisitos funcionais](#2-requisitos-funcionais)
3. [Requisitos não funcionais](#3-requisitos-não-funcionais)
4. [Casos de uso e requisitos](#4-casos-de-uso-e-requisitos)
5. [Resumo da cobertura](#5-resumo-da-cobertura)
6. [Observações e ajustes em relação ao relatório](#6-observações-e-ajustes-em-relação-ao-relatório)

---

## 1. Como ler

| Coluna | Significado |
|---|---|
| Caso de uso | O oval do diagrama de caso de uso que cobre o requisito |
| Código | A classe e o método (ou o arquivo) que implementam |
| Teste automatizado | A classe e o teste do PHPUnit que verificam |
| Verificação manual | O que se confere no painel, no Swagger ou no terminal, quando não há teste automatizado |

O frontend (JavaScript) não tem testes automatizados. A parte de **regra e dados** de cada requisito é testada no PHP, e a parte de **tela** é conferida pelo [roteiro manual](testes.md#9-conferência-manual).

## 2. Requisitos funcionais

Os requisitos RF01 a RF12 são os do relatório técnico. Os RF13 a RF18 são os acrescentados nesta versão.

### Informação ao operador

| Requisito | Caso de uso | Código | Teste automatizado | Verificação manual |
|---|---|---|---|---|
| **RF01** Exibir o nome da usina e o operador responsável | Consultar painel | `Usina::getNome`, `Usina::getOperadorResponsavel`, `UsinaService::montarLeitura` (só nome e setor do operador), `ui.js` (`mostrarSituacao`) | `UsinaRepositorySimuladoTest::testDadosGeraisDaUsina`, `testOperadorInformadoEOResponsavelPelaUsina`; `UsinaServiceTest::testLeituraNaoExpoeDadosPessoaisDoOperador` | Faixa da situação geral no painel mostra a usina e o operador |
| **RF02** Exibir o nível do reservatório em % | Consultar painel | `Reservatorio::getNivel`, `UsinaService::montarLeitura`, `ui.js` (`mostrarReservatorio`) | `ReservatorioTest::testGettersDevolvemOsValoresInformados`; `UsinaServiceTest::testLeituraDoCenarioCTemOsDadosEsperados` | Tanque e número do nível no painel |
| **RF03** Exibir os parâmetros (vazão, potência, temperatura) com as unidades | Consultar painel | `Reservatorio::getVazao`, `Usina::getPotenciaGerada`, `Turbina::getTemperatura`, `ui.js` | `UsinaRepositorySimuladoTest::testDadosGeraisDaUsina` (potência e vazão); `UsinaServiceTest::testLeituraDoCenarioCTemOsDadosEsperados` | Painel mostra MW, m³/s e °C |
| **RF04** Exibir a temperatura de cada turbina e a maior | Consultar painel | `Turbina::getTemperatura`, `Usina::temperaturaMaxima` | `UsinaTest::testTemperaturaMaximaEntreAsTurbinas`; `UsinaServiceTest::testLeituraDoCenarioCTemOsDadosEsperados` (máxima 92) | Barra de temperatura em cada turbina |
| **RF05** Exibir a quantidade de turbinas e a lista com nome e estado | Consultar painel | `Usina::quantidadeTurbinas`, `Usina::getTurbinas`, `Turbina::verificarEstado` | `UsinaTest::testQuantidadeDeTurbinas`; `TurbinaTest::testVerificarEstadoLigada`, `testVerificarEstadoDesligada`; `UsinaServiceTest::testLeituraDoCenarioCTemOsDadosEsperados` | Lista de turbinas no painel |

### Decisões automáticas

| Requisito | Caso de uso | Código | Teste automatizado | Verificação manual |
|---|---|---|---|---|
| **RF06** Classificar o nível (30 e 90) | Classificar parâmetros | `Reservatorio::verificarNivel`, `Usina::verificarNivel` | `ReservatorioTest::testVerificarNivel` (0, 29.9, 30, 60, 90, 90.1, 100); `UsinaTest::testVerificarNivelDelegaAoReservatorio` | Cenário B (nível baixo) e C (nível muito alto) |
| **RF07** Classificar a temperatura (70 e 85) | Classificar parâmetros | `Turbina::verificarTemperatura`, `Usina::verificarTemperatura` (turbina mais quente) | `TurbinaTest::testVerificarTemperatura` (20, 69.9, 70, 78, 85, 85.1, 120); `UsinaTest::testVerificarTemperaturaUsaATurbinaMaisQuente` | Cenário B (ATENÇÃO) e C (CRÍTICO) |
| **RF08** Classificar o estado da turbina | Classificar parâmetros | `Turbina::verificarEstado`, `Usina::verificarTurbina` | `TurbinaTest::testVerificarEstadoLigada`, `testVerificarEstadoDesligada`; `UsinaTest::testVerificarTurbinaComTodasLigadas`, `testVerificarTurbinaComAoMenosUmaDesligada` | Cenário C (T-02 desligada) |
| **RF09** Gerar um alerta por parâmetro fora do normal | Gerar alertas | `Usina::gerarAlertas`, `Alerta` (parâmetro, gravidade, mensagem e ação) | `UsinaTest::testAlertaDeNivelBaixo`, `testAlertaDeNivelMuitoAlto`, `testAlertaDeTemperaturaEmAtencao`, `testAlertaDeTemperaturaCritica`, `testAlertaDeUmaTurbinaDesligada`, `testAlertaDeVariasTurbinasDesligadasContinuaSendoUmSo`, `testCenarioCGeraOsTresAlertasNaOrdemEComAGravidadeCerta`; `AlertaTest` (todos) | Cenário C mostra 3 alertas |
| **RF10** Determinar a situação geral (pior estado) | Gerar alertas | `Usina::verificarSituacaoGeral` | `UsinaTest::testQuantidadeDeAlertasESituacaoGeral` (cenários A, B e C e as fronteiras); `UsinaRepositorySimuladoTest::testCenarioProduzOResultadoEsperado` | Faixa da situação geral muda de cor e forma |

### Informação ao operador (tela)

| Requisito | Caso de uso | Código | Teste automatizado | Verificação manual |
|---|---|---|---|---|
| **RF11** Exibir a situação geral e a lista de alertas, ou "Nenhum alerta ativo" | Visualizar alertas | `Alerta::texto`, `UsinaService::montarLeitura`, `ui.js` (`mostrarAlertas`) | `UsinaServiceTest::testCenarioASemAlertasVirandoJsonComListaVazia`; `AlertaTest::testTextoMontaAFraseParaOOperador` | Cenário A mostra "Nenhum alerta ativo" |
| **RF12** Exibir a classificação ao lado de cada parâmetro | Consultar painel | `UsinaService::montarLeitura` (campos `situacao`), `ui.js` | `UsinaServiceTest::testLeituraDoCenarioCTemOsDadosEsperados` (situação do reservatório e das turbinas) | Selo de estado e situação da temperatura em cada linha |

### Requisitos acrescentados

| Requisito | Caso de uso | Código | Teste automatizado | Verificação manual |
|---|---|---|---|---|
| **RF13** Login e logout do operador | Realizar login, Validar credenciais, Sair | `AuthService::entrar`, `sair`, `operadorLogado`; `Usuario::verificarSenha`; `AuthController`; rotas protegidas com 401 | `AuthServiceTest` (7 testes: senha certa, senha errada, e-mail inexistente, maiúsculas, sair, sair sem login, sessão adulterada); `UsuarioTest::testVerificarSenhaCorreta`, `testVerificarSenhaErrada`, `testVerificarSenhaVazia`, `testRejeitaSenhaEmTextoPuro` | Tela de login; `curl` sem sessão devolve 401; Swagger (`entrar`, `sair`, `eu`) |
| **RF14** Selecionar o cenário de simulação | Selecionar cenário | `UsinaRepositorySimulado::obterUsina`, `LeituraController::obter` | `UsinaRepositorySimuladoTest::testCenarioProduzOResultadoEsperado`, `testAceitaCenarioEmMinusculasEComEspacos`, `testRejeitaCenarioInvalido` | Botões A, B e C no painel; `cenario=Z` devolve 400 |
| **RF15** Ligar e desligar turbinas | Ligar turbina, Desligar turbina | `Operador::ligarTurbina`, `desligarTurbina`; `Turbina::ligar`, `desligar`; `Usina::buscarTurbina`; `UsinaService::alterarEstadoTurbina`; `TurbinaController` | `TurbinaTest` (ligar, desligar e os dois erros); `OperadorTest` (7 testes); `UsinaTest::testDesligarTurbinaPeloOperadorPassaAGerarAlertaDeTurbina`, `testBuscarTurbinaInexistenteLancaErro`; `UsinaServiceTest::testDesligarTurbinaAtualizaALeituraERegistraAAcao`, `testAlteracaoPersisteEntreRequisicoesSoNoCenarioAlterado`, `testLigarTurbinaDesligadaDoCenarioCRemoveOAlertaDeTurbina`, `testDesligarTurbinaJaDesligadaNaoGravaEstadoNemRegistro`, `testTurbinaInexistenteLancaErro` | Botões Ligar e Desligar; erro 409 (turbina já no estado pedido) e 404 (turbina inexistente) |
| **RF16** Registrar e consultar as ações do operador | Registrar ação do operador | `RegistroAcao`, `RegistroService`, `RegistroRepositoryJson`, `UsinaService::obterRegistros` | `RegistroAcaoTest` (18 testes); `PersistenciaJsonTest::testRegistrosVoltamDoMaisRecenteParaOMaisAntigoRespeitandoOLimite`; `AuthServiceTest` (ENTRAR e SAIR registrados); `UsinaServiceTest::testDesligarTurbinaAtualizaALeituraERegistraAAcao` | Lista "Últimos eventos" no painel; `?rota=registros` |
| **RF17** Restaurar a simulação | Restaurar simulação | `UsinaService::restaurarSimulacao`, `EstadoRepositoryJson::limpar` | `UsinaServiceTest::testRestaurarSimulacaoDesfazAsAlteracoesERegistraAAcao`; `PersistenciaJsonTest::testLimparDescartaAsAlteracoesDeTodosOsCenarios` | Botão "Restaurar simulação" (com confirmação) |
| **RF18** Avisar os alertas novos por toast | Notificar alerta ao operador | `alertas.js` (`sincronizar`, `reiniciar`), `ui.js` (`mostrarToast`) | Sem teste automatizado (JavaScript) | Roteiro manual: toasts no cenário C, sem repetição ao atualizar, crítico fica até fechar, atenção some em cerca de 8 s |

## 3. Requisitos não funcionais

Os RNF01 a RNF14 são os do relatório técnico. Os RNF15 a RNF19 são os acrescentados nesta versão.

| Requisito | Como é atendido | Teste automatizado | Verificação manual |
|---|---|---|---|
| **RNF01** Todas as informações em uma única tela | `frontend/index.html` é um painel único | - | Nenhuma navegação entre páginas para ler os dados |
| **RNF02** Legibilidade (fonte grande, contraste) | `style.css`: texto de 18 px, situação geral em fonte grande, tema escuro de alto contraste | - | Leitura no painel, inclusive à noite |
| **RNF03** Linguagem simples, em português | Textos da interface e mensagens de alerta | - | Revisão dos textos |
| **RNF04** Estados por cor, forma e texto | `style.css` (cor + forma + texto), `ui.js` | - | Cenários A, B e C: círculo verde, triângulo amarelo e losango vermelho, sempre com a palavra |
| **RNF05** Responsividade | Media queries em `style.css` | - | Janela estreita ou modo celular do navegador |
| **RNF06** Limites exatos (30, 90, 70 e 85) | Constantes em `Reservatorio` e `Turbina`; fonte única dos limites | `ReservatorioTest::testVerificarNivel`, `TurbinaTest::testVerificarTemperatura`, `UsinaTest::testQuantidadeDeAlertasESituacaoGeral` (valores de fronteira) | - |
| **RNF07** Nenhum erro PHP na tela | `bootstrap.php` (`display_errors` desligado e tratador de exceções em JSON); controllers traduzem erros em 400, 401, 404, 405 e 409; leitura tolerante do JSON | `PersistenciaJsonTest::testLerArquivoInexistenteDevolveListaVazia`, `testLerArquivoCorrompidoDevolveListaVazia` | Cenário inválido devolve 400 em JSON, e não uma página de erro |
| **RNF08** Orientação a objetos (uma classe por arquivo) | Classes em `backend/domain/` (e demais camadas), uma por arquivo | - | Estrutura de pastas |
| **RNF09** Separação de responsabilidades | Controller, service, repository e domain; regras de decisão só no domínio | `UsinaServiceTest::testLeituraNaoExpoeDadosPessoaisDoOperador` (o service só monta a resposta) | Estrutura de pastas |
| **RNF10** Tipagem declarada | `declare(strict_types=1)`, atributos e parâmetros tipados em todas as classes | Os testes exercitam os tipos (por exemplo, `string` e `float`) | - |
| **RNF11** `return` em vez de `echo` nos métodos de verificação | Todos os `verificar*` devolvem texto | Todos os testes de `verificar*` conferem o valor devolvido | - |
| **RNF12** Nomes descritivos e comentários | Blocos de documentação nas classes e nos pontos principais | - | Revisão do código |
| **RNF13** Ambiente PHP no Laragon | Apache e PHP do Laragon; **o endereço passou a ser** `http://localhost/hidrocontrol/frontend/` | - | Painel abre nesse endereço |
| **RNF14** Compatibilidade (Chrome, Edge e Firefox) | HTML, CSS e JavaScript sem biblioteca. **Usa recursos modernos** (módulos ES e `color-mix`), então exige versões atuais | - | Abrir o painel nos navegadores |
| **RNF15** Persistência em arquivo JSON, sem SGBD | `ArmazenamentoJson`, `EstadoRepositoryJson`, `RegistroRepositoryJson` | `PersistenciaJsonTest` (6 testes) | Arquivos criados em `backend/storage/` |
| **RNF16** Autenticação com sessão | `AuthService`, `session_regenerate_id`, cookie `HttpOnly` e `SameSite=Lax`, hash de senha | `AuthServiceTest`, `UsuarioTest` | `curl` com e sem cookie |
| **RNF17** API documentada em OpenAPI | `docs/openapi.yaml` e `docs/docs.html` (Swagger UI) | - | "Try it out" no Swagger |
| **RNF18** Testes automatizados com PHPUnit | `phpunit.xml`, `backend/tests/` (158 testes) | A própria suíte | `php phpunit.phar --testdox` |
| **RNF19** Avisos sem dependência de som | Toasts, cor, forma e texto; nenhum recurso de áudio | - | Painel funciona com o som desligado |

## 4. Casos de uso e requisitos

| Caso de uso | Requisitos |
|---|---|
| Realizar login | RF13 |
| Consultar painel | RF01 a RF05, RF12 |
| Visualizar alertas | RF11 |
| Selecionar cenário | RF14 |
| Ligar turbina | RF15 |
| Desligar turbina | RF15 |
| Consultar registro de ações | RF16 |
| Restaurar simulação | RF17 |
| Sair | RF13 |

**Ações do sistema** (ovais amarelos do diagrama)

| Ação do sistema | Requisitos | Código |
|---|---|---|
| Validar credenciais | RF13 | `Usuario::verificarSenha`, `AuthService::entrar` |
| Classificar parâmetros | RF06, RF07, RF08 | `Reservatorio::verificarNivel`, `Turbina::verificarTemperatura`, `Turbina::verificarEstado` |
| Gerar alertas | RF09, RF10 | `Usina::gerarAlertas`, `Usina::verificarSituacaoGeral` |
| Notificar alerta ao operador | RF18 | `alertas.js`, `ui.js` |
| Registrar ação do operador | RF16 | `RegistroAcao`, `RegistroService` |

## 5. Resumo da cobertura

| Medida | Resultado |
|---|---|
| Requisitos funcionais | 18 |
| Com teste automatizado na parte de regra e dados | 17 (RF01 a RF17) |
| Só com verificação manual | 1 (RF18, toasts, que é JavaScript) |
| Requisitos de tela (RF11 e RF12) | A regra e os dados são testados no PHP, e a exibição é conferida à mão |
| Requisitos não funcionais | 19: 8 com teste automatizado (RNF06, 07, 09, 10, 11, 15, 16 e 18) e 11 verificados por inspeção do código ou conferência manual |
| Testes automatizados | 158, em 11 classes de teste |

A contagem de testes por classe está em [`testes.md`](testes.md#8-quantidade-de-testes-por-classe).

## 6. Observações e ajustes em relação ao relatório

- **Operador:** deixou de ser apenas ator externo. Existe como classe (herda de `Usuario`) e é o usuário autenticado.
- **`exibirDados()` e `exibirTurbinas()`** não existem mais na `Usina`: exibir é papel do frontend, e o PHP só devolve JSON.
- **Atributos:** passaram de `public` para `private`, com construtor e getters. As validações ficam nos construtores.
- **Cenários:** deixaram de estar "em um array no `index.php`" e passaram para o `UsinaRepositorySimulado`.
- **Novas classes:** `Usuario`, `Operador` e `RegistroAcao`, além de `Usina::buscarTurbina`, `Turbina::ligar` e `Turbina::desligar`.
- **Relatório em PDF ou CSV:** fora do escopo desta versão (trabalho futuro). A constante `RegistroAcao::GERAR_RELATORIO` permanece no código, testada, mas sem uso.
- **Som:** não implementado por decisão de projeto (RNF19).
- **Textos dos alertas:** citam a turbina ("Temperatura da Turbina 01 em 92 °C"), o que dá mais informação que o texto genérico da seção 2.5 do relatório. Atualize a seção para o que ficou implementado.
- **Códigos RF13 a RF18 e RNF15 a RNF19** são a numeração proposta para o relatório técnico.
