/**
 * painel.js
 *
 * Arquivo responsável por orquestrar a página do painel: carrega os dados da
 * usina, controla a troca de cenário e liga os botões de ligar, desligar,
 * atualizar, restaurar e sair.
 *
 * @package js
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

import { Api, ErroApi } from './api.js';
import { Ui } from './ui.js';
import { Alertas } from './alertas.js';

const CENARIOS = ['A', 'B', 'C'];
let cenario = lerCenarioGuardado();

function lerCenarioGuardado() {
  try {
    const guardado = sessionStorage.getItem('cenario');
    return CENARIOS.includes(guardado) ? guardado : 'A';
  } catch {
    return 'A';
  }
}

function guardarCenario(valor) {
  try {
    sessionStorage.setItem('cenario', valor);
  } catch {
    /* sem armazenamento: o cenário só vale nesta página */
  }
}

function tratarErro(erro) {
  if (erro instanceof ErroApi && erro.status === 401) {
    location.replace('login.html');
    return;
  }
  Ui.mostrarErro(erro instanceof ErroApi ? erro.message : 'Algo deu errado. Tente atualizar o painel.');
}

async function atualizarEventos() {
  const historico = await Api.registros();
  Ui.mostrarEventos(historico.registros);
}

async function carregar() {
  try {
    const [leitura, historico] = await Promise.all([Api.leitura(cenario), Api.registros()]);
    Ui.limparErro();
    Ui.mostrarLeitura(leitura);
    Alertas.sincronizar(leitura.alertas);
    Ui.mostrarEventos(historico.registros);
  } catch (erro) {
    tratarErro(erro);
  }
}

function escolherCenario(novo) {
  cenario = novo;
  guardarCenario(novo);
  Ui.marcarCenario(novo);
  Ui.limparAviso();
  Alertas.reiniciar();
  carregar();
}

/**
 * Liga ou desliga uma turbina. O servidor devolve a leitura já atualizada.
 * Se a turbina já estava no estado pedido (409), a tela é recarregada para
 * mostrar a situação real.
 */
async function alterarTurbina(acao, codigo) {
  Ui.bloquearAcoes(true);
  try {
    const leitura = await Api.alterarTurbina(acao, codigo, cenario);
    Ui.limparErro();
    Ui.mostrarLeitura(leitura);
    Alertas.sincronizar(leitura.alertas);
    Ui.mostrarAviso(leitura.registro);
    await atualizarEventos();
  } catch (erro) {
    tratarErro(erro);
    if (erro instanceof ErroApi && erro.status === 409) {
      await carregar();
    }
  } finally {
    Ui.bloquearAcoes(false);
  }
}

async function restaurar() {
  if (!Ui.confirmar('Restaurar a simulação? As alterações nas turbinas de todos os cenários serão desfeitas.')) {
    return;
  }
  Ui.bloquearAcoes(true);
  try {
    await Api.restaurar();
    Alertas.reiniciar();
    await carregar();
    Ui.mostrarAviso('Simulação restaurada.');
  } catch (erro) {
    tratarErro(erro);
  } finally {
    Ui.bloquearAcoes(false);
  }
}

async function sair() {
  try {
    await Api.sair();
  } finally {
    location.replace('login.html');
  }
}

async function iniciar() {
  try {
    const { operador } = await Api.eu();
    Ui.mostrarOperador(operador.nome);
  } catch (erro) {
    tratarErro(erro);
    return;
  }

  document.querySelectorAll('[data-cenario]').forEach((botao) => {
    botao.addEventListener('click', () => escolherCenario(botao.dataset.cenario));
  });

  // Os botões das turbinas são recriados a cada leitura: um único ouvinte na lista.
  document.querySelector('#lista-turbinas').addEventListener('click', (evento) => {
    const botao = evento.target.closest('button[data-acao]');
    if (botao) {
      alterarTurbina(botao.dataset.acao, botao.dataset.codigo);
    }
  });

  document.querySelector('#btn-atualizar').addEventListener('click', carregar);
  document.querySelector('#btn-restaurar').addEventListener('click', restaurar);
  document.querySelector('#btn-sair').addEventListener('click', sair);

  Ui.marcarCenario(cenario);
  carregar();
}

iniciar();
