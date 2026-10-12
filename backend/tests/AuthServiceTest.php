<?php

/**
 * AuthServiceTest.php
 *
 * Arquivo responsável por testar o AuthService: credenciais, sessão do
 * operador e registro de entrada e saída no histórico.
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
 * Só o que tem risco: credenciais, sessão e registro de entrada e saída.
 * Controller e rotas são conferidos com curl.
 */
class AuthServiceTest extends TestCase
{
    private string $arquivo;
    private RegistroRepositoryJson $registroRepository;
    private AuthService $auth;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->arquivo = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('hidrocontrol_', true) . '.json';
        $this->registroRepository = new RegistroRepositoryJson(new ArmazenamentoJson($this->arquivo));
        $this->auth = new AuthService(
            new UsuarioRepositorySimulado(),
            new RegistroService($this->registroRepository)
        );
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (is_file($this->arquivo)) {
            unlink($this->arquivo);
        }
    }

    public function testEntrarComCredenciaisValidasAbreASessaoERegistraAEntrada(): void
    {
        $operador = $this->auth->entrar('joao@hidrocontrol.test', 'senha123');

        $this->assertNotNull($operador);
        $this->assertSame('João Silva', $operador->getNome());
        $this->assertSame('João Silva', $this->auth->operadorLogado()?->getNome());

        $registros = $this->registroRepository->listar(10);
        $this->assertCount(1, $registros);
        $this->assertSame(RegistroAcao::ENTRAR, $registros[0]->getAcao());
        $this->assertSame('João Silva', $registros[0]->getOperador());
    }

    public function testSenhaErradaNaoAbreSessaoNemRegistra(): void
    {
        $this->assertNull($this->auth->entrar('joao@hidrocontrol.test', 'errada'));
        $this->assertNull($this->auth->operadorLogado());
        $this->assertSame([], $this->registroRepository->listar(10));
    }

    public function testEmailInexistenteNaoAbreSessao(): void
    {
        $this->assertNull($this->auth->entrar('ninguem@hidrocontrol.test', 'senha123'));
        $this->assertNull($this->auth->operadorLogado());
    }

    // public function testEmailNaoDiferenciaMaiusculasDeMinusculas(): void
    // {
    //     $this->assertNotNull($this->auth->entrar('JOAO@HIDROCONTROL.TEST', 'senha123'));
    // }

    public function testSairEncerraASessaoERegistraASaida(): void
    {
        $this->auth->entrar('maria@hidrocontrol.test', 'senha123');

        $this->auth->sair();

        $this->assertNull($this->auth->operadorLogado());
        $registros = $this->registroRepository->listar(10);
        $this->assertSame(RegistroAcao::SAIR, $registros[0]->getAcao());
        $this->assertSame('Maria Souza', $registros[0]->getOperador());
    }

    // public function testSairSemLoginNaoFazNadaNemRegistra(): void
    // {
    //     $this->auth->sair();

    //     $this->assertNull($this->auth->operadorLogado());
    //     $this->assertSame([], $this->registroRepository->listar(10));
    // }

    // public function testSessaoAdulteradaNaoAutentica(): void
    // {
    //     $_SESSION['operador_email'] = 'invasor@exemplo.com';
    //     $this->assertNull($this->auth->operadorLogado());

    //     $_SESSION['operador_email'] = 123;
    //     $this->assertNull($this->auth->operadorLogado());
    // }
}
