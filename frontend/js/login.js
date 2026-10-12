/**
 * login.js
 *
 * Arquivo responsável por controlar a página de login: envia as credenciais
 * do operador, mostra o erro se falhar e redireciona para o painel.
 *
 * @package js
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

import { Api, ErroApi } from './api.js';

const formulario = document.querySelector('#formulario');
const botao = document.querySelector('#btn-entrar');
const aviso = document.querySelector('#mensagem-erro');

// Quem já está logado vai direto para o painel.
Api.eu().then(() => location.replace('index.html')).catch(() => {});

formulario.addEventListener('submit', async (evento) => {
  evento.preventDefault();
  aviso.hidden = true;
  botao.disabled = true;

  try {
    await Api.entrar(formulario.email.value, formulario.senha.value);
    location.replace('index.html');
  } catch (erro) {
    aviso.textContent = erro instanceof ErroApi ? erro.message : 'Não foi possível entrar. Tente novamente.';
    aviso.hidden = false;
    formulario.senha.value = '';
    formulario.senha.focus();
  } finally {
    botao.disabled = false;
  }
});
