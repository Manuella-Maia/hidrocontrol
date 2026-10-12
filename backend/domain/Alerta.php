<?php
declare(strict_types=1);

/**
 * Aviso emitido ao operador quando um parâmetro sai do normal.
 *
 * O alerta só carrega dados e é imutável: representa um fato daquele momento.
 * Quem decide a gravidade e a ação recomendada é a Usina (gerarAlertas()).
 */
class Alerta
{
    public const ATENCAO = 'ATENÇÃO';
    public const CRITICO = 'CRÍTICO';

    private string $codigo;
    private string $parametro;
    private string $gravidade;
    private string $mensagem;
    private string $acaoRecomendada;

    /**
     * @param string $codigo          identificador derivado, ex.: "NIVEL-ATENCAO"
     * @param string $parametro       parâmetro que gerou o alerta, ex.: "Nível do reservatório"
     * @param string $gravidade       Alerta::ATENCAO ou Alerta::CRITICO
     * @param string $mensagem        o que aconteceu (sem ponto final)
     * @param string $acaoRecomendada o que o operador deve fazer (sem ponto final)
     *
     * @throws InvalidArgumentException se algum texto for vazio ou a gravidade for inválida
     */
    public function __construct(
        string $codigo,
        string $parametro,
        string $gravidade,
        string $mensagem,
        string $acaoRecomendada
    ) {
        if (trim($codigo) === '') {
            throw new InvalidArgumentException('O código do alerta não pode ser vazio.');
        }
        if (trim($parametro) === '') {
            throw new InvalidArgumentException('O parâmetro do alerta não pode ser vazio.');
        }
        if ($gravidade !== self::ATENCAO && $gravidade !== self::CRITICO) {
            throw new InvalidArgumentException('A gravidade deve ser "ATENÇÃO" ou "CRÍTICO".');
        }
        if (trim($mensagem) === '') {
            throw new InvalidArgumentException('A mensagem do alerta não pode ser vazia.');
        }
        if (trim($acaoRecomendada) === '') {
            throw new InvalidArgumentException('A ação recomendada não pode ser vazia.');
        }

        $this->codigo = $codigo;
        $this->parametro = $parametro;
        $this->gravidade = $gravidade;
        $this->mensagem = $mensagem;
        $this->acaoRecomendada = $acaoRecomendada;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getParametro(): string
    {
        return $this->parametro;
    }

    public function getGravidade(): string
    {
        return $this->gravidade;
    }

    public function getMensagem(): string
    {
        return $this->mensagem;
    }

    public function getAcaoRecomendada(): string
    {
        return $this->acaoRecomendada;
    }

    /**
     * Mensagem pronta para exibir ao operador (toast, relatório).
     * Ex.: "ATENÇÃO: Nível abaixo de 30%. Ação: Reduzir a vazão turbinada."
     */
    public function texto(): string
    {
        return "{$this->gravidade}: {$this->mensagem}. Ação: {$this->acaoRecomendada}.";
    }
}
