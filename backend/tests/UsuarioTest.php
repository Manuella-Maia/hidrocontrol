<?php

/**
 * UsuarioTest.php
 *
 * Arquivo responsável por testar a classe abstrata Usuario, usando Operador
 * como implementação concreta: getters, verificação de senha e validações do
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

/**
 * Usuario é abstrata, então os testes usam Operador como implementação concreta.
 */
class UsuarioTest extends TestCase
{
    private const SENHA = 'senha123';

    private function hash(string $senha = self::SENHA): string
    {
        // custo baixo só para os testes rodarem rápido
        return password_hash($senha, PASSWORD_BCRYPT, ['cost' => 4]);
    }

    private function criarOperador(): Operador
    {
        return new Operador(
            'João',
            '(31) 90000-0000',
            'joao@hidrocontrol.test',
            '000.000.000-00',
            'Operação',
            $this->hash()
        );
    }

    // public function testUsuarioEAbstrata(): void
    // {
    //     $this->assertTrue((new ReflectionClass(Usuario::class))->isAbstract());
    // }

    // public function testOperadorHerdaDeUsuario(): void
    // {
    //     $this->assertInstanceOf(Usuario::class, $this->criarOperador());
    // }

    public function testGettersDevolvemOsValoresInformados(): void
    {
        $operador = $this->criarOperador();

        $this->assertSame('João', $operador->getNome());
        $this->assertSame('(31) 90000-0000', $operador->getTelefone());
        $this->assertSame('joao@hidrocontrol.test', $operador->getEmail());
        $this->assertSame('000.000.000-00', $operador->getCpf());
        $this->assertSame('Operação', $operador->getSetor());
    }

    public function testVerificarSenhaCorreta(): void
    {
        $this->assertTrue($this->criarOperador()->verificarSenha(self::SENHA));
    }

    public function testVerificarSenhaErrada(): void
    {
        $this->assertFalse($this->criarOperador()->verificarSenha('outra-senha'));
    }

    public function testVerificarSenhaVazia(): void
    {
        $this->assertFalse($this->criarOperador()->verificarSenha(''));
    }

    // public function testHashDaSenhaNaoTemGetter(): void
    // {
    //     $this->assertFalse(method_exists(Usuario::class, 'getSenhaHash'));
    // }

    public function testRejeitaSenhaEmTextoPuro(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Operador('João', '', 'joao@hidrocontrol.test', '', 'Operação', 'senha123');
    }

    public function testRejeitaNomeVazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Operador('  ', '', 'joao@hidrocontrol.test', '', 'Operação', $this->hash());
    }

    public static function emailsInvalidos(): array
    {
        return [
            'vazio'        => [''],
            'sem arroba'   => ['joao.hidrocontrol.test'],
            'sem domínio'  => ['joao@'],
            'sem usuário'  => ['@hidrocontrol.test'],
            'com espaço'   => ['joao silva@hidrocontrol.test'],
        ];
    }

    #[DataProvider('emailsInvalidos')]
    public function testRejeitaEmailInvalido(string $email): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Operador('João', '', $email, '', 'Operação', $this->hash());
    }
}
