<?php
/**
 * TurbinaController.php
 *
 * Controller das ações sobre as turbinas: ligar, desligar e restaurar a
 * simulação. Traduz os erros do domínio em códigos HTTP e responde em JSON.
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
 * Recebe as ações do operador sobre as turbinas e devolve JSON.
 * Não tem regra de negócio: só traduz os erros do domínio em códigos HTTP.
 *
 *   400  cenário inválido
 *   404  turbina inexistente
 *   409  a turbina já está no estado pedido
 */
class TurbinaController
{
    // Serviço que concentra a regra de negócio da usina.
    private UsinaService $usinaService;

    // Recebe o UsinaService por injeção de dependência.
    public function __construct(UsinaService $usinaService)
    {
        // Guarda o serviço para uso nos demais métodos.
        $this->usinaService = $usinaService;
    }

    // Ação "ligar": reaproveita o método alterar() passando true (ligar).
    public function ligar(string $cenario, string $codigo, Operador $operador): void
    {
        // true = a turbina deve ficar ligada.
        $this->alterar($cenario, $codigo, true, $operador);
    }

    // Ação "desligar": reaproveita o método alterar() passando false (desligar).
    public function desligar(string $cenario, string $codigo, Operador $operador): void
    {
        // false = a turbina deve ficar desligada.
        $this->alterar($cenario, $codigo, false, $operador);
    }

    // Ação "restaurar": devolve a simulação ao estado inicial.
    public function restaurar(Operador $operador): void
    {
        // Delega ao service a restauração, registrando o operador responsável.
        $this->usinaService->restaurarSimulacao($operador);
        // Confirma ao cliente com status 200 e uma mensagem.
        $this->responder(200, ['mensagem' => 'Simulação restaurada.']);
    }

    // Lógica comum de ligar/desligar: chama o service e traduz os erros em HTTP.
    private function alterar(string $cenario, string $codigo, bool $ligar, Operador $operador): void
    {
        // Tenta alterar o estado; o service lança exceções específicas em caso de erro.
        try {
            // Responde 200 com o resultado da alteração de estado da turbina.
            $this->responder(200, $this->usinaService->alterarEstadoTurbina($cenario, $codigo, $ligar, $operador));
        // Cenário inválido.
        } catch (InvalidArgumentException $e) {      // deve vir antes de LogicException (é subclasse)
            // Responde 400 (requisição inválida) com a mensagem da exceção.
            $this->responder(400, ['erro' => $e->getMessage()]);
        // Turbina com o código informado não existe.
        } catch (OutOfBoundsException $e) {
            // Responde 404 (não encontrado) com a mensagem da exceção.
            $this->responder(404, ['erro' => $e->getMessage()]);
        // Turbina já está no estado pedido (conflito).
        } catch (LogicException $e) {
            // Responde 409 (conflito) com a mensagem da exceção.
            $this->responder(409, ['erro' => $e->getMessage()]);
        }
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