<?php
declare(strict_types=1);

/**
 * Equipamento gerador da usina.
 * Guarda o estado (ligada ou desligada) e a temperatura, e conhece as regras
 * de classificação de ambos.
 */
class Turbina
{
    /** Temperatura (°C) a partir da qual a turbina entra em ATENÇÃO (inclusive). */
    public const TEMPERATURA_ATENCAO = 70.0;

    /** Temperatura (°C) acima da qual a turbina é CRÍTICA (85 ainda é ATENÇÃO). */
    public const TEMPERATURA_CRITICA = 85.0;

    public const TEMP_NORMAL = 'NORMAL';
    public const TEMP_ATENCAO = 'ATENÇÃO';
    public const TEMP_CRITICO = 'CRÍTICO';

    public const ESTADO_EM_OPERACAO = 'EM OPERAÇÃO';
    public const ESTADO_DESLIGADA = 'DESLIGADA';

    private string $codigo;
    private string $nome;
    private bool $ligada;
    private float $temperatura;

    /**
     * @throws InvalidArgumentException se o código ou o nome forem vazios
     */
    public function __construct(string $codigo, string $nome, bool $ligada, float $temperatura)
    {
        if (trim($codigo) === '') {
            throw new InvalidArgumentException('O código da turbina não pode ser vazio.');
        }
        if (trim($nome) === '') {
            throw new InvalidArgumentException('O nome da turbina não pode ser vazio.');
        }

        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->ligada = $ligada;
        $this->temperatura = $temperatura;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function isLigada(): bool
    {
        return $this->ligada;
    }

    public function getTemperatura(): float
    {
        return $this->temperatura;
    }

    /**
     * Liga a turbina.
     *
     * @throws LogicException se a turbina já estiver ligada
     */
    public function ligar(): void
    {
        if ($this->ligada) {
            throw new LogicException("A turbina {$this->codigo} já está ligada.");
        }
        $this->ligada = true;
    }

    /**
     * Desliga a turbina.
     *
     * @throws LogicException se a turbina já estiver desligada
     */
    public function desligar(): void
    {
        if (!$this->ligada) {
            throw new LogicException("A turbina {$this->codigo} já está desligada.");
        }
        $this->ligada = false;
    }

    /**
     * Classifica o estado da turbina (RF08).
     */
    public function verificarEstado(): string
    {
        return $this->ligada ? self::ESTADO_EM_OPERACAO : self::ESTADO_DESLIGADA;
    }

    /**
     * Classifica a temperatura (RF07). Os valores 70 e 85 são ATENÇÃO.
     */
    public function verificarTemperatura(): string
    {
        if ($this->temperatura < self::TEMPERATURA_ATENCAO) {
            return self::TEMP_NORMAL;
        }
        if ($this->temperatura <= self::TEMPERATURA_CRITICA) {
            return self::TEMP_ATENCAO;
        }
        return self::TEMP_CRITICO;
    }
}
