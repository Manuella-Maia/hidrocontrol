/**
 * alertas.js
 *
 * Arquivo responsável por decidir quando mostrar um aviso (toast): só os
 * alertas novos são exibidos, os críticos primeiro. Não mexe no DOM, quem
 * desenha é o ui.js.
 *
 * @package js
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

// Decide QUANDO mostrar um toast (sem tocar no DOM; quem desenha é o ui.js).
//
// Regra: só os alertas NOVOS viram toast. Um alerta que continua ativo não
// repete a cada atualização; se ele some e volta, é novo de novo.

import { Ui } from './ui.js';

let vistos = new Set();

const chave = (alerta) => `${alerta.codigo}|${alerta.mensagem}`;
const prioridade = (alerta) => (alerta.gravidade === 'CRÍTICO' ? 0 : 1);

export const Alertas = {
  /**
   * Compara os alertas atuais com os já mostrados e avisa só dos novos
   * (os críticos primeiro). Devolve os novos, para outras camadas (som)
   * poderem reagir ao mesmo evento.
   */
  sincronizar(alertas) {
    const novos = alertas.filter((alerta) => !vistos.has(chave(alerta)));
    vistos = new Set(alertas.map(chave));

    novos.sort((a, b) => prioridade(a) - prioridade(b));
    novos.forEach((alerta) => Ui.mostrarToast(alerta));
    return novos;
  },

  /** Esquece o que já foi mostrado (troca de cenário, restauração). */
  reiniciar() {
    vistos = new Set();
    Ui.limparToasts();
  },
};
