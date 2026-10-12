<?php
declare(strict_types=1);

/**
 * ArmazenamentoJson.php
 *
 * Arquivo responsável por ler e gravar o arquivo JSON de armazenamento.
 * A leitura é tolerante: arquivo ausente, vazio ou corrompido devolve lista vazia.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Único lugar que lê e grava um arquivo JSON.
 *
 * Leitura tolerante: arquivo ausente, vazio ou corrompido devolve lista vazia,
 * para o sistema nunca quebrar por causa do arquivo (RNF07).
 */
class ArmazenamentoJson
{
    private string $caminho;

    public function __construct(string $caminho)
    {
        $this->caminho = $caminho;
    }

    public function ler(): array
    {
        if (!is_file($this->caminho)) {
            return [];
        }

        $conteudo = file_get_contents($this->caminho);
        if ($conteudo === false || trim($conteudo) === '') {
            return [];
        }

        $dados = json_decode($conteudo, true);

        return is_array($dados) ? $dados : [];
    }

    /**
     * @throws RuntimeException se não for possível gravar o arquivo
     */
    public function gravar(array $dados): void
    {
        $pasta = dirname($this->caminho);
        if (!is_dir($pasta) && !mkdir($pasta, 0775, true) && !is_dir($pasta)) {
            throw new RuntimeException('Não foi possível criar a pasta de armazenamento.');
        }

        $json = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false || file_put_contents($this->caminho, $json, LOCK_EX) === false) {
            throw new RuntimeException('Não foi possível gravar o arquivo de armazenamento.');
        }
    }
}
