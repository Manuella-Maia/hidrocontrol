<?php
declare(strict_types=1);

/**
 * Represa que armazena a água da usina.
 * Guarda o nível (%) e a vazão (m³/s) e conhece a regra de classificação do nível.
 */
class Reservatorio
{
    /** Abaixo deste nível (%), o reservatório está baixo. */
    public const NIVEL_MINIMO = 30.0;

    /** Acima deste nível (%), o reservatório está muito alto. */
    public const NIVEL_MAXIMO = 90.0;

    public const NIVEL_BAIXO = 'ALERTA: nível baixo';
    public const NIVEL_NORMAL = 'NORMAL';
    public const NIVEL_MUITO_ALTO = 'ALERTA: nível muito alto';

    private string $codigo;
    private float $nivel;
    private float $vazao;

    /**
     * @throws InvalidArgumentException se o código for vazio, o nível estiver
     *                                  fora de 0 a 100 ou a vazão for negativa
     */
    public function __construct(string $codigo, float $nivel, float $vazao)
    {
        if (trim($codigo) === '') {
            throw new InvalidArgumentException('O código do reservatório não pode ser vazio.');
        }
        if ($nivel < 0.0 || $nivel > 100.0) {
            throw new InvalidArgumentException('O nível do reservatório deve estar entre 0 e 100.');
        }
        if ($vazao < 0.0) {
            throw new InvalidArgumentException('A vazão não pode ser negativa.');
        }

        $this->codigo = $codigo;
        $this->nivel = $nivel;
        $this->vazao = $vazao;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getNivel(): float
    {
        return $this->nivel;
    }

    public function getVazao(): float
    {
        return $this->vazao;
    }

    /**
     * Classifica o nível (RF06). Os valores 30 e 90 são considerados NORMAL.
     */
    public function verificarNivel(): string
    {
        if ($this->nivel < self::NIVEL_MINIMO) {
            return self::NIVEL_BAIXO;
        }
        if ($this->nivel > self::NIVEL_MAXIMO) {
            return self::NIVEL_MUITO_ALTO;
        }
        return self::NIVEL_NORMAL;
    }
}
