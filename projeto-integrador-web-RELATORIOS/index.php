
<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    header('Location: usuario/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/home.css">
  <link rel="stylesheet" href="css/styles.css"> 
  
    <title>Contador de gastos</title>
</head>
<body>
    <header>
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
        <a href="contas.html">
            <button class="contas">Contas</button>
        </a>
        <a href="despesas.html">
            <button class="despesas">Despesas</button>
        </a>
        <a href="formaPagamento.html">
            <button class="formas-pagamento">Formas de Pagamento</button>
        </a>
        <a href="categorias.html">
            <button class="categorias">Categorias</button>
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