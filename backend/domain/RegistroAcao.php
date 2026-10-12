<?php
declare(strict_types=1);

/**
 * Registro de uma ação do operador (quem fez o quê, em qual turbina e quando).
 *
 * É um fato do passado e, por isso, imutável. Guarda o NOME do operador (texto),
 * e não o objeto, para o histórico não depender de o usuário continuar existindo
 * e para não expor dados pessoais no arquivo JSON.
 */
class RegistroAcao
{
    public const LIGAR_TURBINA = 'LIGAR_TURBINA';
    public const DESLIGAR_TURBINA = 'DESLIGAR_TURBINA';
    public const ENTRAR = 'ENTRAR';
    public const SAIR = 'SAIR';
    public const GERAR_RELATORIO = 'GERAR_RELATORIO';
    public const RESTAURAR_SIMULACAO = 'RESTAURAR_SIMULACAO';

    public const ACOES = [
        self::LIGAR_TURBINA,
        self::DESLIGAR_TURBINA,
        self::ENTRAR,
        self::SAIR,
        self::GERAR_RELATORIO,
        self::RESTAURAR_SIMULACAO,
    ];

    /** Formato de data e hora guardado e gravado no JSON. */
    public const FORMATO_DATA_HORA = 'Y-m-d H:i:s';

    private string $operador;
    private string $acao;
    private ?string $codigoTurbina;
    private string $dataHora;

    /**
     * @param string      $operador      nome do operador que realizou a ação
     * @param string      $acao          uma das constantes de RegistroAcao::ACOES
     * @param string|null $codigoTurbina código da turbina (obrigatório para ligar e desligar)
     * @param string|null $dataHora      "Y-m-d H:i:s"; se omitida, usa o momento atual
     *
     * @throws InvalidArgumentException se algum dado for inválido
     */
    public function __construct(
        string $operador,
        string $acao,
        ?string $codigoTurbina = null,
        ?string $dataHora = null
    ) {
        if (trim($operador) === '') {
            throw new InvalidArgumentException('O nome do operador não pode ser vazio.');
        }
        if (!in_array($acao, self::ACOES, true)) {
            throw new InvalidArgumentException("Ação inválida: {$acao}.");
        }
        if ($codigoTurbina !== null && trim($codigoTurbina) === '') {
            throw new InvalidArgumentException('O código da turbina não pode ser vazio.');
        }
        $acaoDeTurbina = $acao === self::LIGAR_TURBINA || $acao === self::DESLIGAR_TURBINA;
        if ($acaoDeTurbina && $codigoTurbina === null) {
            throw new InvalidArgumentException('Ligar e desligar exigem o código da turbina.');
        }

        $dataHora = $dataHora ?? date(self::FORMATO_DATA_HORA);
        $data = DateTime::createFromFormat(self::FORMATO_DATA_HORA, $dataHora);
        if ($data === false || $data->format(self::FORMATO_DATA_HORA) !== $dataHora) {
            throw new InvalidArgumentException('Data e hora inválidas. Use o formato "Y-m-d H:i:s".');
        }

        $this->operador = $operador;
        $this->acao = $acao;
        $this->codigoTurbina = $codigoTurbina;
        $this->dataHora = $dataHora;
    }

    public function getOperador(): string
    {
        return $this->operador;
    }

    public function getAcao(): string
    {
        return $this->acao;
    }

    public function getCodigoTurbina(): ?string
    {
        return $this->codigoTurbina;
    }

    public function getDataHora(): string
    {
        return $this->dataHora;
    }

    /**
     * Frase pronta para a lista "Últimos eventos".
     * Ex.: "09/10 14:32 – João desligou a turbina T-02"
     */
    public function texto(): string
    {
        $data = DateTime::createFromFormat(self::FORMATO_DATA_HORA, $this->dataHora);
        $quando = $data->format('d/m H:i');

        $descricao = match ($this->acao) {
            self::LIGAR_TURBINA => "ligou a turbina {$this->codigoTurbina}",
            self::DESLIGAR_TURBINA => "desligou a turbina {$this->codigoTurbina}",
            self::ENTRAR => 'entrou no sistema',
            self::SAIR => 'saiu do sistema',
            self::GERAR_RELATORIO => 'gerou o relatório',
            self::RESTAURAR_SIMULACAO => 'restaurou a simulação',
        };

        return "{$quando} – {$this->operador} {$descricao}";
    }
}
