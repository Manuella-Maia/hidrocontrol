<?php
declare(strict_types=1);

/**
 * Mapeia ?rota=... para o controller responsável.
 * A injeção de dependência (quais repositories usar) acontece aqui.
 *
 *   Livres (sem login):
 *     GET   ?rota=status
 *     POST  ?rota=auth&acao=entrar      (e-mail e senha)
 *     POST  ?rota=auth&acao=sair
 *     GET   ?rota=auth&acao=eu
 *
 *   Protegidas (401 sem login):
 *     GET   ?rota=leitura&cenario=A|B|C
 *     GET   ?rota=registros
 *     POST  ?rota=turbina&acao=ligar|desligar&codigo=T-02&cenario=A|B|C
 *     POST  ?rota=restaurar
 */
function responderErro(int $status, string $mensagem): void
{
    http_response_code($status);
    echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE);
}

function exigirPost(string $metodo): bool
{
    if ($metodo === 'POST') {
        return true;
    }
    header('Allow: POST');
    responderErro(405, 'Use o método POST.');
    return false;
}

$rota = $_GET['rota'] ?? 'status';
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($rota === 'status') {
    echo json_encode(['sistema' => 'HidroControl', 'status' => 'ok']);
    return;
}

$estado = new EstadoRepositoryJson(new ArmazenamentoJson(__DIR__ . '/../storage/estado.json'));
$registros = new RegistroService(
    new RegistroRepositoryJson(new ArmazenamentoJson(__DIR__ . '/../storage/registros.json'))
);
$authService = new AuthService(new UsuarioRepositorySimulado(), $registros);

// ---- Autenticação (rotas livres) ----
if ($rota === 'auth') {
    $controller = new AuthController($authService);
    $acao = $_GET['acao'] ?? '';

    if ($acao === 'entrar') {
        if (exigirPost($metodo)) {
            $controller->entrar();
        }
    } elseif ($acao === 'sair') {
        if (exigirPost($metodo)) {
            $controller->sair();
        }
    } elseif ($acao === 'eu') {
        $controller->eu();
    } else {
        responderErro(400, 'Ação inválida: use entrar, sair ou eu.');
    }
    return;
}

// ---- Daqui em diante, só com login ----
$operador = $authService->operadorLogado();
if ($operador === null) {
    responderErro(401, 'Faça login para continuar.');
    return;
}

$usinaService = new UsinaService(new UsinaRepositorySimulado($estado), $estado, $registros);

switch ($rota) {
    case 'leitura':
        (new LeituraController($usinaService))->obter($_GET['cenario'] ?? 'A', $operador);
        break;

    case 'registros':
        (new LeituraController($usinaService))->registros();
        break;

    case 'turbina':
        if (!exigirPost($metodo)) {
            break;
        }
        $controller = new TurbinaController($usinaService);
        $cenario = $_GET['cenario'] ?? 'A';
        $codigo = $_GET['codigo'] ?? '';
        $acao = $_GET['acao'] ?? '';

        if ($acao === 'ligar') {
            $controller->ligar($cenario, $codigo, $operador);
        } elseif ($acao === 'desligar') {
            $controller->desligar($cenario, $codigo, $operador);
        } else {
            responderErro(400, 'Ação inválida: use ligar ou desligar.');
        }
        break;

    case 'restaurar':
        if (exigirPost($metodo)) {
            (new TurbinaController($usinaService))->restaurar($operador);
        }
        break;

    default:
        responderErro(404, 'Rota não encontrada');
}
