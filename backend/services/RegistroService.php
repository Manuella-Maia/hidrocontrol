<?php
declare(strict_types=1);

/**
 * RegistroService.php
 *
 * Arquivo responsável por registrar e consultar o histórico de ações dos
 * operadores.
 *
 * @package service
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Registra e consulta o histórico de ações dos operadores.
 */
class RegistroService
{
    private RegistroRepositoryInterface $repository;

    public function __construct(RegistroRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function registrar(RegistroAcao $registro): void
    {
        $this->repository->salvar($registro);
    }

    /**
     * @return RegistroAcao[] do mais recente para o mais antigo
     */
    public function listarUltimos(int $limite = 10): array
    {
        return $this->repository->listar($limite);
    }
}
