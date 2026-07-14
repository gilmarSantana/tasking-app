<?php
session_start();
$logged_name = $_SESSION['name'];
$logged_email = $_SESSION['email'];

include_once '../utils/pg_connector.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasking-App Home</title>
    <link rel="stylesheet" href="../styles/index.css">
</head>

<body>
    <header>
        Olá, <?= $logged_name; ?>.
    </header>
    <main>
        <button id="btn-test">Teste</button>
        <div id="main-separator-view">
            <div id="div-form-add-task">
                <h1>Adicionar tarefa</h1>

                <form action="#" method="POST" id="form-add-task">
                    <div class="input-group">
                        <label for="title">Título</label>
                        <input type="text" name="title" id="title" autofocus required autocomplete="off" placeholder="Informe um título para a tarefa" tabindex="1" alt="Título para a tarefa">
                    </div>

                    <div class="input-group">
                        <label for="description">Descrição</label>
                        <textarea name="description" id="description" placeholder="Informe uma descrição para a tarefa" tabindex="2" rows="8"></textarea>
                    </div>

                    <div class="input-group">
                        <button id="btn-save-task" type="submit">Salvar tarefa</button>
                    </div>
                </form>
            </div>
            <div id="list-of-tasks">
                <h1>Suas tarefas</h1>

                <div id="list-of-user-tasks">
                    
                </div>
            </div>
        </div>
    </main>

    <script src="../js/index.js"></script>
</body>

</html>