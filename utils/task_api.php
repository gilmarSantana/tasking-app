<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'response_type' => 'error',
        'msg' => 'Usuário não autenticado'
    ]);
}

$user_id = $_SESSION['user_id'];

include_once './pg_connector.php';

function getAllTaksByUserId($user_id, $order_by = 'date_created')
{
    $response = [];

    $pdo = pg_connector();

    print_r($pdo);


    return json_encode($response);
}

function createTask(int $user_id, string $title, string $description): string
{

    if (empty($title) || !$title) {
        return json_encode([
            'response_type' => 'error',
            'msg' => 'O título da tarefa está vazio'
        ]);
    }


    $pdo = pg_connector();

    try {
        $insert_task = "INSERT INTO tasks (created_by, title, description) VALUES (:created_by, :title, :description);";
        $stmt_insert_task = $pdo->prepare($insert_task);
        $stmt_insert_task->bindParam(':created_by', $user_id);
        $stmt_insert_task->bindParam(':title', $title);
        $stmt_insert_task->bindParam(':description', $description);
        $stmt_insert_task->execute();

        return json_encode([
            'response_type' => 'success',
            'msg' => 'Tarefa criada com sucesso',
            'task_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        return json_encode([
            'response_type' => 'error',
            'msg' => htmlspecialchars($e->getMessage())
        ]);
    }
}





try {
    $dados = json_decode(file_get_contents('php://input'), true);

    switch ($dados['action']) {
        case 'getAllTaksByUserId':
            echo getAllTaksByUserId($user_id);
            break;

        case 'createTask':
            $title = $dados['title'];
            $description = $dados['description'];
            echo createTask($user_id, $title, $description);
            break;
        default:

            echo json_encode([
                'response_type' => 'error',
                'msg' => 'O parâmetro de ação não foi identificado'
            ]);
            break;
    }
} catch (Exception $th) {
    echo json_encode([
        'response_type' => 'error',
        'msg' => htmlspecialchars($th->getMessage())
    ]);
}
