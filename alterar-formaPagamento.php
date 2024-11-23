<?php
include ('usuario/conexao.php'); // Inclui o arquivo de conexão
include ('modelo/FormaPagamento.php'); // Inclui o modelo FormaPagamento

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$formaPagamentoModel = new FormaPagamento($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $formaPagamentoModel->alterar($_POST['idFormaPagamento'], $_POST['nome']);
    } elseif (isset($_POST['deletar'])) {
        $formaPagamentoModel->deletar($_POST['idFormaPagamento']);
    }
}

$formasPagamento = $formaPagamentoModel->listar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <title>Manutenção de Formas de Pagamento</title>
</head>
<body>
<header>
        <nav id="navMenu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a>|</a></li>
                <li><a href="contas.html">Contas</a></li>
                <li><a>|</a></li>
                <li><a href="despesas.html">Despesas</a></li>
                <li><a>|</a></li>
                <li><a href="formaPagamento.html">Formas de pagamento</a></li>
                <li><a>|</a></li>
                <li><a href="categorias.html">Categorias</a></li>
                <li><a>|</a></li>
            </ul>
        </nav>
</header>

<div id="conteudo">
    <h1>Manutenção de Formas de Pagamento</h1>
    <table border="1">
        <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($formasPagamento as $formaPagamento): ?>
        <tr>
            <td><?php echo $formaPagamento['idFormaPagamento']; ?></td>
            <td><?php echo $formaPagamento['nome']; ?></td>
            <td>
                <!-- Formulário de alteração -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idFormaPagamento" value="<?php echo $formaPagamento['idFormaPagamento']; ?>">
                    <input type="text" name="nome" value="<?php echo $formaPagamento['nome']; ?>">
                    <button type="submit" class="btn-alterar" name="alterar">Alterar</button>
                </form>
                
                <!-- Formulário de deleção -->
                <form method="POST" style="display:inline;">
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
