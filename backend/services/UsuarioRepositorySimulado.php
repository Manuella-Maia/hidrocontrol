<?php
declare(strict_types=1);

/**
 * UsuarioRepositorySimulado.php
 *
 * Arquivo responsável por fornecer operadores fixos no código, com dados
 * fictícios, para demonstração do sistema.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Operadores fixos no código, para demonstração (não há cadastro de usuários).
 * Todos os dados são fictícios. A senha de ambos é "senha123", guardada como hash.
 */
class UsuarioRepositorySimulado implements UsuarioRepositoryInterface
{
    private const SENHA_DEMONSTRACAO = 'senha123';

    /** @var Operador[] indexados pelo e-mail em minúsculas */
    private array $operadores = [];

    public function __construct()
    {
        $hash = password_hash(self::SENHA_DEMONSTRACAO, PASSWORD_DEFAULT);

        $this->operadores = [
            'joao@hidrocontrol.test' => new Operador(
                'João Silva',
                '(31) 90000-0001',
                'joao@hidrocontrol.test',
                '000.000.000-01',
                'Operação',
                $hash
            ),
            'maria@hidrocontrol.test' => new Operador(
                'Maria Souza',
                '(31) 90000-0002',
                'maria@hidrocontrol.test',
                '000.000.000-02',
                'Operação',
                $hash
            ),
        ];
    }

    public function buscarPorEmail(string $email): ?Operador
    {
        return $this->operadores[strtolower(trim($email))] ?? null;
    }
}
