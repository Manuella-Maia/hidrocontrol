<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

/**
 * Autoload simples: o nome do arquivo é igual ao nome da classe.
 * Usado pela aplicação (via bootstrap.php) e pelos testes PHPUnit.
 * Não inicia sessão nem envia headers, para poder rodar no terminal.
 */
spl_autoload_register(function (string $classe): void {
    foreach (['domain', 'repository', 'services', 'controller'] as $pasta) {
        $arquivo = __DIR__ . '/../' . $pasta . '/' . $classe . '.php';
        if (is_file($arquivo)) {
            require_once $arquivo;
            return;
        }
    }
});
