<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    // Caso não esteja logado, redireciona para a página de login
    header('Location: usuario/login.php');
    exit;
}

include ('usuario/conexao.php'); // Inclui o arquivo de conexão
include ('modelo/FormaPagamento.php'); // Inclui o modelo FormaPagamento

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

// Cria uma instância da classe FormaPagamento
$formaPagamentoModel = new FormaPagamento($pdo);

// Verifica se o formulário foi enviado (alteração ou exclusão)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $formaPagamentoModel->alterar($_POST['idFormaPagamento'], $_POST['nome'], $_SESSION['idUsuario']);
        echo "<script>alert('Forma de pagamento alterada com sucesso!');</script>";
    } elseif (isset($_POST['deletar'])) {
        $formaPagamentoModel->deletar($_POST['idFormaPagamento'], $_SESSION['idUsuario']);
        echo "<script>alert('Forma de pagamento deletada com sucesso!');</script>";
    }
}

// Lista as formas de pagamento do usuário logado
$formasPagamento = $formaPagamentoModel->listar($_SESSION['idUsuario']);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Manutenção de Formas de Pagamento</title>
    <script type="text/javascript">
        // Função de confirmação para o botão deletar
        function confirmarDeletar(event) {
            if (!confirm("Você tem certeza que deseja deletar esta forma de pagamento?")) {
                event.preventDefault();
            }
        }

        // Função de confirmação para o botão alterar
        function confirmarAlterar(event) {
            if (!confirm("Você tem certeza que deseja alterar esta forma de pagamento?")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
<header class="formas-pagamento">
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

<div id="conteudo">
    <h1>Manutenção de Formas de Pagamento</h1>
    <table border="1">
        <tr>
            <th class="formas-pagamento">Código</th>
            <th class="formas-pagamento" >Nome</th>
            <th class="formas-pagamento">Ações</th>
        </tr>
        <?php foreach ($formasPagamento as $formaPagamento): ?>
        <tr>
            <td><?php echo $formaPagamento['idFormaPagamento']; ?></td>
            <td><?php echo $formaPagamento['nome']; ?></td>
            <td>
                <!-- Formulário de alteração -->
                <form method="POST" style="display:inline;" onsubmit="confirmarAlterar(event)">
                    <input type="hidden" name="idFormaPagamento" value="<?php echo $formaPagamento['idFormaPagamento']; ?>">
                    <!-- Adicionando o label para o input "nome" -->
                    <label for="nome<?php echo $formaPagamento['idFormaPagamento']; ?>">Nome:</label>
                    <input type="text" id="nome<?php echo $formaPagamento['idFormaPagamento']; ?>" name="nome" value="<?php echo $formaPagamento['nome']; ?>">
                    <button type="submit" class="btn-alterar" name="alterar">Alterar</button>
                </form>
                
                <!-- Formulário de deleção -->
                <form method="POST" style="display:inline;" onsubmit="confirmarDeletar(event)">
                    <input type="hidden" name="idFormaPagamento" value="<?php echo $formaPagamento['idFormaPagamento']; ?>">
                    <button type="submit" class="btn-deletar" name="deletar">Deletar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
