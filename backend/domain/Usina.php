<?php
declare(strict_types=1);

/**
 * Central hidrelétrica: reúne o reservatório e as turbinas e consolida a situação geral.
 *
 * A Usina não repete as regras de decisão: ela delega ao Reservatorio e às Turbinas
 * (a regra fica junto do dado que avalia) e só decide o que depende do conjunto,
 * como a turbina mais quente, os alertas e o pior estado encontrado.
 */
class Usina
{
    public const SITUACAO_NORMAL = 'NORMAL';

    private string $nome;
    private Operador $operadorResponsavel;
    private float $potenciaGerada;
    private Reservatorio $reservatorio;

    /** @var Turbina[] */
    private array $turbinas;

    /**
     * @param Turbina[] $turbinas ao menos uma, com códigos diferentes entre si
     *
     * @throws InvalidArgumentException se o nome for vazio, a potência for negativa,
     *                                  não houver turbinas, algum item não for uma
     *                                  Turbina ou houver códigos repetidos
     */
    public function __construct(
        string $nome,
        Operador $operadorResponsavel,
        float $potenciaGerada,
        Reservatorio $reservatorio,
        array $turbinas
    ) {
        if (trim($nome) === '') {
            throw new InvalidArgumentException('O nome da usina não pode ser vazio.');
        }
        if ($potenciaGerada < 0.0) {
            throw new InvalidArgumentException('A potência gerada não pode ser negativa.');
        }
        if (count($turbinas) === 0) {
            throw new InvalidArgumentException('A usina precisa ter ao menos uma turbina.');
        }

        $codigos = [];
        foreach ($turbinas as $turbina) {
            if (!$turbina instanceof Turbina) {
                throw new InvalidArgumentException('A lista de turbinas deve conter apenas objetos Turbina.');
            }
            if (in_array($turbina->getCodigo(), $codigos, true)) {
                throw new InvalidArgumentException("Código de turbina repetido: {$turbina->getCodigo()}.");
            }
            $codigos[] = $turbina->getCodigo();
        }

        $this->nome = $nome;
        $this->operadorResponsavel = $operadorResponsavel;
        $this->potenciaGerada = $potenciaGerada;
        $this->reservatorio = $reservatorio;
        $this->turbinas = array_values($turbinas);
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getOperadorResponsavel(): Operador
    {
        return $this->operadorResponsavel;
    }

    public function getPotenciaGerada(): float
    {
        return $this->potenciaGerada;
    }

    public function getReservatorio(): Reservatorio
    {
        return $this->reservatorio;
    }

    /**
     * @return Turbina[]
     */
    public function getTurbinas(): array
    {
        return $this->turbinas;
    }

    /**
     * Localiza uma turbina pelo código.
     *
     * @throws OutOfBoundsException se não existir turbina com esse código (vira 404 na API)
     */
    public function buscarTurbina(string $codigo): Turbina
    {
        foreach ($this->turbinas as $turbina) {
            if ($turbina->getCodigo() === $codigo) {
                return $turbina;
            }
        }
        throw new OutOfBoundsException("Turbina não encontrada: {$codigo}.");
    }

    public function quantidadeTurbinas(): int
    {
        return count($this->turbinas);
    }

    /**
     * Maior temperatura entre as turbinas (a mais quente é a de maior risco).
     */
    public function temperaturaMaxima(): float
    {
        return $this->turbinaMaisQuente()->getTemperatura();
    }

    /**
     * Classificação do nível (delegada ao reservatório).
     */
    public function verificarNivel(): string
    {
        return $this->reservatorio->verificarNivel();
    }

    /**
     * Classificação da temperatura (delegada à turbina mais quente).
     */
    public function verificarTemperatura(): string
    {
        return $this->turbinaMaisQuente()->verificarTemperatura();
    }

    /**
     * "EM OPERAÇÃO" somente se todas as turbinas estiverem ligadas;
     * se ao menos uma estiver desligada, "DESLIGADA" (critério do pior estado).
     */
    public function verificarTurbina(): string
    {
        foreach ($this->turbinas as $turbina) {
            if ($turbina->verificarEstado() === Turbina::ESTADO_DESLIGADA) {
                return Turbina::ESTADO_DESLIGADA;
            }
        }
        return Turbina::ESTADO_EM_OPERACAO;
    }

    /**
     * Gera no máximo um alerta por parâmetro (nível, temperatura e turbinas),
     * portanto de 0 a 3 alertas, nessa ordem. Lista vazia se tudo estiver normal.
     *
     * @return Alerta[]
     */
    public function gerarAlertas(): array
    {
        $alertas = [];

        foreach ([$this->alertaDeNivel(), $this->alertaDeTemperatura(), $this->alertaDeTurbinas()] as $alerta) {
            if ($alerta !== null) {
                $alertas[] = $alerta;
            }
        }

        return $alertas;
    }

    /**
     * Situação geral: prevalece o pior estado encontrado.
     * Primeiro CRÍTICO, depois ATENÇÃO e, por fim, NORMAL.
     */
    public function verificarSituacaoGeral(): string
    {
        $alertas = $this->gerarAlertas();

        foreach ($alertas as $alerta) {
            if ($alerta->getGravidade() === Alerta::CRITICO) {
                return Alerta::CRITICO;
            }
        }

        return count($alertas) > 0 ? Alerta::ATENCAO : self::SITUACAO_NORMAL;
    }

    private function turbinaMaisQuente(): Turbina
    {
        $maisQuente = $this->turbinas[0];
        foreach ($this->turbinas as $turbina) {
            if ($turbina->getTemperatura() > $maisQuente->getTemperatura()) {
                $maisQuente = $turbina;
            }
        }
        return $maisQuente;
    }

    private function alertaDeNivel(): ?Alerta
    {
        return match ($this->verificarNivel()) {
            Reservatorio::NIVEL_BAIXO => new Alerta(
                'NIVEL-ATENCAO',
                'Nível do reservatório',
                Alerta::ATENCAO,
                sprintf('Nível abaixo de %g%%', Reservatorio::NIVEL_MINIMO),
                'Reduzir a vazão turbinada e comunicar o supervisor'
            ),
            Reservatorio::NIVEL_MUITO_ALTO => new Alerta(
                'NIVEL-ATENCAO',
                'Nível do reservatório',
                Alerta::ATENCAO,
                sprintf('Nível acima de %g%%', Reservatorio::NIVEL_MAXIMO),
                'Avaliar a abertura do vertedouro e comunicar o supervisor'
            ),
            default => null,
        };
    }

    private function alertaDeTemperatura(): ?Alerta
    {
        $turbina = $this->turbinaMaisQuente();
        $nome = $turbina->getNome();
        $temperatura = sprintf('%g', $turbina->getTemperatura());

        return match ($this->verificarTemperatura()) {
            Turbina::TEMP_ATENCAO => new Alerta(
                'TEMP-ATENCAO',
                'Temperatura',
                Alerta::ATENCAO,
                sprintf(
                    'Temperatura da %s em %s °C (entre %g °C e %g °C)',
                    $nome,
                    $temperatura,
                    Turbina::TEMPERATURA_ATENCAO,
                    Turbina::TEMPERATURA_CRITICA
                ),
                'Monitorar a turbina e verificar o sistema de refrigeração'
            ),
            Turbina::TEMP_CRITICO => new Alerta(
                'TEMP-CRITICO',
                'Temperatura',
                Alerta::CRITICO,
                sprintf(
                    'Temperatura da %s em %s °C (acima de %g °C)',
                    $nome,
                    $temperatura,
                    Turbina::TEMPERATURA_CRITICA
                ),
                'Reduzir a carga ou desligar a turbina e acionar a manutenção'
            ),
            default => null,
        };
    }

    private function alertaDeTurbinas(): ?Alerta
    {
        if ($this->verificarTurbina() !== Turbina::ESTADO_DESLIGADA) {
            return null;
        }

        $nomes = [];
        foreach ($this->turbinas as $turbina) {
            if (!$turbina->isLigada()) {
                $nomes[] = $turbina->getNome();
            }
        }

        $mensagem = count($nomes) === 1
            ? "{$nomes[0]} está desligada"
            : 'Turbinas desligadas: ' . implode(', ', $nomes);

        return new Alerta(
            'TURBINA-ATENCAO',
            'Turbina',
            Alerta::ATENCAO,
            $mensagem,
            'Verificar o motivo do desligamento e acionar a manutenção'
        );
    }
}