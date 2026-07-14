<?php

include_once "../utils/pg_connector.php";


// Receber dados da requisição POST

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $received_email = $_POST['email'] ?? '';
    $received_password = $_POST['password'] ?? null;    

    // Sanitização e validação do email
    $sanitized_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    

    if (!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
        echo "O email está em formato incorreto: $received_email";
        exit(500);
    }

    // Verificação da senha informada
    if(empty($received_password)) {
        echo "A senha é necessária para a autenticaçao.";
        exit(404);
    }

    // Autenticação das credenciais recebidas com o banco de dados
    $pdo = pg_connector('tasking_app');

    $search_credentials = "SELECT id, email, password, name, is_active FROM users WHERE email = :email;";
    $stmt_search_credentials = $pdo->prepare($search_credentials);
    $stmt_search_credentials->bindParam(':email', $sanitized_email);
    $stmt_search_credentials->execute();
    $result_search_credentials = $stmt_search_credentials->fetch(PDO::FETCH_ASSOC);

    $db_email = $result_search_credentials['email'] ?? null;
    $db_password = $result_search_credentials['password'] ?? null;
    $db_name = $result_search_credentials['name'] ?? null;    
    $db_is_active = $result_search_credentials['is_active'] ?? null;

    // Verifica se o email existe no banco de dados
    if(empty($db_email) || !$db_email || $db_email === null) {
        echo "Credenciais inválidas!";
        exit(404);
    }    

    if(!password_verify($received_password, $db_password)) {
        echo "Credenciais inválidas!";
        exit(404);
    }

    if($db_is_active !== true) {
        echo "Este email está inativo, contate o suporte.";
        exit(401);
    }

    session_start();
    $db_user_id = $result_search_credentials['id'];

    $_SESSION['name'] = $db_name;
    $_SESSION['email'] = $db_email;
    $_SESSION['user_id'] = $db_user_id;
    
    header('Location: ../app/index.php');
    exit(200);
}
