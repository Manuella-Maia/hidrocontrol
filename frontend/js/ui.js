/**
 * ui.js
 *
 * Arquivo responsável por desenhar a interface: é a única camada que mexe no
 * DOM, mostrando situação, reservatório, turbinas, alertas, eventos e avisos.
 *
 * @package js
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

const ESTADO_PELO_TEXTO = { 'NORMAL': 'normal', 'ATENÇÃO': 'atencao', 'CRÍTICO': 'critico' };
const ESCALA_TEMPERATURA = 120; // °C no fim da barra (só apresentação)

const $ = (seletor) => document.querySelector(seletor);
const numero = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 1 });

function criar(tag, { classe, texto, atributos = {} } = {}, filhos = []) {
  const elemento = document.createElement(tag);
  if (classe) elemento.className = classe;
  if (texto !== undefined) elemento.textContent = texto;
  for (const [nome, valor] of Object.entries(atributos)) elemento.setAttribute(nome, String(valor));
  elemento.append(...filhos);
  return elemento;
}

const limitar = (valor, minimo, maximo) => Math.min(maximo, Math.max(minimo, valor));
const marca = () => criar('span', { classe: 'marca', atributos: { 'aria-hidden': 'true' } });
const estadoDaSituacao = (texto) => ESTADO_PELO_TEXTO[texto] ?? 'atencao';

function mostrarSituacao(leitura) {
  const quantidade = leitura.alertas.length;
  $('#situacao').dataset.estado = estadoDaSituacao(leitura.situacaoGeral);
  $('#situacao-texto').textContent = leitura.situacaoGeral;
  $('#situacao-resumo').textContent =
    quantidade === 0 ? 'Nenhum alerta ativo' : quantidade === 1 ? '1 alerta ativo' : `${quantidade} alertas ativos`;
  $('#meta-usina').textContent = leitura.usina.nome;
  $('#meta-operador').textContent = leitura.usina.operador.nome;
  $('#meta-potencia').textContent = `${numero.format(leitura.usina.potenciaGerada)} MW`;
  $('#meta-atualizado').textContent = new Date().toLocaleTimeString('pt-BR');
}

function mostrarReservatorio(reservatorio, limites) {
  const { nivelMinimo, nivelMaximo } = limites;
  $('#reservatorio').dataset.estado = reservatorio.situacao === 'NORMAL' ? 'normal' : 'atencao';
  $('#reservatorio-nivel').textContent = `${numero.format(reservatorio.nivel)}%`;
  $('#reservatorio-vazao').textContent = `${numero.format(reservatorio.vazao)} m³/s`;
  $('#reservatorio-situacao').textContent = reservatorio.situacao;

  const tanque = $('#tanque');
  tanque.setAttribute('aria-valuenow', String(reservatorio.nivel));
  tanque.setAttribute('aria-valuetext', `${numero.format(reservatorio.nivel)}%, ${reservatorio.situacao}`);
  $('#tanque-agua').style.height = `${limitar(reservatorio.nivel, 0, 100)}%`;

  const faixa = $('#tanque-faixa');
  faixa.style.bottom = `${nivelMinimo}%`;
  faixa.style.height = `${nivelMaximo - nivelMinimo}%`;
  $('#marca-minimo').style.bottom = `${nivelMinimo}%`;
  $('#marca-minimo').textContent = `${numero.format(nivelMinimo)}%`;
  $('#marca-maximo').style.bottom = `${nivelMaximo}%`;
  $('#marca-maximo').textContent = `${numero.format(nivelMaximo)}%`;
}

function linhaDeTurbina(turbina, limites) {
  const posicao = (graus) => `${limitar((graus / ESCALA_TEMPERATURA) * 100, 0, 100)}%`;
  const estadoOperacao = turbina.ligada ? 'normal' : 'atencao';

  const barra = criar('div', { classe: 'barra', atributos: { 'aria-hidden': 'true' } }, [
    criar('div', { classe: 'barra-nivel', atributos: { style: `width:${posicao(turbina.temperatura)}` } }),
    criar('span', { classe: 'barra-marca', atributos: { style: `left:${posicao(limites.temperaturaAtencao)}` } }),
    criar('span', { classe: 'barra-marca', atributos: { style: `left:${posicao(limites.temperaturaCritica)}` } }),
  ]);

  const selo = criar('span', { classe: 'selo', atributos: { 'data-estado': estadoOperacao } }, [
    marca(),
    criar('span', { texto: turbina.estado }),
  ]);

  const verbo = turbina.ligada ? 'Desligar' : 'Ligar';
  const botao = criar('button', {
    classe: 'botao-acao',
    texto: verbo,
    atributos: {
      type: 'button',
      'data-acao': turbina.ligada ? 'desligar' : 'ligar',
      'data-codigo': turbina.codigo,
      'aria-label': `${verbo} ${turbina.nome}`,
    },
  });

  return criar(
    'li',
    { classe: 'turbina', atributos: { 'data-estado': estadoDaSituacao(turbina.situacaoTemperatura) } },
    [
      criar('div', { classe: 'turbina-nome' }, [
        criar('strong', { texto: turbina.nome }),
        criar('span', { classe: 'codigo', texto: turbina.codigo }),
      ]),
      criar('div', { classe: 'turbina-estado' }, [selo, botao]),
      criar('div', { classe: 'turbina-temperatura' }, [
        criar('span', { classe: 'valor', texto: numero.format(turbina.temperatura) }),
        criar('span', { classe: 'unidade', texto: '°C' }),
        criar('span', { classe: 'situacao-temperatura' }, [marca(), criar('span', { texto: turbina.situacaoTemperatura })]),
      ]),
      barra,
    ]
  );
}

function mostrarTurbinas(turbinas, limites) {
  $('#lista-turbinas').replaceChildren(...turbinas.map((turbina) => linhaDeTurbina(turbina, limites)));
}

function mostrarAlertas(alertas) {
  const lista = $('#lista-alertas');
  if (alertas.length === 0) {
    lista.replaceChildren(criar('li', { classe: 'vazio', texto: 'Nenhum alerta ativo' }));
    return;
  }
  lista.replaceChildren(
    ...alertas.map((alerta) =>
      criar('li', { classe: 'alerta', atributos: { 'data-estado': estadoDaSituacao(alerta.gravidade) } }, [
        criar('p', { classe: 'alerta-titulo' }, [marca(), criar('strong', { texto: alerta.gravidade }), criar('span', { texto: alerta.parametro })]),
        criar('p', { classe: 'alerta-mensagem', texto: alerta.mensagem }),
        criar('p', { classe: 'alerta-acao', texto: `O que fazer: ${alerta.acaoRecomendada}` }),
      ])
    )
  );
}

function mostrarEventos(eventos) {
  const lista = $('#lista-eventos');
  if (eventos.length === 0) {
    lista.replaceChildren(criar('li', { classe: 'vazio', texto: 'Nenhuma ação registrada ainda' }));
    return;
  }
  lista.replaceChildren(...eventos.map((evento) => criar('li', { texto: evento.texto })));
}

const LIMITE_TOASTS = 4;
const TEMPO_TOAST_ATENCAO_MS = 8000; // críticos ficam até serem fechados

function mostrarToast(alerta) {
  const area = $('#toasts');
  const critico = alerta.gravidade === 'CRÍTICO';

  const fechar = criar('button', {
    classe: 'toast-fechar',
    texto: 'Fechar',
    atributos: { type: 'button', 'aria-label': `Fechar aviso: ${alerta.mensagem}` },
  });
  const toast = criar(
    'div',
    {
      classe: 'toast',
      atributos: { 'data-estado': critico ? 'critico' : 'atencao', role: critico ? 'alert' : 'status' },
    },
    [
      criar('p', { classe: 'toast-titulo' }, [marca(), criar('strong', { texto: alerta.gravidade }), criar('span', { texto: alerta.parametro })]),
      criar('p', { classe: 'toast-mensagem', texto: alerta.mensagem }),
      criar('p', { classe: 'toast-acao', texto: alerta.acaoRecomendada }),
      fechar,
    ]
  );

  fechar.addEventListener('click', () => toast.remove());
  area.append(toast);

  // Sobrando toasts, sai o mais antigo que não seja crítico (ou o mais antigo de todos).
  while (area.children.length > LIMITE_TOASTS) {
    const descartavel = area.querySelector('.toast[data-estado="atencao"]') ?? area.firstElementChild;
    descartavel.remove();
  }

  if (!critico) {
    let temporizador;
    const iniciar = () => {
      temporizador = setTimeout(() => toast.remove(), TEMPO_TOAST_ATENCAO_MS);
    };
    const pausar = () => clearTimeout(temporizador);
    toast.addEventListener('mouseenter', pausar);
    toast.addEventListener('mouseleave', iniciar);
    toast.addEventListener('focusin', pausar);
    toast.addEventListener('focusout', iniciar);
    iniciar();
  }
}

function limparToasts() {
  $('#toasts').replaceChildren();
}

export const Ui = {
  mostrarToast,
  limparToasts,
  mostrarLeitura(leitura) {
    mostrarSituacao(leitura);
    mostrarReservatorio(leitura.reservatorio, leitura.limites);
    mostrarTurbinas(leitura.turbinas, leitura.limites);
    mostrarAlertas(leitura.alertas);
  },
  mostrarEventos,
  mostrarOperador(nome) {
    $('#operador-nome').textContent = nome;
  },
  marcarCenario(cenario) {
    document.querySelectorAll('[data-cenario]').forEach((botao) => {
      botao.setAttribute('aria-pressed', String(botao.dataset.cenario === cenario));
    });
  },
  mostrarAviso(texto) {
    const aviso = $('#mensagem-status');
    aviso.textContent = texto;
    aviso.hidden = false;
  },
  limparAviso() {
    $('#mensagem-status').hidden = true;
  },
  bloquearAcoes(bloqueado) {
    document.querySelectorAll('.botao-acao, #btn-restaurar').forEach((botao) => {
      botao.disabled = bloqueado;
    });
  },
  confirmar(pergunta) {
    return window.confirm(pergunta);
  },
  mostrarErro(mensagem) {
    const aviso = $('#mensagem-erro');
    aviso.textContent = mensagem;
    aviso.hidden = false;
  },
  limparErro() {
    $('#mensagem-erro').hidden = true;
  },
};
