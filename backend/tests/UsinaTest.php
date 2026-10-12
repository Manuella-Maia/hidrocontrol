<?php

/**
 * UsinaTest.php
 *
 * Arquivo responsável por testar a classe Usina: cenários de alertas e
 * situação geral, conteúdo dos alertas, consultas, busca de turbina e
 * validações do construtor.
 *
 * @package test
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UsinaTest extends TestCase
{
    private function criarOperador(): Operador
    {
        return new Operador(
            'João',
            '(31) 90000-0000',
            'joao@hidrocontrol.test',
            '000.000.000-00',
            'Operação',
            password_hash('senha123', PASSWORD_BCRYPT, ['cost' => 4])
        );
    }

    /**
     * Monta uma usina com o nível informado e turbinas T-01, T-02... a partir
     * de pares [ligada, temperatura].
     *
     * @param array<int, array{0: bool, 1: float}> $dadosTurbinas
     */
    private function criarUsina(float $nivel, array $dadosTurbinas): Usina
    {
        $turbinas = [];
        foreach ($dadosTurbinas as $indice => [$ligada, $temperatura]) {
            $numero = sprintf('%02d', $indice + 1);
            $turbinas[] = new Turbina("T-{$numero}", "Turbina {$numero}", $ligada, $temperatura);
        }

        return new Usina(
            'Usina SENAI',
            $this->criarOperador(),
            65.8,
            new Reservatorio('R-01', $nivel, 420.0),
            $turbinas
        );
    }

    // ------------------------------------------------------------------
    // Cenários de teste (A, B e C) e valores de fronteira (RNF06)
    // ------------------------------------------------------------------

    /**
     * [nível, turbinas [ligada, temperatura], quantidade de alertas, situação geral]
     */
    public static function cenarios(): array
    {
        return [
            'A: operação normal' => [
                70.0, [[true, 65.0], [true, 62.0], [true, 60.0]], 0, 'NORMAL',
            ],
            'B: atenção' => [
                25.0, [[true, 78.0], [true, 70.0], [true, 65.0]], 2, 'ATENÇÃO',
            ],
            'C: crítico' => [
                95.0, [[true, 92.0], [false, 70.0], [true, 65.0]], 3, 'CRÍTICO',
            ],
            'nível 30 não gera alerta' => [
                30.0, [[true, 60.0]], 0, 'NORMAL',
            ],
            'nível 90 não gera alerta' => [
                90.0, [[true, 60.0]], 0, 'NORMAL',
            ],
            'nível 29.9 gera alerta' => [
                29.9, [[true, 60.0]], 1, 'ATENÇÃO',
            ],
            'nível 90.1 gera alerta' => [
                90.1, [[true, 60.0]], 1, 'ATENÇÃO',
            ],
            'temperatura 69.9 normal' => [
                60.0, [[true, 69.9]], 0, 'NORMAL',
            ],
            'temperatura 70 atenção' => [
                60.0, [[true, 70.0]], 1, 'ATENÇÃO',
            ],
            'temperatura 85 atenção' => [
                60.0, [[true, 85.0]], 1, 'ATENÇÃO',
            ],
            'temperatura 85.1 crítico' => [
                60.0, [[true, 85.1]], 1, 'CRÍTICO',
            ],
            'só turbina desligada' => [
                60.0, [[false, 60.0]], 1, 'ATENÇÃO',
            ],
        ];
    }

    #[DataProvider('cenarios')]
    public function testQuantidadeDeAlertasESituacaoGeral(
        float $nivel,
        array $dadosTurbinas,
        int $quantidadeAlertas,
        string $situacaoGeral
    ): void {
        $usina = $this->criarUsina($nivel, $dadosTurbinas);

        $this->assertCount($quantidadeAlertas, $usina->gerarAlertas());
        $this->assertSame($situacaoGeral, $usina->verificarSituacaoGeral());
    }

    public function testCenarioANaoGeraAlertas(): void
    {
        $usina = $this->criarUsina(70.0, [[true, 65.0], [true, 62.0], [true, 60.0]]);

        $this->assertSame([], $usina->gerarAlertas());
    }

    public function testCenarioCGeraOsTresAlertasNaOrdemEComAGravidadeCerta(): void
    {
        $usina = $this->criarUsina(95.0, [[true, 92.0], [false, 70.0], [true, 65.0]]);

        $alertas = $usina->gerarAlertas();

        $this->assertSame(
            ['NIVEL-ATENCAO', 'TEMP-CRITICO', 'TURBINA-ATENCAO'],
            array_map(fn (Alerta $a) => $a->getCodigo(), $alertas)
        );
        $this->assertSame(
            ['ATENÇÃO', 'CRÍTICO', 'ATENÇÃO'],
            array_map(fn (Alerta $a) => $a->getGravidade(), $alertas)
        );
    }

    // ------------------------------------------------------------------
    // Conteúdo dos alertas (tabela da seção 2.5)
    // ------------------------------------------------------------------

    public function testAlertaDeNivelBaixo(): void
    {
        $alerta = $this->criarUsina(25.0, [[true, 60.0]])->gerarAlertas()[0];

        $this->assertSame('Nível do reservatório', $alerta->getParametro());
        $this->assertSame('Nível abaixo de 30%', $alerta->getMensagem());
        $this->assertSame('Reduzir a vazão turbinada e comunicar o supervisor', $alerta->getAcaoRecomendada());
        $this->assertSame(
            'ATENÇÃO: Nível abaixo de 30%. Ação: Reduzir a vazão turbinada e comunicar o supervisor.',
            $alerta->texto()
        );
    }

    public function testAlertaDeNivelMuitoAlto(): void
    {
        $alerta = $this->criarUsina(95.0, [[true, 60.0]])->gerarAlertas()[0];

        $this->assertSame('Nível acima de 90%', $alerta->getMensagem());
        $this->assertSame('Avaliar a abertura do vertedouro e comunicar o supervisor', $alerta->getAcaoRecomendada());
    }

    public function testAlertaDeTemperaturaEmAtencao(): void
    {
        $alerta = $this->criarUsina(60.0, [[true, 78.0], [true, 65.0]])->gerarAlertas()[0];

        $this->assertSame('TEMP-ATENCAO', $alerta->getCodigo());
        $this->assertSame('Temperatura', $alerta->getParametro());
        $this->assertSame('Temperatura da Turbina 01 em 78 °C (entre 70 °C e 85 °C)', $alerta->getMensagem());
        $this->assertSame('Monitorar a turbina e verificar o sistema de refrigeração', $alerta->getAcaoRecomendada());
    }

    public function testAlertaDeTemperaturaCritica(): void
    {
        $alerta = $this->criarUsina(60.0, [[true, 92.0]])->gerarAlertas()[0];

        $this->assertSame('TEMP-CRITICO', $alerta->getCodigo());
        $this->assertSame('Temperatura da Turbina 01 em 92 °C (acima de 85 °C)', $alerta->getMensagem());
        $this->assertSame('Reduzir a carga ou desligar a turbina e acionar a manutenção', $alerta->getAcaoRecomendada());
    }

    public function testAlertaDeUmaTurbinaDesligada(): void
    {
        $alerta = $this->criarUsina(60.0, [[true, 60.0], [false, 60.0]])->gerarAlertas()[0];

        $this->assertSame('TURBINA-ATENCAO', $alerta->getCodigo());
        $this->assertSame('Turbina', $alerta->getParametro());
        $this->assertSame('Turbina 02 está desligada', $alerta->getMensagem());
        $this->assertSame('Verificar o motivo do desligamento e acionar a manutenção', $alerta->getAcaoRecomendada());
    }

    // public function testAlertaDeVariasTurbinasDesligadasContinuaSendoUmSo(): void
    // {
    //     $alertas = $this->criarUsina(60.0, [[false, 60.0], [true, 60.0], [false, 60.0]])->gerarAlertas();

    //     $this->assertCount(1, $alertas);
    //     $this->assertSame('Turbinas desligadas: Turbina 01, Turbina 03', $alertas[0]->getMensagem());
    // }

    // ------------------------------------------------------------------
    // Consultas e delegação
    // ------------------------------------------------------------------

    // public function testQuantidadeDeTurbinas(): void
    // {
    //     $this->assertSame(3, $this->criarUsina(60.0, [[true, 60.0], [true, 60.0], [true, 60.0]])->quantidadeTurbinas());
    // }

    public function testTemperaturaMaximaEntreAsTurbinas(): void
    {
        $usina = $this->criarUsina(60.0, [[true, 60.0], [true, 78.0], [true, 65.0]]);

        $this->assertSame(78.0, $usina->temperaturaMaxima());
    }

    // public function testVerificarTemperaturaUsaATurbinaMaisQuente(): void
    // {
    //     $usina = $this->criarUsina(60.0, [[true, 60.0], [true, 78.0], [true, 65.0]]);

    //     $this->assertSame('ATENÇÃO', $usina->verificarTemperatura());
    // }

    public function testAlertaDeTemperaturaCitaATurbinaMaisQuente(): void
    {
        $usina = $this->criarUsina(60.0, [[true, 60.0], [true, 78.0], [true, 65.0]]);

        $this->assertStringContainsString('Turbina 02', $usina->gerarAlertas()[0]->getMensagem());
    }

    // public function testVerificarNivelDelegaAoReservatorio(): void
    // {
    //     $this->assertSame('ALERTA: nível baixo', $this->criarUsina(25.0, [[true, 60.0]])->verificarNivel());
    //     $this->assertSame('NORMAL', $this->criarUsina(60.0, [[true, 60.0]])->verificarNivel());
    // }

    // public function testVerificarTurbinaComTodasLigadas(): void
    // {
    //     $usina = $this->criarUsina(60.0, [[true, 60.0], [true, 60.0]]);

    //     $this->assertSame('EM OPERAÇÃO', $usina->verificarTurbina());
    // }

    // public function testVerificarTurbinaComAoMenosUmaDesligada(): void
    // {
    //     $usina = $this->criarUsina(60.0, [[true, 60.0], [false, 60.0]]);

    //     $this->assertSame('DESLIGADA', $usina->verificarTurbina());
    // }

    // ------------------------------------------------------------------
    // buscarTurbina() e getters
    // ------------------------------------------------------------------

    public function testBuscarTurbinaPeloCodigo(): void
    {
        $usina = $this->criarUsina(60.0, [[true, 60.0], [true, 65.0]]);

        $turbina = $usina->buscarTurbina('T-02');

        $this->assertSame('T-02', $turbina->getCodigo());
        $this->assertSame(65.0, $turbina->getTemperatura());
    }

    // public function testBuscarTurbinaDevolveAMesmaInstanciaDaUsina(): void
    // {
    //     $usina = $this->criarUsina(60.0, [[true, 60.0], [true, 65.0]]);

    //     $this->assertSame($usina->getTurbinas()[1], $usina->buscarTurbina('T-02'));
    // }

    public function testBuscarTurbinaInexistenteLancaErro(): void
    {
        $usina = $this->criarUsina(60.0, [[true, 60.0]]);

        $this->expectException(OutOfBoundsException::class);

        $usina->buscarTurbina('T-99');
    }

    public function testGettersDevolvemOsValoresInformados(): void
    {
        $usina = $this->criarUsina(78.5, [[true, 72.0], [true, 70.0]]);

        $this->assertSame('Usina SENAI', $usina->getNome());
        $this->assertSame(65.8, $usina->getPotenciaGerada());
        $this->assertSame(78.5, $usina->getReservatorio()->getNivel());
        $this->assertCount(2, $usina->getTurbinas());
        $this->assertSame('João', $usina->getOperadorResponsavel()->getNome());
    }

    // ------------------------------------------------------------------
    // Fluxo do diagrama de sequência: operador desliga, alertas mudam
    // ------------------------------------------------------------------

    // public function testDesligarTurbinaPeloOperadorPassaAGerarAlertaDeTurbina(): void
    // {
    //     $usina = $this->criarUsina(60.0, [[true, 60.0], [true, 60.0]]);
    //     $this->assertSame('NORMAL', $usina->verificarSituacaoGeral());

    //     $registro = $usina->getOperadorResponsavel()->desligarTurbina($usina->buscarTurbina('T-02'));

    //     $this->assertSame(RegistroAcao::DESLIGAR_TURBINA, $registro->getAcao());
    //     $this->assertSame('T-02', $registro->getCodigoTurbina());
    //     $this->assertSame('ATENÇÃO', $usina->verificarSituacaoGeral());
    //     $this->assertSame('TURBINA-ATENCAO', $usina->gerarAlertas()[0]->getCodigo());
    // }

    // public function testLigarDeNovoAATurbinaVoltaAoNormal(): void
    // {
    //     $usina = $this->criarUsina(60.0, [[false, 60.0]]);
    //     $this->assertSame('ATENÇÃO', $usina->verificarSituacaoGeral());

    //     $usina->getOperadorResponsavel()->ligarTurbina($usina->buscarTurbina('T-01'));

    //     $this->assertSame('NORMAL', $usina->verificarSituacaoGeral());
    //     $this->assertSame([], $usina->gerarAlertas());
    // }

    // ------------------------------------------------------------------
    // Validações do construtor
    // ------------------------------------------------------------------

    public function testRejeitaNomeVazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Usina(' ', $this->criarOperador(), 65.8, new Reservatorio('R-01', 60.0, 420.0), [
            new Turbina('T-01', 'Turbina 01', true, 60.0),
        ]);
    }

    public function testRejeitaPotenciaNegativa(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Usina('Usina SENAI', $this->criarOperador(), -1.0, new Reservatorio('R-01', 60.0, 420.0), [
            new Turbina('T-01', 'Turbina 01', true, 60.0),
        ]);
    }

    public function testAceitaPotenciaZero(): void
    {
        $usina = new Usina('Usina SENAI', $this->criarOperador(), 0.0, new Reservatorio('R-01', 60.0, 420.0), [
            new Turbina('T-01', 'Turbina 01', false, 25.0),
        ]);

        $this->assertSame(0.0, $usina->getPotenciaGerada());
    }

    // public function testRejeitaUsinaSemTurbinas(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     new Usina('Usina SENAI', $this->criarOperador(), 65.8, new Reservatorio('R-01', 60.0, 420.0), []);
    // }

    // public function testRejeitaItemQueNaoEUmaTurbina(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     new Usina('Usina SENAI', $this->criarOperador(), 65.8, new Reservatorio('R-01', 60.0, 420.0), [
    //         'não sou uma turbina',
    //     ]);
    // }

    // public function testRejeitaCodigosDeTurbinaRepetidos(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     new Usina('Usina SENAI', $this->criarOperador(), 65.8, new Reservatorio('R-01', 60.0, 420.0), [
    //         new Turbina('T-01', 'Turbina 01', true, 60.0),
    //         new Turbina('T-01', 'Turbina 02', true, 60.0),
    //     ]);
    // }
}