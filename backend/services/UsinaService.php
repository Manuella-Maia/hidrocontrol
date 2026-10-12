<?php
declare(strict_types=1);

/**
 * UsinaService.php
 *
 * Arquivo responsável por orquestrar o domínio da usina: obtém a usina do
 * repository, monta a leitura em formato pronto para JSON e coordena as ações
 * do operador (ligar, desligar e restaurar a simulação).
 *
 * @package service
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Orquestra o domínio: pede a usina ao repository, consulta as regras e monta
 * a leitura (array pronto para virar JSON).
 *
 * Também coordena as ações do operador (ligar, desligar, restaurar): o domínio muda
 * o estado, e este service guarda a alteração e o registro da ação.
 *
 * Não contém regra de decisão (limites, gravidade): isso é do domínio.
 * Do operador, expõe apenas nome e setor, nunca CPF, e-mail, telefone ou senha.
 */
class UsinaService
{
    private UsinaRepositoryInterface $repository;
    private EstadoRepositoryInterface $estadoRepository;
    private RegistroService $registroService;

    public function __construct(
        UsinaRepositoryInterface $repository,
        EstadoRepositoryInterface $estadoRepository,
        RegistroService $registroService
    ) {
        $this->repository = $repository;
        $this->estadoRepository = $estadoRepository;
        $this->registroService = $registroService;
    }

    /**
     * Leitura completa da usina no cenário informado.
     *
     * @throws InvalidArgumentException se o cenário não existir
     */
    public function obterLeitura(string $cenario, Operador $operador): array
    {
        $usina = $this->repository->obterUsina($cenario, $operador);

        return $this->montarLeitura($usina, strtoupper(trim($cenario)));
    }

    /**
     * Liga ou desliga uma turbina e devolve a leitura atualizada.
     * O estado e o registro só são gravados depois de a turbina mudar com sucesso.
     *
     * @throws InvalidArgumentException se o cenário não existir
     * @throws OutOfBoundsException     se a turbina não existir
     * @throws LogicException           se a turbina já estiver no estado pedido
     */
    public function alterarEstadoTurbina(string $cenario, string $codigo, bool $ligar, Operador $operador): array
    {
        $cenario = strtoupper(trim($cenario));
        $usina = $this->repository->obterUsina($cenario, $operador);
        $turbina = $usina->buscarTurbina($codigo);
        $responsavel = $usina->getOperadorResponsavel();

        $registro = $ligar
            ? $responsavel->ligarTurbina($turbina)
            : $responsavel->desligarTurbina($turbina);

        $this->estadoRepository->definirEstadoTurbina($cenario, $turbina->getCodigo(), $ligar);
        $this->registroService->registrar($registro);

        $leitura = $this->montarLeitura($usina, $cenario);
        $leitura['registro'] = $registro->texto();

        return $leitura;
    }

    /**
     * Descarta as alterações feitas nas turbinas (todos os cenários) e registra a ação.
     */
    public function restaurarSimulacao(Operador $operador): void
    {
        $this->estadoRepository->limpar();
        $this->registroService->registrar(
            new RegistroAcao($operador->getNome(), RegistroAcao::RESTAURAR_SIMULACAO)
        );
    }

    /**
     * Últimas ações dos operadores, da mais recente para a mais antiga.
     */
    public function obterRegistros(int $limite = 10): array
    {
        $registros = [];
        foreach ($this->registroService->listarUltimos($limite) as $registro) {
            $registros[] = [
                'operador' => $registro->getOperador(),
                'acao' => $registro->getAcao(),
                'codigoTurbina' => $registro->getCodigoTurbina(),
                'dataHora' => $registro->getDataHora(),
                'texto' => $registro->texto(),
            ];
        }
        return $registros;
    }

    /**
     * Converte uma usina na estrutura devolvida pela API.
     */
    public function montarLeitura(Usina $usina, string $cenario): array
    {
        $reservatorio = $usina->getReservatorio();
        $operador = $usina->getOperadorResponsavel();

        $turbinas = [];
        foreach ($usina->getTurbinas() as $turbina) {
            $turbinas[] = [
                'codigo' => $turbina->getCodigo(),
                'nome' => $turbina->getNome(),
                'ligada' => $turbina->isLigada(),
                'estado' => $turbina->verificarEstado(),
                'temperatura' => $turbina->getTemperatura(),
                'situacaoTemperatura' => $turbina->verificarTemperatura(),
            ];
        }

        $alertas = [];
        foreach ($usina->gerarAlertas() as $alerta) {
            $alertas[] = [
                'codigo' => $alerta->getCodigo(),
                'parametro' => $alerta->getParametro(),
                'gravidade' => $alerta->getGravidade(),
                'mensagem' => $alerta->getMensagem(),
                'acaoRecomendada' => $alerta->getAcaoRecomendada(),
                'texto' => $alerta->texto(),
            ];
        }

        return [
            'cenario' => $cenario,
            'situacaoGeral' => $usina->verificarSituacaoGeral(),
            'usina' => [
                'nome' => $usina->getNome(),
                'potenciaGerada' => $usina->getPotenciaGerada(),
                'operador' => [
                    'nome' => $operador->getNome(),
                    'setor' => $operador->getSetor(),
                ],
            ],
            'reservatorio' => [
                'codigo' => $reservatorio->getCodigo(),
                'nivel' => $reservatorio->getNivel(),
                'vazao' => $reservatorio->getVazao(),
                'situacao' => $reservatorio->verificarNivel(),
            ],
            'resumoTurbinas' => [
                'quantidade' => $usina->quantidadeTurbinas(),
                'temperaturaMaxima' => $usina->temperaturaMaxima(),
                'situacaoTemperatura' => $usina->verificarTemperatura(),
                'estado' => $usina->verificarTurbina(),
            ],
            'turbinas' => $turbinas,
            'alertas' => $alertas,
            // Limites das regras, para o painel desenhar as marcas das escalas
            // sem repetir os números (a fonte única é o domínio).
            'limites' => [
                'nivelMinimo' => Reservatorio::NIVEL_MINIMO,
                'nivelMaximo' => Reservatorio::NIVEL_MAXIMO,
                'temperaturaAtencao' => Turbina::TEMPERATURA_ATENCAO,
                'temperaturaCritica' => Turbina::TEMPERATURA_CRITICA,
            ],
        ];
    }
}
