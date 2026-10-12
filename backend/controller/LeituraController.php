<?php
/**
 * LeituraController.php
 *
 * Controller das leituras da usina. Devolve a leitura do cenário escolhido
 * e a lista dos últimos registros de ações dos operadores, sempre em JSON.
 *
 * @package controller
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

// Ativa a tipagem estrita: o PHP não converte tipos silenciosamente.
declare(strict_types=1);

/**
 * Recebe a requisição de leitura e devolve JSON.
 * Não tem regra de negócio: só traduz a resposta do service em HTTP.
 */
class LeituraController
{
    // Serviço que concentra a regra de negócio da usina.
    private UsinaService $usinaService;

    // Recebe o UsinaService por injeção de dependência.
    public function __construct(UsinaService $usinaService)
    {
        // Guarda o serviço para uso nos demais métodos.
        $this->usinaService = $usinaService;
    }

    /**
     * 200 com a leitura; 400 se o cenário for inválido.
     */
    public function obter(string $cenario, Operador $operador): void
    {
        // Tenta obter a leitura; o service lança exceção se o cenário não existir.
        try {
            // Responde 200 com a leitura do cenário para o operador informado.
            $this->responder(200, $this->usinaService->obterLeitura($cenario, $operador));
        // Cenário inválido: captura a exceção lançada pelo service.
        } catch (InvalidArgumentException $e) {
            // Responde 400 (requisição inválida) com a mensagem da exceção.
            $this->responder(400, ['erro' => $e->getMessage()]);
        }
    }

    /**
     * 200 com as últimas ações dos operadores.
     */
    public function registros(int $limite = 10): void
    {
        // Responde 200 com os registros mais recentes, limitados a $limite (padrão: 10).
        $this->responder(200, ['registros' => $this->usinaService->obterRegistros($limite)]);
    }

    // Envia a resposta HTTP: define o status e imprime o corpo em JSON.
    private function responder(int $status, array $dados): void
    {
        // Define o código de status HTTP da resposta.
        http_response_code($status);
        // Imprime os dados em JSON, com acentos legíveis e formatação indentada.
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}