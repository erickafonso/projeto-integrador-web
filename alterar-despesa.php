<?php
include('usuario/conexao.php');  // Inclui o arquivo de conexão com o banco de dados
include('modelo/Despesa.php');   // Inclui o modelo da tabela Despesa
include('modelo/Categoria.php'); // Inclui o modelo da tabela Categoria
include('modelo/FormaPagamento.php'); // Inclui o modelo da tabela FormaPagamento

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$despesaModel = new Despesa($pdo);  // Cria uma instância do modelo Despesa
$categoriaModel = new Categoria($pdo);  // Cria uma instância do modelo Categoria
$formaPagamentoModel = new FormaPagamento($pdo); // Cria uma instância do modelo FormaPagamento

// Recupera as listas de categorias e formas de pagamento
$categorias = $categoriaModel->listar();
$formasPagamento = $formaPagamentoModel->listar();

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
            <td>
                <?php
                    // Substituir o valor numérico pela categoria correspondente
                    $categoriaNome = '';
                    foreach ($categorias as $categoria) {
                        if ($categoria['idCategoria'] == $despesa['categoria']) {
                            $categoriaNome = $categoria['nome'];
                            break;
                        }
                    }
                    echo $categoriaNome;
                ?>
            </td>
            <td>
                <?php
                    // Substituir o valor numérico pela forma de pagamento correspondente
                    $formaPagamentoNome = '';
                    foreach ($formasPagamento as $forma) {
                        if ($forma['idFormaPagamento'] == $despesa['formaPagamento']) {
                            $formaPagamentoNome = $forma['nome'];
                            break;
                        }
                    }
                    echo $formaPagamentoNome;
                ?>
            </td>
            <td>
                <!-- Formulário para Alterar ou Deletar -->
                <form method="POST" style="display:inline;">
                <div class="input-group">
                    <div class="input-container">
                        <input type="hidden" name="idDespesa" value="<?php echo $despesa['idDespesa']; ?>">

                        <!-- Inputs para alteração -->
                        <input type="text" name="nome" value="<?php echo $despesa['nome']; ?>" required>
                        <input type="number" step="0.01" name="valor" value="<?php echo $despesa['valor']; ?>" required>
                        <input type="text" name="descricao" value="<?php echo $despesa['descricao']; ?>">
                        </div>
                        <div class="input-container">
                        <input type="date" name="dataPagamento" value="<?php echo $despesa['dataPagamento']; ?>" required>
                        </div>
                        <!-- Combo Box Categoria -->
                        <div class="input-container">
                        <select name="categoria" required>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['idCategoria']; ?>" <?php echo ($categoria['idCategoria'] == $despesa['categoria']) ? 'selected' : ''; ?>>
                                    <?php echo $categoria['nome']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Combo Box Forma de Pagamento -->
                        <select name="formaPagamento" required>
                            <?php foreach ($formasPagamento as $forma): ?>
                                <option value="<?php echo $forma['idFormaPagamento']; ?>" <?php echo ($forma['idFormaPagamento'] == $despesa['formaPagamento']) ? 'selected' : ''; ?>>
                                    <?php echo $forma['nome']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Botões -->
                    <div class="button-container">
                        <button type="submit" name="alterar" class="btn-alterar">Alterar</button>
                        <button type="submit" name="deletar" class="btn-deletar">Deletar</button>
                    </div>
                    </div>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
