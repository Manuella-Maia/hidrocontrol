<?php
declare(strict_types=1);

/**
 * UsinaRepositorySimulado.php
 *
 * Arquivo responsável por montar a usina a partir de cenários simulados
 * (normal, atenção e crítico), aplicando por cima as alterações que o
 * operador fez nas turbinas.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Entrega a usina a partir de cenários de teste guardados no código:
 *
 *   A: operação normal (nenhum alerta)
 *   B: atenção (nível baixo e temperatura em ATENÇÃO)
 *   C: crítico (nível muito alto, temperatura crítica e uma turbina desligada)
 *
 * Cada chamada monta objetos novos e independentes. Se houver um
 * EstadoRepositoryInterface, as alterações que o operador fez nas turbinas
 * (ligar e desligar) são aplicadas por cima dos valores do cenário.
 */
class UsinaRepositorySimulado implements UsinaRepositoryInterface
{
    public const CENARIO_NORMAL = 'A';
    public const CENARIO_ATENCAO = 'B';
    public const CENARIO_CRITICO = 'C';

    public const CENARIOS = [
        self::CENARIO_NORMAL,
        self::CENARIO_ATENCAO,
        self::CENARIO_CRITICO,
    ];

    private const NOME_USINA = 'Usina SENAI';

    /**
     * Valores simulados de cada cenário.
     * Turbinas: [código, nome, ligada, temperatura em °C].
     */
    private const DADOS = [
        self::CENARIO_NORMAL => [
            'potencia' => 65.8,
            'nivel' => 70.0,
            'vazao' => 420.0,
            'turbinas' => [
                ['T-01', 'Turbina 01', true, 65.0],
                ['T-02', 'Turbina 02', true, 62.0],
                ['T-03', 'Turbina 03', true, 60.0],
            ],
        ],
        self::CENARIO_ATENCAO => [
            'potencia' => 48.2,
            'nivel' => 25.0,
            'vazao' => 190.0,
            'turbinas' => [
                ['T-01', 'Turbina 01', true, 78.0],
                ['T-02', 'Turbina 02', true, 70.0],
                ['T-03', 'Turbina 03', true, 65.0],
            ],
        ],
        self::CENARIO_CRITICO => [
            'potencia' => 21.5,
            'nivel' => 95.0,
            'vazao' => 610.0,
            'turbinas' => [
                ['T-01', 'Turbina 01', true, 92.0],
                ['T-02', 'Turbina 02', false, 70.0],
                ['T-03', 'Turbina 03', true, 65.0],
            ],
        ],
    ];

    private ?EstadoRepositoryInterface $estadoRepository;

    public function __construct(?EstadoRepositoryInterface $estadoRepository = null)
    {
        $this->estadoRepository = $estadoRepository;
    }

    public function obterUsina(string $cenario, Operador $operador): Usina
    {
        $cenario = strtoupper(trim($cenario));
        if (!isset(self::DADOS[$cenario])) {
            throw new InvalidArgumentException(
                'Cenário inválido: use ' . implode(', ', self::CENARIOS) . '.'
            );
        }

        $dados = self::DADOS[$cenario];
        $alteracoes = $this->estadoRepository?->obterEstadoTurbinas($cenario) ?? [];

        $turbinas = [];
        foreach ($dados['turbinas'] as [$codigo, $nome, $ligada, $temperatura]) {
            $turbinas[] = new Turbina($codigo, $nome, $alteracoes[$codigo] ?? $ligada, $temperatura);
        }

        return new Usina(
            self::NOME_USINA,
            $operador,
            $dados['potencia'],
            new Reservatorio('R-01', $dados['nivel'], $dados['vazao']),
            $turbinas
        );
    }
}
