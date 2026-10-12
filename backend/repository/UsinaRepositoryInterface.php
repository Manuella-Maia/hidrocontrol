<?php
declare(strict_types=1);

/**
 * UsinaRepositoryInterface.php
 *
 * Arquivo responsável por definir o contrato da fonte de dados da usina,
 * permitindo trocar a origem (simulação, sensores, banco de dados) sem
 * alterar services nem controllers.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Fonte dos dados da usina.
 *
 * Hoje a única implementação devolve cenários simulados. No futuro, outra
 * implementação (sensores, banco de dados) pode ser trocada sem alterar
 * services, controllers nem as regras do domínio.
 */
interface UsinaRepositoryInterface
{
    /**
     * Monta a usina do cenário informado, com o operador como responsável.
     *
     * @throws InvalidArgumentException se o cenário não existir
     */
    public function obterUsina(string $cenario, Operador $operador): Usina;
}
