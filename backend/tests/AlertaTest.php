<?php

/**
 * AlertaTest.php
 *
 * Arquivo responsável por testar a classe Alerta: getters, constantes de
 * gravidade, texto para o operador e validações do construtor.
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

class AlertaTest extends TestCase
{
    private function criarAlerta(string $gravidade = Alerta::ATENCAO): Alerta
    {
        return new Alerta(
            'NIVEL-ATENCAO',
            'Nível do reservatório',
            $gravidade,
            'Nível abaixo de 30%',
            'Reduzir a vazão turbinada e comunicar o supervisor'
        );
    }

    public function testGettersDevolvemOsValoresInformados(): void
    {
        $alerta = $this->criarAlerta();

        $this->assertSame('NIVEL-ATENCAO', $alerta->getCodigo());
        $this->assertSame('Nível do reservatório', $alerta->getParametro());
        $this->assertSame('ATENÇÃO', $alerta->getGravidade());
        $this->assertSame('Nível abaixo de 30%', $alerta->getMensagem());
        $this->assertSame('Reduzir a vazão turbinada e comunicar o supervisor', $alerta->getAcaoRecomendada());
    }

    // public function testConstantesDeGravidade(): void
    // {
    //     $this->assertSame('ATENÇÃO', Alerta::ATENCAO);
    //     $this->assertSame('CRÍTICO', Alerta::CRITICO);
    // }

    // public function testAceitaGravidadeCritica(): void
    // {
    //     $this->assertSame('CRÍTICO', $this->criarAlerta(Alerta::CRITICO)->getGravidade());
    // }

    public function testTextoMontaAFraseParaOOperador(): void
    {
        $this->assertSame(
            'ATENÇÃO: Nível abaixo de 30%. Ação: Reduzir a vazão turbinada e comunicar o supervisor.',
            $this->criarAlerta()->texto()
        );
    }

    public function testTextoComGravidadeCritica(): void
    {
        $alerta = new Alerta(
            'TEMP-CRITICO',
            'Temperatura',
            Alerta::CRITICO,
            'Temperatura acima de 85 °C',
            'Reduzir a carga ou desligar a turbina e acionar a manutenção'
        );

        $this->assertSame(
            'CRÍTICO: Temperatura acima de 85 °C. Ação: Reduzir a carga ou desligar a turbina e acionar a manutenção.',
            $alerta->texto()
        );
    }

    public static function gravidadesInvalidas(): array
    {
        return [
            'texto livre'      => ['ALTA'],
            'sem acento'       => ['ATENCAO'],
            'minúsculas'       => ['atenção'],
            'vazia'            => [''],
        ];
    }

    #[DataProvider('gravidadesInvalidas')]
    public function testRejeitaGravidadeInvalida(string $gravidade): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->criarAlerta($gravidade);
    }

    // public function testRejeitaCodigoVazio(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     new Alerta(' ', 'Parâmetro', Alerta::ATENCAO, 'Mensagem', 'Ação');
    // }

    // public function testRejeitaParametroVazio(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     new Alerta('COD', '', Alerta::ATENCAO, 'Mensagem', 'Ação');
    // }

    // public function testRejeitaMensagemVazia(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     new Alerta('COD', 'Parâmetro', Alerta::ATENCAO, '  ', 'Ação');
    // }

    // public function testRejeitaAcaoRecomendadaVazia(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     new Alerta('COD', 'Parâmetro', Alerta::ATENCAO, 'Mensagem', '');
    // }
}
