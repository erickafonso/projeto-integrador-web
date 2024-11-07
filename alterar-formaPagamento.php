<?php
include ('usuario/conexao.php'); // Inclui o arquivo de conexão
include('modelo/FormaPagamento.php'); // Inclui o modelo FormaPagamento

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$formapagamentoModel = new FormaPagamento($pdo);

// Processa a alteração ou exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $formapagamentoModel->alterar($_POST['idFormaPagamento'], $_POST['nome']);
    } elseif (isset($_POST['deletar'])) {
        $formapagamentoModel->deletar($_POST['idFormaPagamento']);
    }
}

// Lista todas as formas de pagamento
$formaspagamento = $formapagamentoModel->listar();
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
        <?php foreach ($formaspagamento as $forma): ?>
        <tr>
            <td><?php echo $forma['idFormaPagamento']; ?></td>
            <td><?php echo $forma['nome']; ?></td>
            <td>
                <!-- Formulário para alterar o nome da forma de pagamento -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idFormaPagamento" value="<?php echo $forma['idFormaPagamento']; ?>">
                    <input type="text" name="nome" value="<?php echo $forma['nome']; ?>">
                    <button type="submit" name="alterar">Alterar</button>
                </form>
                
                <!-- Formulário para deletar a forma de pagamento -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idFormaPagamento" value="<?php echo $forma['idFormaPagamento']; ?>">
                    <button type="submit" name="deletar">Deletar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
