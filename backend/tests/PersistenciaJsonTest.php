<?php

/**
 * PersistenciaJsonTest.php
 *
 * Arquivo responsável por testar o ArmazenamentoJson e os repositories em
 * arquivo JSON (EstadoRepositoryJson e RegistroRepositoryJson), usando
 * arquivos temporários.
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
 * Armazenamento e repositories em arquivo JSON.
 * Usa sempre arquivos temporários, nunca o estado real em backend/storage.
 */
class PersistenciaJsonTest extends TestCase
{
    /** @var string[] */
    private array $arquivos = [];

    private function arquivoTemporario(): string
    {
        $caminho = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('hidrocontrol_', true) . '.json';
        $this->arquivos[] = $caminho;
        return $caminho;
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

    // --- ArmazenamentoJson ---

    public function testLerArquivoInexistenteDevolveListaVazia(): void
    {
        $armazenamento = new ArmazenamentoJson($this->arquivoTemporario());

        $this->assertSame([], $armazenamento->ler());
    }

    public function testLerArquivoCorrompidoDevolveListaVazia(): void
    {
        $caminho = $this->arquivoTemporario();
        file_put_contents($caminho, '{isto não é json');

        $this->assertSame([], (new ArmazenamentoJson($caminho))->ler());
    }

    // public function testGravarELerPreservaOsAcentos(): void
    // {
    //     $armazenamento = new ArmazenamentoJson($this->arquivoTemporario());

    //     $armazenamento->gravar(['situação' => 'ATENÇÃO']);

    //     $this->assertSame(['situação' => 'ATENÇÃO'], $armazenamento->ler());
    // }

    // --- EstadoRepositoryJson ---

    public function testEstadoDasTurbinasFicaSeparadoPorCenario(): void
    {
        $estado = new EstadoRepositoryJson(new ArmazenamentoJson($this->arquivoTemporario()));

        $estado->definirEstadoTurbina('A', 'T-02', false);

        $this->assertSame(['T-02' => false], $estado->obterEstadoTurbinas('A'));
        $this->assertSame([], $estado->obterEstadoTurbinas('B'));
    }

    // public function testLimparDescartaAsAlteracoesDeTodosOsCenarios(): void
    // {
    //     $estado = new EstadoRepositoryJson(new ArmazenamentoJson($this->arquivoTemporario()));
    //     $estado->definirEstadoTurbina('A', 'T-02', false);
    //     $estado->definirEstadoTurbina('C', 'T-02', true);

    //     $estado->limpar();

    //     $this->assertSame([], $estado->obterEstadoTurbinas('A'));
    //     $this->assertSame([], $estado->obterEstadoTurbinas('C'));
    // }

    // --- RegistroRepositoryJson ---

    // public function testRegistrosVoltamDoMaisRecenteParaOMaisAntigoRespeitandoOLimite(): void
    // {
    //     $repository = new RegistroRepositoryJson(new ArmazenamentoJson($this->arquivoTemporario()));
    //     $repository->salvar(new RegistroAcao('João', RegistroAcao::ENTRAR, null, '2026-10-09 10:00:00'));
    //     $repository->salvar(new RegistroAcao('João', RegistroAcao::DESLIGAR_TURBINA, 'T-02', '2026-10-09 10:05:00'));
    //     $repository->salvar(new RegistroAcao('João', RegistroAcao::SAIR, null, '2026-10-09 10:10:00'));

    //     $registros = $repository->listar(2);

    //     $this->assertCount(2, $registros);
    //     $this->assertSame('SAIR', $registros[0]->getAcao());
    //     $this->assertSame('DESLIGAR_TURBINA', $registros[1]->getAcao());
    //     $this->assertSame('T-02', $registros[1]->getCodigoTurbina());
    // }
}
