# Testes do HidroControl

Este documento reúne o que é verificado automaticamente (PHPUnit) e o roteiro de conferência manual (painel, Swagger e terminal).

## Sumário

1. [Como executar](#1-como-executar)
2. [Valores de fronteira: nível do reservatório](#2-valores-de-fronteira-nível-do-reservatório)
3. [Valores de fronteira: temperatura da turbina](#3-valores-de-fronteira-temperatura-da-turbina)
4. [Situação geral e alertas](#4-situação-geral-e-alertas)
5. [Cenários A, B e C](#5-cenários-a-b-e-c)
6. [Validações de entrada](#6-validações-de-entrada)
7. [Comportamento: turbinas, persistência e autenticação](#7-comportamento-turbinas-persistência-e-autenticação)
8. [Quantidade de testes por classe](#8-quantidade-de-testes-por-classe)
9. [Conferência manual](#9-conferência-manual)

---

## 1. Como executar

Na pasta raiz do projeto (onde estão `phpunit.xml` e `phpunit.phar`), no terminal do Laragon:

```
php phpunit.phar --testdox
```

Ambiente em que a suíte foi executada: PHPUnit 11.5.57, PHP 8.2 (terminal do VS Code) e PHP 8.3.33 (Laragon). Os testes usam arquivos temporários e nunca tocam em `backend/storage/`.

Outros comandos úteis:

| Objetivo | Comando |
|---|---|
| Rodar uma classe | `php phpunit.phar backend/tests/UsinaTest.php` |
| Rodar pelo nome | `php phpunit.phar --filter testVerificarNivel` |
| Listar sem executar | `php phpunit.phar --list-tests` |

Na coluna **Resultado**, "✔" significa que o teste passa na suíte completa.

## 2. Valores de fronteira: nível do reservatório

Regra (RF06, RNF06): abaixo de 30 é nível baixo, de 30 a 90 (inclusive) é normal, acima de 90 é nível muito alto.

Teste: `ReservatorioTest::testVerificarNivel`

| Nível (%) | Esperado | Resultado |
|---|---|---|
| 0 | ALERTA: nível baixo | ✔ |
| 29.9 | ALERTA: nível baixo | ✔ |
| **30** | NORMAL | ✔ |
| 60 | NORMAL | ✔ |
| **90** | NORMAL | ✔ |
| 90.1 | ALERTA: nível muito alto | ✔ |
| 100 | ALERTA: nível muito alto | ✔ |

## 3. Valores de fronteira: temperatura da turbina

Regra (RF07, RNF06): abaixo de 70 é normal, de 70 a 85 (inclusive) é atenção, acima de 85 é crítico.

Teste: `TurbinaTest::testVerificarTemperatura`

| Temperatura (°C) | Esperado | Resultado |
|---|---|---|
| 20 | NORMAL | ✔ |
| 69.9 | NORMAL | ✔ |
| **70** | ATENÇÃO | ✔ |
| 78 | ATENÇÃO | ✔ |
| **85** | ATENÇÃO | ✔ |
| 85.1 | CRÍTICO | ✔ |
| 120 | CRÍTICO | ✔ |

## 4. Situação geral e alertas

Regra (RF09, RF10): no máximo um alerta por parâmetro (nível, temperatura e turbinas), e a situação geral é o pior estado encontrado (CRÍTICO, depois ATENÇÃO, depois NORMAL).

Teste: `UsinaTest::testQuantidadeDeAlertasESituacaoGeral`

| Caso | Nível (%) | Turbinas (estado e °C) | Alertas esperados | Situação geral esperada | Resultado |
|---|---|---|---|---|---|
| A: operação normal | 70 | ligada 65, ligada 62, ligada 60 | 0 | NORMAL | ✔ |
| B: atenção | 25 | ligada 78, ligada 70, ligada 65 | 2 | ATENÇÃO | ✔ |
| C: crítico | 95 | ligada 92, **desligada** 70, ligada 65 | 3 | CRÍTICO | ✔ |
| Nível 30 não gera alerta | 30 | ligada 60 | 0 | NORMAL | ✔ |
| Nível 90 não gera alerta | 90 | ligada 60 | 0 | NORMAL | ✔ |
| Nível 29.9 gera alerta | 29.9 | ligada 60 | 1 | ATENÇÃO | ✔ |
| Nível 90.1 gera alerta | 90.1 | ligada 60 | 1 | ATENÇÃO | ✔ |
| Temperatura 69.9 normal | 60 | ligada 69.9 | 0 | NORMAL | ✔ |
| Temperatura 70 atenção | 60 | ligada 70 | 1 | ATENÇÃO | ✔ |
| Temperatura 85 atenção | 60 | ligada 85 | 1 | ATENÇÃO | ✔ |
| Temperatura 85.1 crítico | 60 | ligada 85.1 | 1 | CRÍTICO | ✔ |
| Só uma turbina desligada | 60 | **desligada** 60 | 1 | ATENÇÃO | ✔ |

Outras verificações da `Usina`:

| Verificação | Teste | Resultado |
|---|---|---|
| Cenário C gera os alertas na ordem nível, temperatura e turbina, com gravidade ATENÇÃO, CRÍTICO e ATENÇÃO | `testCenarioCGeraOsTresAlertasNaOrdemEComAGravidadeCerta` | ✔ |
| Conteúdo do alerta de nível baixo, de nível muito alto, de temperatura em atenção, de temperatura crítica e de turbina desligada (parâmetro, mensagem e ação) | `testAlertaDeNivelBaixo`, `testAlertaDeNivelMuitoAlto`, `testAlertaDeTemperaturaEmAtencao`, `testAlertaDeTemperaturaCritica`, `testAlertaDeUmaTurbinaDesligada` | ✔ |
| Várias turbinas desligadas continuam gerando um só alerta | `testAlertaDeVariasTurbinasDesligadasContinuaSendoUmSo` | ✔ |
| A turbina mais quente decide a classificação de temperatura e é citada no alerta | `testVerificarTemperaturaUsaATurbinaMaisQuente`, `testAlertaDeTemperaturaCitaATurbinaMaisQuente` | ✔ |
| "EM OPERAÇÃO" só com todas ligadas, "DESLIGADA" com ao menos uma desligada | `testVerificarTurbinaComTodasLigadas`, `testVerificarTurbinaComAoMenosUmaDesligada` | ✔ |

## 5. Cenários A, B e C

Teste: `UsinaRepositorySimuladoTest::testCenarioProduzOResultadoEsperado`

| Cenário | Situação geral | Alertas | Nível | Turbinas desligadas | Resultado |
|---|---|---|---|---|---|
| A | NORMAL | 0 | 70 | 0 | ✔ |
| B | ATENÇÃO | 2 | 25 | 0 | ✔ |
| C | CRÍTICO | 3 | 95 | 1 | ✔ |

Outras verificações do repository simulado:

| Verificação | Teste | Resultado |
|---|---|---|
| Cenário em minúsculas e com espaços é aceito (`" b "`) | `testAceitaCenarioEmMinusculasEComEspacos` | ✔ |
| Cenários inexistentes ou vazios são rejeitados (`D`, vazio, `1`, `normal`) | `testRejeitaCenarioInvalido` | ✔ |
| Cada chamada devolve objetos independentes (desligar numa usina não afeta a seguinte) | `testCadaChamadaDevolveObjetosIndependentes` | ✔ |
| O operador informado é o responsável pela usina | `testOperadorInformadoEOResponsavelPelaUsina` | ✔ |

## 6. Validações de entrada

Cada construtor recusa dados inválidos com uma exceção (`InvalidArgumentException`).

| Classe | Entrada recusada | Teste |
|---|---|---|
| `Reservatorio` | Nível menor que 0 ou maior que 100; vazão negativa; código vazio | `testRejeitaNivelAbaixoDeZero`, `testRejeitaNivelAcimaDeCem`, `testRejeitaVazaoNegativa`, `testRejeitaCodigoVazio` |
| `Reservatorio` | Aceita os limites da faixa (0 e 100) | `testAceitaOsLimitesDaFaixaValida` |
| `Turbina` | Código vazio; nome vazio | `testRejeitaCodigoVazio`, `testRejeitaNomeVazio` |
| `Alerta` | Gravidade fora de "ATENÇÃO" e "CRÍTICO" (texto livre, sem acento, minúsculas, vazia); código, parâmetro, mensagem ou ação vazios | `testRejeitaGravidadeInvalida`, `testRejeitaCodigoVazio`, `testRejeitaParametroVazio`, `testRejeitaMensagemVazia`, `testRejeitaAcaoRecomendadaVazia` |
| `RegistroAcao` | Operador vazio; ação inválida; código de turbina vazio; ligar e desligar sem código; data e hora inválidas (texto, formato errado, mês e dia inexistentes) | `testRejeitaOperadorVazio`, `testRejeitaAcaoInvalida`, `testRejeitaCodigoDeTurbinaVazio`, `testLigarExigeCodigoDaTurbina`, `testDesligarExigeCodigoDaTurbina`, `testRejeitaDataEHoraInvalidas` |
| `Usuario` | Nome vazio; e-mail inválido (vazio, sem arroba, sem domínio, sem usuário, com espaço); **senha em texto puro** | `testRejeitaNomeVazio`, `testRejeitaEmailInvalido`, `testRejeitaSenhaEmTextoPuro` |
| `Usina` | Nome vazio; potência negativa; nenhuma turbina; item que não é `Turbina`; códigos de turbina repetidos | `testRejeitaNomeVazio`, `testRejeitaPotenciaNegativa`, `testRejeitaUsinaSemTurbinas`, `testRejeitaItemQueNaoEUmaTurbina`, `testRejeitaCodigosDeTurbinaRepetidos` |
| `Usina` | Aceita potência zero | `testAceitaPotenciaZero` |

## 7. Comportamento: turbinas, persistência e autenticação

### Ligar e desligar

| Verificação | Teste | Resultado |
|---|---|---|
| Desligar muda o estado de ligada para desligada, e ligar faz o inverso | `TurbinaTest::testDesligarMudaOEstadoDeLigadaParaDesligada`, `testLigarMudaOEstadoDeDesligadaParaLigada` | ✔ |
| Desligar uma turbina já desligada (e ligar uma já ligada) lança erro | `TurbinaTest::testDesligarTurbinaJaDesligadaLancaErro`, `testLigarTurbinaJaLigadaLancaErro` | ✔ |
| O erro não altera o estado | `TurbinaTest::testErroAoDesligarNaoAlteraOEstado` | ✔ |
| O operador devolve o registro da ação | `OperadorTest::testDesligarTurbinaDevolveORegistroDaAcao`, `testLigarTurbinaDevolveORegistroDaAcao` | ✔ |
| Com erro, **nenhum registro é criado** e a turbina não muda | `OperadorTest::testErroNaoGeraRegistroNemAlteraAturbina` | ✔ |
| Desligar pelo operador faz a usina passar a gerar o alerta de turbina; ligar de novo volta ao normal | `UsinaTest::testDesligarTurbinaPeloOperadorPassaAGerarAlertaDeTurbina`, `testLigarDeNovoAATurbinaVoltaAoNormal` | ✔ |
| Buscar turbina pelo código devolve a mesma instância da usina; código inexistente lança erro | `UsinaTest::testBuscarTurbinaPeloCodigo`, `testBuscarTurbinaDevolveAMesmaInstanciaDaUsina`, `testBuscarTurbinaInexistenteLancaErro` | ✔ |

### Service e persistência

| Verificação | Teste | Resultado |
|---|---|---|
| Desligar atualiza a leitura (ATENÇÃO, alerta de turbina) e registra a ação | `UsinaServiceTest::testDesligarTurbinaAtualizaALeituraERegistraAAcao` | ✔ |
| A alteração **persiste entre requisições** e só vale no cenário alterado | `UsinaServiceTest::testAlteracaoPersisteEntreRequisicoesSoNoCenarioAlterado` | ✔ |
| Ligar a T-02 no cenário C remove o alerta de turbina | `UsinaServiceTest::testLigarTurbinaDesligadaDoCenarioCRemoveOAlertaDeTurbina` | ✔ |
| Desligar uma turbina já desligada **não grava estado nem registro** | `UsinaServiceTest::testDesligarTurbinaJaDesligadaNaoGravaEstadoNemRegistro` | ✔ |
| Turbina inexistente lança erro | `UsinaServiceTest::testTurbinaInexistenteLancaErro` | ✔ |
| Restaurar desfaz as alterações e registra a ação | `UsinaServiceTest::testRestaurarSimulacaoDesfazAsAlteracoesERegistraAAcao` | ✔ |
| A leitura **não expõe** CPF, e-mail, telefone nem hash do operador | `UsinaServiceTest::testLeituraNaoExpoeDadosPessoaisDoOperador` | ✔ |
| Cenário sem alertas vira JSON com a lista vazia | `UsinaServiceTest::testCenarioASemAlertasVirandoJsonComListaVazia` | ✔ |
| Arquivo JSON ausente ou corrompido devolve lista vazia, sem erro | `PersistenciaJsonTest::testLerArquivoInexistenteDevolveListaVazia`, `testLerArquivoCorrompidoDevolveListaVazia` | ✔ |
| Gravar e ler preserva os acentos | `PersistenciaJsonTest::testGravarELerPreservaOsAcentos` | ✔ |
| O estado fica separado por cenário; limpar descarta tudo | `PersistenciaJsonTest::testEstadoDasTurbinasFicaSeparadoPorCenario`, `testLimparDescartaAsAlteracoesDeTodosOsCenarios` | ✔ |
| Registros voltam do mais recente para o mais antigo, respeitando o limite | `PersistenciaJsonTest::testRegistrosVoltamDoMaisRecenteParaOMaisAntigoRespeitandoOLimite` | ✔ |

### Autenticação

| Verificação | Teste | Resultado |
|---|---|---|
| Credenciais válidas abrem a sessão e registram ENTRAR | `AuthServiceTest::testEntrarComCredenciaisValidasAbreASessaoERegistraAEntrada` | ✔ |
| Senha errada não abre sessão nem registra | `AuthServiceTest::testSenhaErradaNaoAbreSessaoNemRegistra` | ✔ |
| E-mail inexistente não abre sessão | `AuthServiceTest::testEmailInexistenteNaoAbreSessao` | ✔ |
| E-mail não diferencia maiúsculas de minúsculas | `AuthServiceTest::testEmailNaoDiferenciaMaiusculasDeMinusculas` | ✔ |
| Sair encerra a sessão e registra SAIR; sair sem login não faz nada | `AuthServiceTest::testSairEncerraASessaoERegistraASaida`, `testSairSemLoginNaoFazNadaNemRegistra` | ✔ |
| Sessão adulterada (e-mail desconhecido ou valor que não é texto) não autentica | `AuthServiceTest::testSessaoAdulteradaNaoAutentica` | ✔ |
| Senha certa, errada e vazia; o hash não tem getter | `UsuarioTest::testVerificarSenhaCorreta`, `testVerificarSenhaErrada`, `testVerificarSenhaVazia`, `testHashDaSenhaNaoTemGetter` | ✔ |
| `Usuario` é abstrata e `Operador` herda dela | `UsuarioTest::testUsuarioEAbstrata`, `testOperadorHerdaDeUsuario` | ✔ |

## 8. Quantidade de testes por classe

| Classe de teste | Testes |
|---|---|
| `AlertaTest` | 13 |
| `AuthServiceTest` | 7 |
| `OperadorTest` | 7 |
| `PersistenciaJsonTest` | 6 |
| `RegistroAcaoTest` | 18 |
| `ReservatorioTest` | 13 |
| `TurbinaTest` | 17 |
| `UsinaRepositorySimuladoTest` | 15 |
| `UsinaServiceTest` | 9 |
| `UsinaTest` | 39 |
| `UsuarioTest` | 14 |
| **Total** | **158** |

Cada linha de um data provider conta como um teste (por exemplo, os 7 valores de `testVerificarNivel`).

## 9. Conferência manual

O frontend e a API em funcionamento são conferidos à mão. Marque cada item ao executar.

### Painel (navegador)

Endereço: `http://localhost/hidrocontrol/frontend/login.html`

| # | Passo | Esperado | OK |
|---|---|---|---|
| 1 | Abrir `frontend/index.html` sem login | Redireciona para o login | ☐ |
| 2 | Entrar com senha errada | Mensagem "E-mail ou senha inválidos." na própria tela | ☐ |
| 3 | Entrar com `joao@hidrocontrol.test` e `senha123` | Abre o painel | ☐ |
| 4 | Cenário **A** | NORMAL (círculo verde), "Nenhum alerta ativo", nenhum toast | ☐ |
| 5 | Cenário **B** | ATENÇÃO (triângulo amarelo), 2 alertas e 2 toasts | ☐ |
| 6 | Cenário **C** | CRÍTICO (losango vermelho), 3 alertas e 3 toasts (o crítico primeiro) | ☐ |
| 7 | Clicar em **Atualizar** no cenário C | Nenhum toast novo | ☐ |
| 8 | Esperar cerca de 8 s | Toasts de atenção somem; o crítico permanece até **Fechar** | ☐ |
| 9 | Tanque do reservatório | Nível proporcional, com marcas em 30% e 90% | ☐ |
| 10 | Barra de temperatura | Marcas em 70 e 85 em cada turbina | ☐ |
| 11 | No cenário A, **Desligar** a Turbina 02 | ATENÇÃO, alerta de turbina desligada, toast, botão passa a "Ligar" e o evento aparece em "Últimos eventos" | ☐ |
| 12 | **Ligar** a Turbina 02 | Volta a NORMAL e o alerta some | ☐ |
| 13 | No cenário C, **Ligar** a Turbina 02 | O alerta de turbina some (ficam 2) | ☐ |
| 14 | Desligar a T-02 no A, trocar para B e voltar ao A | A alteração continua no A, e o B não foi afetado | ☐ |
| 15 | **Restaurar simulação** e confirmar | Tudo volta ao padrão e "Restaurar simulação" aparece nos eventos | ☐ |
| 16 | Abrir o painel em duas abas, desligar a T-02 em uma e clicar em **Desligar** na outra sem atualizar | A segunda aba mostra o erro (409) e recarrega | ☐ |
| 17 | Redimensionar a janela ou usar o modo celular | Layout em uma coluna, toasts na largura da tela | ☐ |
| 18 | **Sair** | Volta ao login; o painel passa a exigir nova entrada | ☐ |

### API (terminal)

Veja os comandos completos no [README, seção 8](../README.md#8-testando-a-api-pelo-terminal-curl).

| # | Chamada | Esperado | OK |
|---|---|---|---|
| 1 | Leitura sem login | 401 | ☐ |
| 2 | Entrar com senha errada | 401 | ☐ |
| 3 | Entrar com credenciais corretas | 200 e o operador (só nome e setor) | ☐ |
| 4 | Leitura dos cenários A, B e C com sessão | 200 (NORMAL, ATENÇÃO e CRÍTICO) | ☐ |
| 5 | Leitura com `cenario=Z` | 400 | ☐ |
| 6 | Desligar a T-02 no cenário A | 200, situação ATENÇÃO e o campo `registro` | ☐ |
| 7 | A mesma chamada, repetida | 409 | ☐ |
| 8 | Ligar a T-02 no cenário A | 200 e situação NORMAL | ☐ |
| 9 | Ligar a T-02 de novo | 409 | ☐ |
| 10 | Desligar `T-99` | 404 | ☐ |
| 11 | `acao=explodir` | 400 | ☐ |
| 12 | Rota de turbina com GET | 405 | ☐ |
| 13 | Registros | Histórico, do mais recente ao mais antigo | ☐ |
| 14 | Restaurar | 200 e o cenário A volta ao NORMAL | ☐ |
| 15 | Sair e repetir a leitura | 200 e depois 401 | ☐ |

### Documentação (Swagger)

Endereço: `http://localhost/hidrocontrol/docs/docs.html`

| # | Passo | Esperado | OK |
|---|---|---|---|
| 1 | Executar **entrar** | 200 | ☐ |
| 2 | Executar **leitura** com o cenário C | 200 com 3 alertas | ☐ |
| 3 | Executar **desligar** com `codigo=T-02` e `cenario=A` | 200, e o `codigo` e o `cenario` aparecem na chamada | ☐ |
| 4 | Executar **registros** | A ação aparece | ☐ |
| 5 | Executar **restaurar** | 200 | ☐ |
