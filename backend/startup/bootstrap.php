<?php
declare(strict_types=1);

/**
 * Prepara o ambiente de uma requisição HTTP:
 * autoload, fuso horário, tratamento de erros, header JSON e sessão.
 */
require_once __DIR__ . '/autoload.php';

date_default_timezone_set('America/Sao_Paulo');

ini_set('display_errors', '0');   // RNF07: nenhum erro PHP aparece na tela
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Qualquer erro não tratado vira uma resposta JSON genérica (o detalhe vai para o log).
set_exception_handler(function (Throwable $e): void {
    error_log($e::class . ': ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno do servidor.'], JSON_UNESCAPED_UNICODE);
});

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,    // o JavaScript não lê o cookie da sessão
        'samesite' => 'Lax',   // não é enviado em POST vindo de outro site
    ]);
    session_start();
}
