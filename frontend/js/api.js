/**
 * api.js
 *
 * Arquivo responsável por toda a comunicação com o backend: é a única camada
 * que faz fetch(), então o resto do painel não conhece URLs nem HTTP.
 *
 * @package js
 * @author Manuella Maia Lopes
 * @date 12/10/2026
 * @version 1.0.0
 * @since 1.0.0
 */

// Endereço do backend; todas as requisições passam por esse único arquivo PHP
const BASE = '../backend/index.php';

// Erro próprio da API: além da mensagem, guarda o status HTTP para quem tratar o erro
export class ErroApi extends Error {
  constructor(status, mensagem) {
    super(mensagem); // repassa a mensagem para a classe Error
    this.status = status; // guarda o código HTTP (0 = sem resposta do servidor)
  }
}

// Função central que monta e envia qualquer requisição ao backend
// rota: qual rota do backend chamar | metodo: GET por padrão | params: parâmetros na URL | corpo: dados enviados em JSON
async function requisitar(rota, { metodo = 'GET', params = {}, corpo = null } = {}) {
  // Monta a query string (?rota=...&outros=...) juntando a rota com os parâmetros
  const consulta = new URLSearchParams({ rota, ...params });
  // Opções do fetch: método, envia o cookie de sessão e começa sem cabeçalhos
  const opcoes = { method: metodo, credentials: 'same-origin', headers: {} };

  // Só adiciona corpo quando há dados para enviar (ex.: login)
  if (corpo !== null) {
    opcoes.headers['Content-Type'] = 'application/json'; // avisa que o corpo é JSON
    opcoes.body = JSON.stringify(corpo); // converte o objeto em texto JSON
  }

  let resposta;
  try {
    // Faz a requisição ao backend
    resposta = await fetch(`${BASE}?${consulta}`, opcoes);
  } catch {
    // Cai aqui quando não há resposta (servidor desligado, sem rede): status 0
    throw new ErroApi(0, 'Não foi possível falar com o servidor. Confira se o Apache está ligado.');
  }

  let dados = null;
  try {
    // Tenta ler o corpo da resposta como JSON
    dados = await resposta.json();
  } catch {
    // Se a resposta não for JSON válido (ou vier vazia), segue com null
    dados = null;
  }

  // Status fora da faixa 200-299: lança erro com a mensagem do servidor, ou uma padrão
  if (!resposta.ok) {
    throw new ErroApi(resposta.status, dados?.erro ?? 'Erro inesperado no servidor.');
  }
  // Sucesso: devolve os dados já convertidos
  return dados;
}

// Objeto com todas as chamadas que o painel pode fazer; cada uma só chama requisitar()
export const Api = {
  // Faz login enviando e-mail e senha no corpo (POST)
  entrar: (email, senha) =>
    requisitar('auth', { metodo: 'POST', params: { acao: 'entrar' }, corpo: { email, senha } }),
  // Encerra a sessão do operador (POST)
  sair: () => requisitar('auth', { metodo: 'POST', params: { acao: 'sair' } }),
  // Pergunta quem é o operador logado ("eu"); dá erro 401 se não houver login
  eu: () => requisitar('auth', { params: { acao: 'eu' } }),
  // Busca a leitura completa da usina no cenário informado
  leitura: (cenario) => requisitar('leitura', { params: { cenario } }),
  // Busca o histórico das últimas ações dos operadores
  registros: () => requisitar('registros'),
  // Liga ou desliga uma turbina (acao) pelo código, em um cenário (POST)
  alterarTurbina: (acao, codigo, cenario) =>
    requisitar('turbina', { metodo: 'POST', params: { acao, codigo, cenario } }),
  // Descarta as alterações feitas nas turbinas e volta a simulação ao início (POST)
  restaurar: () => requisitar('restaurar', { metodo: 'POST' }),
};