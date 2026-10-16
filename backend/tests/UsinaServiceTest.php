<?php

/**
 * UsinaServiceTest.php
 *
 * Arquivo responsável por testar o UsinaService: conteúdo da leitura, dados
 * que não podem vazar e o fluxo de ligar, desligar e restaurar a simulação.
 *
 * @package test
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Só o que tem regra ou risco: o conteúdo da leitura, o que NÃO pode vazar e o
 * fluxo de ligar/desligar (estado e registro só gravados quando a ação dá certo).
 * Controllers e rotas são conferidos no navegador.
 */
class UsinaServiceTest extends TestCase
{
    /** @var string[] */
    private array $arquivos = [];

    private EstadoRepositoryJson $estado;
    private RegistroRepositoryJson $registroRepository;

    protected function setUp(): void
    {
        $this->estado = new EstadoRepositoryJson(new ArmazenamentoJson($this->arquivoTemporario()));
        $this->registroRepository = new RegistroRepositoryJson(new ArmazenamentoJson($this->arquivoTemporario()));
    }

    protected function tearDown(): void
    {
        foreach ($this->arquivos as $arquivo) {
            if (is_file($arquivo)) {
                unlink($arquivo);
            }
        }
        $this->arquivos = [];
    }

    private function arquivoTemporario(): string
    {
        $caminho = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('hidrocontrol_', true) . '.json';
        $this->arquivos[] = $caminho;
        return $caminho;
    }

    private function criarOperador(): Operador
    {
        return new Operador(
            'João Silva',
            '(31) 90000-0001',
            'joao@hidrocontrol.test',
            '000.000.000-01',
            'Operação',
            password_hash('senha123', PASSWORD_BCRYPT, ['cost' => 4])
        );
    }

    /**
     * Cada chamada cria um service novo sobre os MESMOS arquivos, como acontece
     * entre duas requisições HTTP.
     */
    private function criarService(): UsinaService
    {
        return new UsinaService(
            new UsinaRepositorySimulado($this->estado),
            $this->estado,
            new RegistroService($this->registroRepository)
        );
    }

    // ------------------------------------------------------------------
    // Leitura
    // ------------------------------------------------------------------

    public function testLeituraDoCenarioCTemOsDadosEsperados(): void
    {
        $leitura = $this->criarService()->obterLeitura('C', $this->criarOperador());

        $this->assertSame('C', $leitura['cenario']);
        $this->assertSame(
            ['nivelMinimo' => 30.0, 'nivelMaximo' => 90.0, 'temperaturaAtencao' => 70.0, 'temperaturaCritica' => 85.0],
            $leitura['limites']
        );
        $this->assertSame('CRÍTICO', $leitura['situacaoGeral']);
        $this->assertSame('ALERTA: nível muito alto', $leitura['reservatorio']['situacao']);
        $this->assertSame(3, $leitura['resumoTurbinas']['quantidade']);
        $this->assertSame(92.0, $leitura['resumoTurbinas']['temperaturaMaxima']);
        $this->assertSame('DESLIGADA', $leitura['resumoTurbinas']['estado']);
        $this->assertCount(3, $leitura['turbinas']);
        $this->assertSame('DESLIGADA', $leitura['turbinas'][1]['estado']);
        $this->assertCount(3, $leitura['alertas']);
        $this->assertSame('TEMP-CRITICO', $leitura['alertas'][1]['codigo']);
    }

    public function testCenarioASemAlertasVirandoJsonComListaVazia(): void
    {
        $leitura = $this->criarService()->obterLeitura('a', $this->criarOperador());

        $this->assertSame('A', $leitura['cenario']);
        $this->assertSame('NORMAL', $leitura['situacaoGeral']);
        $this->assertStringContainsString('"alertas":[]', json_encode($leitura));
    }

