<?php
session_start();
$logged_name = $_SESSION['name'];
$logged_email = $_SESSION['email']; 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasking-App Home</title>
</head>
<body>
    <header>
        Olá, <?= $logged_name; ?>.
    </header>
    <main>
        Suas tarefas
    </main>
    
</body>
</html>