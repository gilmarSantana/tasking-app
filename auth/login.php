<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasking-App Login</title>
</head>

<body>
    <header>
        here is the haeder
    </header>
    <main>
        <div>
            <form action="./login.auth.php" method="POST">
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required autocomplete="on" autofocus>
                </div>

                <div>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div>
                    <button type="submit">Acessar</button>
                </div>
            </form>
        </div>
    </main>
    <footer>
        here is the footer
    </footer>
</body>

</html>