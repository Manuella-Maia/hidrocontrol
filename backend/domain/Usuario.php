<?php
declare(strict_types=1);

/**
 * Pessoa cadastrada no sistema.
 *
 * É abstrata: ninguém cria um "usuário genérico", só tipos específicos (Operador).
 * A senha nunca é guardada em texto: o construtor recebe o HASH gerado por
 * password_hash(), e a conferência é feita aqui mesmo (verificarSenha()), então
 * o hash não tem getter e nunca sai da classe.
 */
abstract class Usuario
{
    private string $nome;
    private string $telefone;
    private string $email;
    private string $cpf;
    private string $setor;
    private string $senhaHash;

    /**
     * @param string $senhaHash hash gerado por password_hash() (nunca a senha em texto)
     *
     * @throws InvalidArgumentException se o nome for vazio, o e-mail for inválido
     *                                  ou a senha não for um hash válido
     */
    public function __construct(
        string $nome,
        string $telefone,
        string $email,
        string $cpf,
        string $setor,
        string $senhaHash
    ) {
        if (trim($nome) === '') {
            throw new InvalidArgumentException('O nome do usuário não pode ser vazio.');
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('E-mail inválido.');
        }
        if (password_get_info($senhaHash)['algo'] === null) {
            throw new InvalidArgumentException('A senha deve ser informada como hash (password_hash).');
        }

        $this->nome = $nome;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->cpf = $cpf;
        $this->setor = $setor;
        $this->senhaHash = $senhaHash;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getTelefone(): string
    {
        return $this->telefone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getSetor(): string
    {
        return $this->setor;
    }

    /**
     * Confere a senha informada contra o hash guardado.
     */
    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senhaHash);
    }
}
