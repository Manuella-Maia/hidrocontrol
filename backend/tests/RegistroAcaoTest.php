<?php

/**
 * RegistroAcaoTest.php
 *
 * Arquivo responsável por testar a classe RegistroAcao: getters, data e hora,
 * texto que descreve a ação e validações do construtor.
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

class RegistroAcaoTest extends TestCase
{
    private const DATA_FIXA = '2026-10-09 14:32:10';

    public function testGettersDevolvemOsValoresInformados(): void
    {
        $registro = new RegistroAcao('João', RegistroAcao::DESLIGAR_TURBINA, 'T-02', self::DATA_FIXA);

        $this->assertSame('João', $registro->getOperador());
        $this->assertSame('DESLIGAR_TURBINA', $registro->getAcao());
        $this->assertSame('T-02', $registro->getCodigoTurbina());
        $this->assertSame(self::DATA_FIXA, $registro->getDataHora());
    }

    // public function testCodigoDaTurbinaEOpcionalParaAcoesSemTurbina(): void
    // {
    //     $registro = new RegistroAcao('João', RegistroAcao::ENTRAR, null, self::DATA_FIXA);

    //     $this->assertNull($registro->getCodigoTurbina());
    // }

    // public function testDataEHoraAtuaisQuandoOmitidas(): void
    // {
    //     $antes = time();
    //     $registro = new RegistroAcao('João', RegistroAcao::ENTRAR);
    //     $depois = time();

    //     $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $registro->getDataHora());
    //     $momento = strtotime($registro->getDataHora());
    //     $this->assertGreaterThanOrEqual($antes, $momento);
    //     $this->assertLessThanOrEqual($depois, $momento);
    // }

    public static function textos(): array
    {
        return [
            'ligar turbina'       => [RegistroAcao::LIGAR_TURBINA, 'T-01', 'João ligou a turbina T-01'],
            'desligar turbina'    => [RegistroAcao::DESLIGAR_TURBINA, 'T-02', 'João desligou a turbina T-02'],
            'entrar'              => [RegistroAcao::ENTRAR, null, 'João entrou no sistema'],
            'sair'                => [RegistroAcao::SAIR, null, 'João saiu do sistema'],
            'gerar relatório'     => [RegistroAcao::GERAR_RELATORIO, null, 'João gerou o relatório'],
            'restaurar simulação' => [RegistroAcao::RESTAURAR_SIMULACAO, null, 'João restaurou a simulação'],
        ];
    }

    #[DataProvider('textos')]
    public function testTextoDescreveACao(string $acao, ?string $codigoTurbina, string $descricao): void
    {
        $registro = new RegistroAcao('João', $acao, $codigoTurbina, self::DATA_FIXA);

        $this->assertSame('09/10 14:32 – ' . $descricao, $registro->texto());
    }

    public function testRejeitaOperadorVazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegistroAcao('  ', RegistroAcao::ENTRAR);
    }

    public function testRejeitaAcaoInvalida(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegistroAcao('João', 'EXPLODIR_USINA');
    }

    public function testRejeitaCodigoDeTurbinaVazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegistroAcao('João', RegistroAcao::DESLIGAR_TURBINA, '  ');
    }

    public function testLigarExigeCodigoDaTurbina(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegistroAcao('João', RegistroAcao::LIGAR_TURBINA);
    }

    public function testDesligarExigeCodigoDaTurbina(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegistroAcao('João', RegistroAcao::DESLIGAR_TURBINA);
    }

    public static function datasInvalidas(): array
    {
        return [
            'texto qualquer'  => ['ontem'],
            'formato errado'  => ['09/10/2026 14:32'],
            'mês inexistente' => ['2026-13-01 10:00:00'],
            'dia inexistente' => ['2026-02-30 10:00:00'],
        ];
    }

    #[DataProvider('datasInvalidas')]
    public function testRejeitaDataEHoraInvalidas(string $dataHora): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegistroAcao('João', RegistroAcao::ENTRAR, null, $dataHora);
    }
}
