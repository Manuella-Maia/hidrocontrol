<?php
declare(strict_types=1);

/**
 * AuthService.php
 *
 * Arquivo responsável por autenticar o operador: valida as credenciais, abre
 * e fecha a sessão e informa quem está logado. Registra ENTRAR e SAIR no histórico.
 *
 * @package service
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Autenticação do operador: valida as credenciais, abre e fecha a sessão e
 * informa quem está logado. Registra ENTRAR e SAIR no histórico.
 *
 * A senha é conferida pelo próprio Usuario (verificarSenha); aqui só se decide
 * o que fazer com o resultado. Quem falha no login recebe sempre o mesmo
 * resultado (null), sem revelar se o e-mail existe.
 */
class AuthService
{
    private const CHAVE_SESSAO = 'operador_email';

    private UsuarioRepositoryInterface $repository;
    private RegistroService $registroService;

    public function __construct(UsuarioRepositoryInterface $repository, RegistroService $registroService)
    {
        $this->repository = $repository;
        $this->registroService = $registroService;
    }

    /**
     * Devolve o operador autenticado, ou null se o e-mail ou a senha estiverem errados.
     */
    public function entrar(string $email, string $senha): ?Operador
    {
        $operador = $this->repository->buscarPorEmail($email);
        if ($operador === null || !$operador->verificarSenha($senha)) {
            return null;
        }

        $this->renovarIdentificadorDaSessao(); // evita fixação de sessão
        $_SESSION[self::CHAVE_SESSAO] = $operador->getEmail();

        $this->registroService->registrar(
            new RegistroAcao($operador->getNome(), RegistroAcao::ENTRAR)
        );

        return $operador;
    }

    /**
     * Encerra a sessão. Sem ninguém logado, não faz nada.
     */
    public function sair(): void
    {
        $operador = $this->operadorLogado();

        unset($_SESSION[self::CHAVE_SESSAO]);
        $this->renovarIdentificadorDaSessao();

        if ($operador !== null) {
            $this->registroService->registrar(
                new RegistroAcao($operador->getNome(), RegistroAcao::SAIR)
            );
        }
    }

    /**
     * Operador da sessão atual, ou null se não houver login válido.
     */
    public function operadorLogado(): ?Operador
    {
        $email = $_SESSION[self::CHAVE_SESSAO] ?? null;
        if (!is_string($email)) {
            return null;
        }

        return $this->repository->buscarPorEmail($email);
    }

    private function renovarIdentificadorDaSessao(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
