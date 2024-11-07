<?php
include('usuario/conexao.php');  // Inclui o arquivo de conexão com o banco de dados
include('modelo/Despesa.php');   // Inclui o modelo da tabela Despesa

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$despesaModel = new Despesa($pdo);  // Cria uma instância do modelo Despesa

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        // Chama a função de alterar despesa
        $despesaModel->alterar($_POST['idDespesa'], $_POST['nome'], $_POST['valor'], $_POST['descricao'], $_POST['dataPagamento'], $_POST['categoria'], $_POST['formaPagamento']);
    } elseif (isset($_POST['deletar'])) {
        // Chama a função de deletar despesa
        $despesaModel->deletar($_POST['idDespesa']);
    }
}

$despesas = $despesaModel->listar();  // Lista todas as despesas
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <title>Manutenção de Despesas</title>
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
    <h1>Manutenção de Despesas</h1>
    <table border="1">
        <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Valor</th>
            <th>Descrição</th>
            <th>Data de Pagamento</th>
            <th>Categoria</th>
            <th>Forma de Pagamento</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($despesas as $despesa): ?>
        <tr>
            <td><?php echo $despesa['idDespesa']; ?></td>
            <td><?php echo $despesa['nome']; ?></td>
            <td><?php echo number_format($despesa['valor'], 2, ',', '.'); ?></td>
            <td><?php echo $despesa['descricao']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($despesa['dataPagamento'])); ?></td>
            <td><?php echo $despesa['categoria']; ?></td>
            <td><?php echo $despesa['formaPagamento']; ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idDespesa" value="<?php echo $despesa['idDespesa']; ?>">
                    <input type="text" name="nome" value="<?php echo $despesa['nome']; ?>" required>
                    <input type="number" step="0.01" name="valor" value="<?php echo $despesa['valor']; ?>" required>
                    <input type="text" name="descricao" value="<?php echo $despesa['descricao']; ?>">
                    <input type="date" name="dataPagamento" value="<?php echo $despesa['dataPagamento']; ?>" required>
                    <input type="number" name="categoria" value="<?php echo $despesa['categoria']; ?>" required>
                    <input type="number" name="formaPagamento" value="<?php echo $despesa['formaPagamento']; ?>" required>
                    <button type="submit" name="alterar">Alterar</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idDespesa" value="<?php echo $despesa['idDespesa']; ?>">
                    <button type="submit" name="deletar">Deletar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