    public function testLeituraNaoExpoeDadosPessoaisDoOperador(): void
    {
        $leitura = $this->criarService()->obterLeitura('A', $this->criarOperador());

        $this->assertSame(['nome', 'setor'], array_keys($leitura['usina']['operador']));

        $json = json_encode($leitura);
        $this->assertStringNotContainsString('000.000.000-01', $json);
        $this->assertStringNotContainsString('joao@hidrocontrol.test', $json);
        $this->assertStringNotContainsString('90000-0001', $json);
        $this->assertStringNotContainsString('$2y$', $json);
    }

    // ------------------------------------------------------------------
    // Ligar, desligar e restaurar (fluxo do diagrama de sequência)
    // ------------------------------------------------------------------

    public function testDesligarTurbinaAtualizaALeituraERegistraAAcao(): void
    {
        $leitura = $this->criarService()->alterarEstadoTurbina('A', 'T-02', false, $this->criarOperador());

        $this->assertSame('ATENÇÃO', $leitura['situacaoGeral']);
        $this->assertSame('DESLIGADA', $leitura['turbinas'][1]['estado']);
        $this->assertSame('TURBINA-ATENCAO', $leitura['alertas'][0]['codigo']);
        $this->assertStringContainsString('João Silva desligou a turbina T-02', $leitura['registro']);

        $registros = $this->criarService()->obterRegistros();
        $this->assertCount(1, $registros);
        $this->assertSame('DESLIGAR_TURBINA', $registros[0]['acao']);
        $this->assertSame('T-02', $registros[0]['codigoTurbina']);
    }

    public function testAlteracaoPersisteEntreRequisicoesSoNoCenarioAlterado(): void
    {
        $this->criarService()->alterarEstadoTurbina('A', 'T-02', false, $this->criarOperador());

        $outraRequisicaoA = $this->criarService()->obterLeitura('A', $this->criarOperador());
        $outraRequisicaoB = $this->criarService()->obterLeitura('B', $this->criarOperador());

        $this->assertSame('DESLIGADA', $outraRequisicaoA['turbinas'][1]['estado']);
        $this->assertSame('EM OPERAÇÃO', $outraRequisicaoB['turbinas'][1]['estado']);
    }

    public function testLigarTurbinaDesligadaDoCenarioCRemoveOAlertaDeTurbina(): void
    {
        $leitura = $this->criarService()->alterarEstadoTurbina('C', 'T-02', true, $this->criarOperador());

        $this->assertSame(
            ['NIVEL-ATENCAO', 'TEMP-CRITICO'],
            array_column($leitura['alertas'], 'codigo')
        );
        $this->assertSame('EM OPERAÇÃO', $leitura['resumoTurbinas']['estado']);
    }

    public function testDesligarTurbinaJaDesligadaNaoGravaEstadoNemRegistro(): void
    {
        try {
            $this->criarService()->alterarEstadoTurbina('C', 'T-02', false, $this->criarOperador());
            $this->fail('Era esperado um LogicException.');
        } catch (LogicException $e) {
            // esperado: a turbina T-02 já começa desligada no cenário C
        }

        $this->assertSame([], $this->estado->obterEstadoTurbinas('C'));
        $this->assertSame([], $this->criarService()->obterRegistros());
    }

    public function testTurbinaInexistenteLancaErro(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $this->criarService()->alterarEstadoTurbina('A', 'T-99', false, $this->criarOperador());
    }

    public function testRestaurarSimulacaoDesfazAsAlteracoesERegistraAAcao(): void
    {
        $this->criarService()->alterarEstadoTurbina('A', 'T-02', false, $this->criarOperador());

        $this->criarService()->restaurarSimulacao($this->criarOperador());

        $leitura = $this->criarService()->obterLeitura('A', $this->criarOperador());
        $this->assertSame('NORMAL', $leitura['situacaoGeral']);
        $this->assertSame('RESTAURAR_SIMULACAO', $this->criarService()->obterRegistros()[0]['acao']);
    }
}
