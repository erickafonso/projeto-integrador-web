<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    // Caso não esteja logado, redireciona para a página de login
    header('Location: usuario/login.php');
    exit;
}

include ('usuario/conexao.php'); // Inclui o arquivo de conexão
include ('modelo/Categoria.php'); // Inclui o modelo Categoria

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$categoriaModel = new Categoria($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $categoriaModel->alterar($_POST['idCategoria'], $_POST['nome']);
        echo "<script>alert('Categoria alterada com sucesso!');</script>";
    } elseif (isset($_POST['deletar'])) {
        $categoriaModel->deletar($_POST['idCategoria']);
        echo "<script>alert('Categoria deletada com sucesso!');</script>";
    }
}

$categorias = $categoriaModel->listar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Manutenção de Categorias</title>
    <script type="text/javascript">
        // Função de confirmação para o botão deletar
        function confirmarDeletar(event) {
            if (!confirm("Você tem certeza que deseja deletar esta categoria?")) {
                event.preventDefault();
            }
        }

        // Função de confirmação para o botão alterar
        function confirmarAlterar(event) {
            if (!confirm("Você tem certeza que deseja alterar esta categoria?")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
<header class="categorias">
    <nav id="navMenu">
    <ul>
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a>|</a></li>
                <li><a href="contas.php">Contas</a></li>
                <li><a>|</a></li>
                <li><a href="despesas.php">Despesas</a></li>
                <li><a>|</a></li>
                <li><a href="formaPagamento.php">Formas de pagamento</a></li>
                <li><a>|</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li><a>|</a></li>
                <li><a href="tabelas.php">Tabelas</a></li>
                <li><a>|</a></li>
                <li><a href="graficos.php">Gráficos</a></li>
                <li><a>|</a></li>
                <!-- Botão Sair com class 'logout' -->
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
    </nav>
</header>

<div id="conteudo">
    <h1>Manutenção de Categorias</h1>
    <table border="1">
        <tr>
            <th class="categorias">Código</th>
            <th class="categorias">Nome</th>
            <th class="categorias">Ações</th>
        </tr>
        <?php foreach ($categorias as $categoria): ?>
        <tr>
            <td><?php echo $categoria['idCategoria']; ?></td>
            <td><?php echo $categoria['nome']; ?></td>
            <td>
                <!-- Formulário de Alteração -->
                <form method="POST" style="display:inline;" onsubmit="confirmarAlterar(event)">
                    <input type="hidden" name="idCategoria" value="<?php echo $categoria['idCategoria']; ?>">
                    <label for="nomeCategoria<?php echo $categoria['idCategoria']; ?>">Nome:</label>
                    <input type="text" id="nomeCategoria<?php echo $categoria['idCategoria']; ?>" name="nome" value="<?php echo $categoria['nome']; ?>">
                    <button type="submit" class="btn-alterar" name="alterar">Alterar</button>
                </form>
                <!-- Formulário de Deleção -->
                <form method="POST" style="display:inline;" onsubmit="confirmarDeletar(event)">
                    <input type="hidden" name="idCategoria" value="<?php echo $categoria['idCategoria']; ?>">
                    <button type="submit" class="btn-deletar" name="deletar">Deletar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
