<?php

/**
 * TurbinaTest.php
 *
 * Arquivo responsável por testar a classe Turbina: regra de temperatura nos
 * valores de fronteira, estado, ligar e desligar, getters e validações do
 * construtor.
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

// describe('Turbina')
class TurbinaTest extends TestCase
{
    // Método auxiliar (não é um teste): cria uma turbina pronta para uso, ligada e a 65 °C por padrão
    private function criarTurbina(bool $ligada = true, float $temperatura = 65.0): Turbina
    {
        return new Turbina('T-01', 'Turbina 01', $ligada, $temperatura);
    }

    /**
     * Valores de fronteira da regra de temperatura (RNF06): 70 e 85 são ATENÇÃO.
     */
    // Dados do teste abaixo: cada linha é [temperatura, situação esperada]
    public static function temperaturas(): array
    {
        return [
            'muito baixa'    => [20.0, 'NORMAL'],
            'logo abaixo 70' => [69.9, 'NORMAL'],
            'limite 70'      => [70.0, 'ATENÇÃO'],
            'meio da faixa'  => [78.0, 'ATENÇÃO'],
            'limite 85'      => [85.0, 'ATENÇÃO'],
            'logo acima 85'  => [85.1, 'CRÍTICO'],
            'muito alta'     => [120.0, 'CRÍTICO'],
        ];
    }

    // it('deve classificar a temperatura como NORMAL (abaixo de 70), ATENÇÃO (de 70 a 85) ou CRÍTICO (acima de 85)')
    // Roda uma vez para cada linha da lista "temperaturas"
    #[DataProvider('temperaturas')]
    public function testVerificarTemperatura(float $temperatura, string $esperado): void
    {
        $turbina = $this->criarTurbina(true, $temperatura);

        $this->assertSame($esperado, $turbina->verificarTemperatura());
    }

    // it('deve informar "EM OPERAÇÃO" quando a turbina está ligada')
    // public function testVerificarEstadoLigada(): void
    // {
    //     $this->assertSame('EM OPERAÇÃO', $this->criarTurbina(true)->verificarEstado());
    // }

    // it('deve informar "DESLIGADA" quando a turbina está desligada')
    // public function testVerificarEstadoDesligada(): void
    // {
    //     $this->assertSame('DESLIGADA', $this->criarTurbina(false)->verificarEstado());
    // }

    // it('deve devolver nos getters os mesmos valores informados no construtor')
    public function testGettersDevolvemOsValoresInformados(): void
    {
        $turbina = new Turbina('T-02', 'Turbina 02', true, 72.0);

        $this->assertSame('T-02', $turbina->getCodigo());
        $this->assertSame('Turbina 02', $turbina->getNome());
        $this->assertTrue($turbina->isLigada());
        $this->assertSame(72.0, $turbina->getTemperatura());
    }

    // it('deve mudar o estado de ligada para desligada ao desligar a turbina')
    public function testDesligarMudaOEstadoDeLigadaParaDesligada(): void
    {
        $turbina = $this->criarTurbina(true);

        $turbina->desligar();

        $this->assertFalse($turbina->isLigada());
        $this->assertSame('DESLIGADA', $turbina->verificarEstado());
    }

    // it('deve mudar o estado de desligada para ligada ao ligar a turbina')
    public function testLigarMudaOEstadoDeDesligadaParaLigada(): void
    {
        $turbina = $this->criarTurbina(false);

        $turbina->ligar();

        $this->assertTrue($turbina->isLigada());
        $this->assertSame('EM OPERAÇÃO', $turbina->verificarEstado());
    }

    // it('deve lançar LogicException ao desligar uma turbina que já está desligada')
    // public function testDesligarTurbinaJaDesligadaLancaErro(): void
    // {
    //     $turbina = $this->criarTurbina(false);

    //     $this->expectException(LogicException::class);

    //     $turbina->desligar();
    // }

    // it('deve lançar LogicException ao ligar uma turbina que já está ligada')
    // public function testLigarTurbinaJaLigadaLancaErro(): void
    // {
    //     $turbina = $this->criarTurbina(true);

    //     $this->expectException(LogicException::class);

    //     $turbina->ligar();
    // }

    // it('não deve alterar o estado da turbina quando a tentativa de desligar dá erro')
    // public function testErroAoDesligarNaoAlteraOEstado(): void
    // {
    //     $turbina = $this->criarTurbina(false);

    //     try {
    //         $turbina->desligar();
    //     } catch (LogicException $e) {
    //         // esperado
    //     }

    //     $this->assertFalse($turbina->isLigada());
    // }

    // it('deve rejeitar a criação de uma turbina com código vazio')
    public function testRejeitaCodigoVazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Turbina('', 'Turbina 01', true, 65.0);
    }

    // it('deve rejeitar a criação de uma turbina com nome vazio ou só de espaços')
    public function testRejeitaNomeVazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Turbina('T-01', '   ', true, 65.0);
    }
}