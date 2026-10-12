<?php
declare(strict_types=1);

/**
 * UsuarioRepositoryInterface.php
 *
 * Arquivo responsável por definir o contrato da fonte de usuários do sistema,
 * permitindo localizar um operador pelo e-mail.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Fonte dos usuários do sistema.
 * Hoje são usuários fixos no código; no futuro pode ser um banco de dados.
 */
interface UsuarioRepositoryInterface
{
    /**
     * Localiza um operador pelo e-mail (sem diferenciar maiúsculas de minúsculas).
     * Devolve null se não existir.
     */
    public function buscarPorEmail(string $email): ?Operador;
}
