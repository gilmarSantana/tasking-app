<?php

function pg_connector(string $dbname = 'tasking_app')
{
    $env = parse_ini_file(__DIR__ . '/../.env');

    $host = $env['DB_HOST'];
    $user = $env['DB_USER'];
    $pass = $env['DB_PASS'];
    $db   = $dbname ?? $env['DB_NAME'];

    // Configurações extras do PDO (Melhores práticas para segurança e estabilidade)
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Transforma erros do banco em Exceções do PHP
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna os dados como array associativo por padrão
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Desativa a emulação para usar Prepared Statements reais (Proteção contra SQL Injection)
    ];

    $dsn = "pgsql:host=$host;dbname=$db;";

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);

        if ($pdo) {
            return $pdo;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        // Se der erro, ele cai aqui. 
        // IMPORTANTE: Em produção, nunca dê echo em $e->getMessage() para o usuário, pois pode vazar sua senha!
        echo $e->getMessage();
        die("Erro ao conectar com o banco de dados. Tente novamente mais tarde.");
    }
}
