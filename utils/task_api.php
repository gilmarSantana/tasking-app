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

// Single responsability - Só pega todas as tarefas do usuário
// Análise: Em todos os status?
function getAllTaksByUserId(int $user_id, string $order_by = 'date_created'): string
{

    try {
        $pdo = pg_connector();

        $query = "SELECT * FROM tasks WHERE created_by = :user_id AND status in('done', 'pending') ORDER BY 
            CASE WHEN status = 'pending' THEN 1 ELSE 2 end;";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode([
            'response_type' => 'success',
            'msg' => 'Tarefas carregas com sucesso',
            'tasks' => json_encode($result, JSON_UNESCAPED_UNICODE)
        ]);
    } catch (PDOException $e) {
        return json_encode([
            'response_type' => 'error',
            'msg' => htmlspecialchars($e->getMessage())
        ]);
    }
}

// Single responsability - Só cria a task no banco de dados
function createTask(int $user_id, string $title, string $description): string
{
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



// Single responsability: Completar uma tarefa (Status: done)
function completeTask(int $task_id, int $user_id)
{
    // Checar se tarefa pertence ao usuário logado
    if (!check_task_owner($task_id, $user_id)) {
        return json_encode([
            'response_type' => 'error',
            'msg' => 'Você não tem permissão para alterar esta tarefa'
        ]);
    }

    try {
        $pdo = pg_connector();

        $query = "UPDATE tasks SET status = :status WHERE id = :task_id;";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':status', 'done');
        $stmt->bindParam(':task_id', $task_id);
        $stmt->execute();

        if (!$stmt->rowCount() > 0) {
            return json_encode([
                'response_type' => 'error',
                'msg' => 'Erro ao atualizar status da tarefa',
                'task_id' => $task_id
            ]);
        }

        return json_encode([
            'response_type' => 'success',
            'msg' => 'Tarefa finalizada com sucesso',
            'task_id' => $task_id
        ]);
    } catch (PDOException $e) {
        return json_encode([
            'response_type' => 'error',
            'msg' => htmlspecialchars($e->getMessage())
        ]);
    }
}


// Single responsability: Checar se task id pertence a user_id
function check_task_owner(int $task_id, int $user_id)
{
    $pdo = pg_connector();

    try {
        $query = "SELECT id, created_by FROM tasks WHERE id = :task_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->execute();

        if ($stmt->rowCount() <= 0) {
            return false;
        }

        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task['created_by'] !== $user_id) {
            return false;
        }

        return true;
    } catch (PDOException $e) {
        throw new Exception("Error Processing Request:" . $e->getMessage(), 1);
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

        case 'completeTask':
            $task_id = $dados['task_id'];
            echo completeTask($task_id, $user_id);
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
