<?php

/**
 * UsinaRepositorySimuladoTest.php
 *
 * Arquivo responsável por testar o UsinaRepositorySimulado: cenários A, B e C,
 * operador responsável, cenários inválidos e independência dos objetos
 * devolvidos a cada chamada.
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

class UsinaRepositorySimuladoTest extends TestCase
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

    private function criarRepository(): UsinaRepositorySimulado
    {
        return new UsinaRepositorySimulado();
    }

    public function testImplementaAInterfaceDeRepository(): void
    {
        $this->assertInstanceOf(UsinaRepositoryInterface::class, $this->criarRepository());
    }

    /**
     * [cenário, situação geral, quantidade de alertas, nível, turbinas desligadas]
     */
    public static function cenarios(): array
    {
        return [
            'A: operação normal' => ['A', 'NORMAL', 0, 70.0, 0],
            'B: atenção'         => ['B', 'ATENÇÃO', 2, 25.0, 0],
            'C: crítico'         => ['C', 'CRÍTICO', 3, 95.0, 1],
        ];
    }

    #[DataProvider('cenarios')]
    public function testCenarioProduzOResultadoEsperado(
        string $cenario,
        string $situacaoGeral,
        int $quantidadeAlertas,
        float $nivel,
        int $desligadas
    ): void {
        $usina = $this->criarRepository()->obterUsina($cenario, $this->criarOperador());

        $this->assertSame($situacaoGeral, $usina->verificarSituacaoGeral());
        $this->assertCount($quantidadeAlertas, $usina->gerarAlertas());
        $this->assertSame($nivel, $usina->getReservatorio()->getNivel());
        $this->assertSame(3, $usina->quantidadeTurbinas());

        $quantidadeDesligadas = count(array_filter(
            $usina->getTurbinas(),
            fn (Turbina $t) => !$t->isLigada()
        ));
        $this->assertSame($desligadas, $quantidadeDesligadas);
    }

    public function testCenarioANaoTemAlertas(): void
    {
        $usina = $this->criarRepository()->obterUsina('A', $this->criarOperador());

        $this->assertSame([], $usina->gerarAlertas());
        $this->assertSame('EM OPERAÇÃO', $usina->verificarTurbina());
    }

    public function testCenarioCTemOsTresAlertas(): void
    {
        $usina = $this->criarRepository()->obterUsina('C', $this->criarOperador());

        $this->assertSame(
            ['NIVEL-ATENCAO', 'TEMP-CRITICO', 'TURBINA-ATENCAO'],
            array_map(fn (Alerta $a) => $a->getCodigo(), $usina->gerarAlertas())
        );
        $this->assertSame('Turbina 02 está desligada', $usina->gerarAlertas()[2]->getMensagem());
    }

    public function testOperadorInformadoEOResponsavelPelaUsina(): void
    {
        $operador = $this->criarOperador();

        $usina = $this->criarRepository()->obterUsina('A', $operador);

        $this->assertSame($operador, $usina->getOperadorResponsavel());
    }

    public function testDadosGeraisDaUsina(): void
    {
        $usina = $this->criarRepository()->obterUsina('A', $this->criarOperador());

        $this->assertSame('Usina SENAI', $usina->getNome());
        $this->assertSame(65.8, $usina->getPotenciaGerada());
        $this->assertSame(420.0, $usina->getReservatorio()->getVazao());
        $this->assertSame('T-01', $usina->getTurbinas()[0]->getCodigo());
        $this->assertSame('Turbina 03', $usina->buscarTurbina('T-03')->getNome());
    }

    public function testAceitaCenarioEmMinusculasEComEspacos(): void
    {
        $usina = $this->criarRepository()->obterUsina(' b ', $this->criarOperador());

        $this->assertSame('ATENÇÃO', $usina->verificarSituacaoGeral());
    }

    public static function cenariosInvalidos(): array
    {
        return [
            'inexistente' => ['D'],
            'vazio'       => [''],
            'número'      => ['1'],
            'texto'       => ['normal'],
        ];
    }

    #[DataProvider('cenariosInvalidos')]
    public function testRejeitaCenarioInvalido(string $cenario): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->criarRepository()->obterUsina($cenario, $this->criarOperador());
    }

    public function testCadaChamadaDevolveObjetosIndependentes(): void
    {
        $repository = $this->criarRepository();
        $operador = $this->criarOperador();

        $primeira = $repository->obterUsina('A', $operador);
        $operador->desligarTurbina($primeira->buscarTurbina('T-02'));
        $segunda = $repository->obterUsina('A', $operador);

        $this->assertNotSame($primeira, $segunda);
        $this->assertFalse($primeira->buscarTurbina('T-02')->isLigada());
        $this->assertTrue($segunda->buscarTurbina('T-02')->isLigada());
        $this->assertSame('NORMAL', $segunda->verificarSituacaoGeral());
    }

    public function testListaDeCenariosDisponiveis(): void
    {
        $this->assertSame(['A', 'B', 'C'], UsinaRepositorySimulado::CENARIOS);
    }
}
