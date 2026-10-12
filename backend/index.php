<?php
declare(strict_types=1);

// Ponto de entrada da API: prepara o ambiente e delega às rotas.
require_once __DIR__ . '/startup/bootstrap.php';
require_once __DIR__ . '/routes/api.php';
