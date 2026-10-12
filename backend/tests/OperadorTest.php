<?php

/**
 * OperadorTest.php
 *
 * Arquivo responsável por testar a classe Operador: ligar e desligar
 * turbinas, registro da ação e erros quando a turbina já está no estado pedido.
 *
 * @package test
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class OperadorTest extends TestCase
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

    private function criarTurbina(bool $ligada): Turbina
    {
        return new Turbina('T-02', 'Turbina 02', $ligada, 65.0);
    }

    public function testDesligarTurbinaLigadaMudaOEstado(): void
    {
        $turbina = $this->criarTurbina(true);

        $this->criarOperador()->desligarTurbina($turbina);

        $this->assertFalse($turbina->isLigada());
    }

    public function testDesligarTurbinaDevolveORegistroDaAcao(): void
    {
        $turbina = $this->criarTurbina(true);

        $registro = $this->criarOperador()->desligarTurbina($turbina);

        $this->assertInstanceOf(RegistroAcao::class, $registro);
        $this->assertSame('João', $registro->getOperador());
        $this->assertSame(RegistroAcao::DESLIGAR_TURBINA, $registro->getAcao());
        $this->assertSame('T-02', $registro->getCodigoTurbina());
    }

    public function testLigarTurbinaDesligadaMudaOEstado(): void
    {
        $turbina = $this->criarTurbina(false);

        $this->criarOperador()->ligarTurbina($turbina);

        $this->assertTrue($turbina->isLigada());
    }

    public function testLigarTurbinaDevolveORegistroDaAcao(): void
    {
        $turbina = $this->criarTurbina(false);

        $registro = $this->criarOperador()->ligarTurbina($turbina);

        $this->assertInstanceOf(RegistroAcao::class, $registro);
        $this->assertSame('João', $registro->getOperador());
        $this->assertSame(RegistroAcao::LIGAR_TURBINA, $registro->getAcao());
        $this->assertSame('T-02', $registro->getCodigoTurbina());
    }

    public function testDesligarTurbinaJaDesligadaLancaErro(): void
    {
        $turbina = $this->criarTurbina(false);

        $this->expectException(LogicException::class);

        $this->criarOperador()->desligarTurbina($turbina);
    }

    // public function testLigarTurbinaJaLigadaLancaErro(): void
    // {
    //     $turbina = $this->criarTurbina(true);

    //     $this->expectException(LogicException::class);

    //     $this->criarOperador()->ligarTurbina($turbina);
    // }

    // public function testErroNaoGeraRegistroNemAlteraAturbina(): void
    // {
    //     $turbina = $this->criarTurbina(false);
    //     $registro = null;

    //     try {
    //         $registro = $this->criarOperador()->desligarTurbina($turbina);
    //     } catch (LogicException $e) {
    //         // esperado
    //     }

    //     $this->assertNull($registro);
    //     $this->assertFalse($turbina->isLigada());
    // }
}
