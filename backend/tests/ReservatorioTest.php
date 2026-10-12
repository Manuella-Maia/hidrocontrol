<?php

/**
 * ReservatorioTest.php
 *
 * Arquivo responsável por testar a classe Reservatorio: regra de nível nos
 * valores de fronteira, getters e validações do construtor.
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

class ReservatorioTest extends TestCase
{
    /**
     * Valores de fronteira da regra de nível (RNF06): 30 e 90 são NORMAL.
     */
    public static function niveis(): array
    {
        return [
            'zero'          => [0.0, 'ALERTA: nível baixo'],
            'logo abaixo 30' => [29.9, 'ALERTA: nível baixo'],
            'limite 30'     => [30.0, 'NORMAL'],
            'meio da faixa' => [60.0, 'NORMAL'],
            'limite 90'     => [90.0, 'NORMAL'],
            'logo acima 90' => [90.1, 'ALERTA: nível muito alto'],
            'cem'           => [100.0, 'ALERTA: nível muito alto'],
        ];
    }

    #[DataProvider('niveis')]
    public function testVerificarNivel(float $nivel, string $esperado): void
    {
        $reservatorio = new Reservatorio('R-01', $nivel, 420.0);

        $this->assertSame($esperado, $reservatorio->verificarNivel());
    }

    public function testGettersDevolvemOsValoresInformados(): void
    {
        $reservatorio = new Reservatorio('R-01', 78.5, 420.0);

        $this->assertSame('R-01', $reservatorio->getCodigo());
        $this->assertSame(78.5, $reservatorio->getNivel());
        $this->assertSame(420.0, $reservatorio->getVazao());
    }

    public function testAceitaOsLimitesDaFaixaValida(): void
    {
        $this->assertSame(0.0, (new Reservatorio('R-01', 0.0, 0.0))->getNivel());
        $this->assertSame(100.0, (new Reservatorio('R-01', 100.0, 0.0))->getNivel());
    }

    public function testRejeitaNivelAbaixoDeZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Reservatorio('R-01', -0.1, 420.0);
    }

    public function testRejeitaNivelAcimaDeCem(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Reservatorio('R-01', 100.1, 420.0);
    }

    public function testRejeitaVazaoNegativa(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Reservatorio('R-01', 50.0, -1.0);
    }

    public function testRejeitaCodigoVazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Reservatorio('  ', 50.0, 420.0);
    }
}
