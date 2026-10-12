<?php
/**
 * AuthController.php
 *
 * Controller de autenticação do painel. Expõe as três ações de sessão:
 * entrar (login), sair (logout) e eu (consulta do operador logado).
 * Não contém regra de negócio: apenas lê a requisição, delega ao
 * AuthService e traduz o resultado em resposta HTTP/JSON.
 *
 * @package controller
 * @author autor
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

// Ativa a tipagem estrita: o PHP não converte tipos silenciosamente
// (ex.: passar "123" onde se espera int gera erro).
declare(strict_types=1);

/**
 * Entrar, sair e "quem sou eu". Não tem regra de negócio: lê o corpo da
 * requisição, chama o AuthService e traduz o resultado em HTTP.
 *
 *   401  credenciais inválidas ou ninguém logado
 */
class AuthController
{
    // Serviço que concentra a regra de autenticação (validar senha, abrir/fechar sessão).
    private AuthService $authService;

    /**
     * Recebe o AuthService por injeção de dependência, o que facilita testes
     * e mantém o controller desacoplado da implementação do serviço.
     */
    public function __construct(AuthService $authService)
    {
        // Guarda o serviço recebido para uso nos demais métodos da classe.
        $this->authService = $authService;
    }

    /**
     * Ação de login: valida e-mail e senha e responde com os dados resumidos do operador.
     */
    public function entrar(): void
    {
        // Obtém os dados enviados pelo cliente (JSON ou formulário).
        $corpo = $this->lerCorpo();
        // Pede ao serviço para autenticar; retorna o Operador ou null se falhar.
        $operador = $this->authService->entrar(
            // E-mail informado; se ausente, usa string vazia. O (string) garante o tipo.
            (string) ($corpo['email'] ?? ''),
            // Senha informada; se ausente, usa string vazia. O (string) garante o tipo.
            (string) ($corpo['senha'] ?? '')
        );

        // Credenciais inválidas: o serviço devolveu null.
        if ($operador === null) {
            // Responde 401 (não autorizado) com mensagem de erro.
            $this->responder(401, ['erro' => 'E-mail ou senha inválidos.']);
            // Encerra o método para não enviar uma segunda resposta.
            return;
        }

        // Login bem-sucedido: responde 200 com apenas nome e setor do operador.
        $this->responder(200, ['operador' => $this->resumo($operador)]);
    }

    /**
     * Ação de logout: encerra a sessão do operador atual.
     */
    public function sair(): void
    {
        // Delega ao serviço o encerramento da sessão.
        $this->authService->sair();
        // Confirma ao cliente com status 200 e uma mensagem.
        $this->responder(200, ['mensagem' => 'Sessão encerrada.']);
    }

    /**
     * Ação "quem sou eu": devolve o operador atualmente autenticado.
     */
    public function eu(): void
    {
        // Consulta ao serviço quem está logado; null significa ninguém autenticado.
        $operador = $this->authService->operadorLogado();

        // Não há sessão ativa.
        if ($operador === null) {
            // Responde 401 (não autorizado) com mensagem de erro.
            $this->responder(401, ['erro' => 'Nenhum operador autenticado.']);
            // Encerra o método para não enviar uma segunda resposta.
            return;
        }

        // Há operador logado: responde 200 com seu nome e setor.
        $this->responder(200, ['operador' => $this->resumo($operador)]);
    }

    /**
     * Do operador, só nome e setor (nunca CPF, e-mail, telefone ou senha).
     */
    private function resumo(Operador $operador): array
    {
        // Monta um array só com os campos seguros para expor ao cliente.
        return ['nome' => $operador->getNome(), 'setor' => $operador->getSetor()];
    }

    /**
     * Aceita JSON (usado pelo painel) ou formulário (usado no curl).
     */
    private function lerCorpo(): array
    {
        // Lê o tipo de conteúdo da requisição; usa string vazia se o cabeçalho não existir.
        $tipo = $_SERVER['CONTENT_TYPE'] ?? '';
        // Se o cliente enviou JSON (caso do painel)...
        if (str_contains($tipo, 'application/json')) {
            // Lê o corpo bruto da requisição e converte o JSON em array associativo.
            $dados = json_decode((string) file_get_contents('php://input'), true);
            // Se o JSON for válido (array), devolve; senão, devolve array vazio.
            return is_array($dados) ? $dados : [];
        }
        // Caso contrário, assume formulário e devolve os dados de $_POST.
        return $_POST;
    }

    /**
     * Envia a resposta HTTP: define o status e imprime o corpo em JSON.
     */
    private function responder(int $status, array $dados): void
    {
        // Define o código de status HTTP da resposta (200, 401 etc.).
        http_response_code($status);
        // Imprime os dados como JSON, mantendo acentos legíveis e formatação indentada.
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}