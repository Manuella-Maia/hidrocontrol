<?php
declare(strict_types=1);

/**
 * Usuário que opera o painel: consulta a usina e liga ou desliga turbinas.
 *
 * Não tem atributos próprios (tudo vem de Usuario). Cada ação devolve o
 * RegistroAcao correspondente; quem grava o registro é o service.
 */
class Operador extends Usuario
{
    /**
     * Liga a turbina e devolve o registro da ação.
     * Se a turbina já estiver ligada, o erro sobe e nenhum registro é criado.
     *
     * @throws LogicException se a turbina já estiver ligada
     */
    public function ligarTurbina(Turbina $turbina): RegistroAcao
    {
        $turbina->ligar();

        return new RegistroAcao(
            $this->getNome(),
            RegistroAcao::LIGAR_TURBINA,
            $turbina->getCodigo()
        );
    }

    /**
     * Desliga a turbina e devolve o registro da ação.
     * Se a turbina já estiver desligada, o erro sobe e nenhum registro é criado.
     *
     * @throws LogicException se a turbina já estiver desligada
     */
    public function desligarTurbina(Turbina $turbina): RegistroAcao
    {
        $turbina->desligar();

        return new RegistroAcao(
            $this->getNome(),
            RegistroAcao::DESLIGAR_TURBINA,
            $turbina->getCodigo()
        );
    }
}
