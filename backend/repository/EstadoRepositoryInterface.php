<?php
declare(strict_types=1);

/**
 * EstadoRepositoryInterface.php
 *
 * Arquivo responsável por definir o contrato para guardar as alterações que
 * o operador fez nas turbinas (ligar e desligar), separadas por cenário.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Guarda as alterações que o operador fez nas turbinas (ligar e desligar).
 * Cada cenário mantém as suas próprias alterações.
 */
interface EstadoRepositoryInterface
{
    /**
     * Alterações do cenário: código da turbina => ligada (true) ou desligada (false).
     * Só traz as turbinas que o operador alterou.
     *
     * @return array<string, bool>
     */
    public function obterEstadoTurbinas(string $cenario): array;

    public function definirEstadoTurbina(string $cenario, string $codigo, bool $ligada): void;

    /**
     * Descarta todas as alterações, de todos os cenários.
     */
    public function limpar(): void;
}
