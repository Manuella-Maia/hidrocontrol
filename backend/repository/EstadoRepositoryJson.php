<?php
declare(strict_types=1);

/**
 * EstadoRepositoryJson.php
 *
 * Arquivo responsável por guardar em arquivo JSON as alterações que o
 * operador fez nas turbinas, separadas por cenário.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Guarda as alterações das turbinas em arquivo JSON, no formato:
 * { "turbinas": { "A": { "T-02": false }, "C": { "T-02": true } } }
 */
class EstadoRepositoryJson implements EstadoRepositoryInterface
{
    private ArmazenamentoJson $armazenamento;

    public function __construct(ArmazenamentoJson $armazenamento)
    {
        $this->armazenamento = $armazenamento;
    }

    public function obterEstadoTurbinas(string $cenario): array
    {
        $dados = $this->armazenamento->ler();
        $turbinas = is_array($dados['turbinas'] ?? null) ? $dados['turbinas'] : [];
        $doCenario = is_array($turbinas[$cenario] ?? null) ? $turbinas[$cenario] : [];

        return array_filter($doCenario, 'is_bool');
    }

    public function definirEstadoTurbina(string $cenario, string $codigo, bool $ligada): void
    {
        $dados = $this->armazenamento->ler();
        $turbinas = is_array($dados['turbinas'] ?? null) ? $dados['turbinas'] : [];
        $doCenario = is_array($turbinas[$cenario] ?? null) ? $turbinas[$cenario] : [];

        $doCenario[$codigo] = $ligada;
        $turbinas[$cenario] = $doCenario;
        $dados['turbinas'] = $turbinas;

        $this->armazenamento->gravar($dados);
    }

    public function limpar(): void
    {
        $this->armazenamento->gravar([]);
    }
}
