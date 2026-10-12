<?php
declare(strict_types=1);

/**
 * RegistroRepositoryJson.php
 *
 * Arquivo responsável por guardar em arquivo JSON o histórico de ações dos
 * operadores, mantendo apenas os últimos registros.
 *
 * @package repository
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

/**
 * Guarda o histórico em arquivo JSON, no formato:
 * { "registros": [ { "operador": "...", "acao": "...", "codigoTurbina": "...", "dataHora": "..." } ] }
 *
 * Mantém só os últimos LIMITE_GUARDADO registros, para o arquivo não crescer sem fim.
 */
class RegistroRepositoryJson implements RegistroRepositoryInterface
{
    private const LIMITE_GUARDADO = 200;

    private ArmazenamentoJson $armazenamento;

    public function __construct(ArmazenamentoJson $armazenamento)
    {
        $this->armazenamento = $armazenamento;
    }

    public function salvar(RegistroAcao $registro): void
    {
        $dados = $this->armazenamento->ler();
        $lista = is_array($dados['registros'] ?? null) ? $dados['registros'] : [];

        $lista[] = [
            'operador' => $registro->getOperador(),
            'acao' => $registro->getAcao(),
            'codigoTurbina' => $registro->getCodigoTurbina(),
            'dataHora' => $registro->getDataHora(),
        ];

        $dados['registros'] = array_slice($lista, -self::LIMITE_GUARDADO);
        $this->armazenamento->gravar($dados);
    }

    public function listar(int $limite): array
    {
        if ($limite <= 0) {
            return [];
        }

        $dados = $this->armazenamento->ler();
        $lista = is_array($dados['registros'] ?? null) ? $dados['registros'] : [];

        $registros = [];
        foreach (array_reverse($lista) as $item) {
            if (count($registros) >= $limite) {
                break;
            }
            if (!is_array($item)) {
                continue;
            }
            try {
                $registros[] = new RegistroAcao(
                    (string) ($item['operador'] ?? ''),
                    (string) ($item['acao'] ?? ''),
                    isset($item['codigoTurbina']) ? (string) $item['codigoTurbina'] : null,
                    isset($item['dataHora']) ? (string) $item['dataHora'] : null
                );
            } catch (InvalidArgumentException $e) {
                continue; // entrada corrompida no arquivo: ignora
            }
        }

        return $registros;
    }
}
