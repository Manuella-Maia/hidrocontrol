<?php
declare(strict_types=1);

/**
 * RegistroRepositoryInterface.php
 *
 * Arquivo responsável por definir o contrato para guardar e listar o
 * histórico de ações dos operadores, do registro mais recente para o mais antigo.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Guarda o histórico de ações dos operadores.
 */
interface RegistroRepositoryInterface
{
    public function salvar(RegistroAcao $registro): void;

    /**
     * Os registros mais recentes, do mais novo para o mais antigo.
     *
     * @return RegistroAcao[]
     */
    public function listar(int $limite): array;
}
