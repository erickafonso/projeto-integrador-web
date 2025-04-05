<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    // Caso não esteja logado, redireciona para a página de login
    header('Location: usuario/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/inside-pages.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Contador de gastos</title>
</head>
<body>
    <header class="categorias">
        <nav id="navMenu">
        <ul>
                <li><a href="index.php">Home</a></li>
                <li><a>|</a></li>
                <li><a href="contas.php">Contas</a></li>
                <li><a>|</a></li>
                <li><a href="despesas.php">Despesas</a></li>
                <li><a>|</a></li>
                <li><a href="formaPagamento.php">Formas de pagamento</a></li>
                <li><a>|</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li><a>|</a></li>
                <li><a href="relatoriosv2.php">Relatórios</a></li>
                <li><a>|</a></li>
                <li><a href="graficos.php">Gráficos</a></li>
                <li><a>|</a></li>
                <!-- Botão Sair com class 'logout' -->
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="button-container">
        <a href="cadastro-categorias.php">
            <button class="categorias">Cadastro</button>
        </a>
        <a href="alterar-categoria.php">
            <button class="categorias">Alterar Categorias</button>
        </a>
        
        </div>
        <script>
            window.onload = function () {
                    console.log("Window loaded");
                    const buttons = document.querySelectorAll('.button-container a');
                    buttons.forEach((button, index) => {
                        setTimeout(() => {
                            console.log("Showing button:", index);
                            button.classList.add('show');
                        }, index * 200); // Delay for each button
                    });
                };
        </script>
</body>
</html>