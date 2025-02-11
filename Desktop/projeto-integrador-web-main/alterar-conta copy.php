<?php
include('usuario/conexao.php');  // Inclui o arquivo de conexão com o banco de dados
include('modelo/Conta.php'); // Inclui o modelo Conta

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$contaModel = new Conta($pdo);

// Processa a alteração ou exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        // Obtém os dados do formulário e chama o método de alteração
        $contaModel->alterar(
            $_POST['idConta'],
            $_POST['nome'],
            $_POST['valor'],
            $_POST['descricao'],
            $_POST['dataPagamento'],
            $_POST['dataVencimento'],
            $_POST['categoria'],
            $_POST['formaPagamento']
        );
    } elseif (isset($_POST['deletar'])) {
        $contaModel->deletar($_POST['idConta']);
    }
}

// Lista todas as contas
$contas = $contaModel->listar();

// Funções para buscar IDs de categoria e forma de pagamento
function getIdCategoria($conn, $nome) {
    $id_categoria = 1;
    $sql = "SELECT idCategoria FROM categoria WHERE nome = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $stmt->bind_result($id_categoria);
    $stmt->fetch();
    $stmt->close();
    return $id_categoria ?? 1; // Retorna 1 se não encontrar
}

function getIdFormaPagamento($conn, $nome) {
    $id_forma_pagamento = 1;
    $sql = "SELECT idFormaPagamento FROM formaPagamento WHERE nome = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $stmt->bind_result($id_forma_pagamento);
    $stmt->fetch();
    $stmt->close();
    return $id_forma_pagamento ?? 1; // Retorna 1 se não encontrar
}

// Preencher os combo boxes
function preencherComboBoxFormaPagamento($conn) {
    $sql = "SELECT idFormaPagamento, nome FROM formaPagamento";
    $result = $conn->query($sql);
    $formas_pagamento = [];
    while ($row = $result->fetch_assoc()) {
        $formas_pagamento[] = $row;
    }
    return $formas_pagamento;
}

function preencherComboBoxCategoria($conn) {
    $sql = "SELECT idCategoria, nome FROM categoria";
    $result = $conn->query($sql);
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    return $categorias;
}

$formas_pagamento = preencherComboBoxFormaPagamento($conn);
$categorias = preencherComboBoxCategoria($conn);


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <title>Manutenção de Contas</title>
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
    <h1>Manutenção de Contas</h1>
    <table border="1">
        <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Valor</th>
            <th>Descrição</th>
            <th>Data Pagamento</th>
            <th>Data Vencimento</th>
            <th>Categoria</th>
            <th>Forma de Pagamento</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($contas as $conta): ?>
        <tr>
            <td><?php echo $conta['idConta']; ?></td>
            <td><?php echo $conta['nome']; ?></td>
            <td><?php echo $conta['valor']; ?></td>
            <td><?php echo $conta['descricao']; ?></td>
            <td><?php echo $conta['dataPagamento']; ?></td>
            <td><?php echo $conta['dataVencimento']; ?></td>
            <td><?php echo $conta['categoria']; ?></td>
            <td><?php echo $conta['formaPagamento']; ?></td>
            <td class="acoes">
            <!-- Container principal para organizar todos os elementos (inputs e botões) -->
            <div class="input-group">
                <!-- Container para o conjunto de campos (nome, valor, descrição) -->
                <div class="input-container">
                    <input type="text" name="nome" placeholder="Nome:" value="<?php echo $conta['nome'] ; ?>" required>
                    <input type="number" name="valor" placeholder="Valor:" value="<?php echo $conta['valor']; ?>" step="0.01" required>
                    <input type="text" name="descricao" placeholder="Descrição:" value="<?php echo $conta['descricao']; ?>">
                </div>

                <!-- Container para as duas datas -->
                <div class="input-container">
                    <input type="date" name="dataPagamento" value="<?php echo $conta['dataPagamento']; ?>">
                    <input type="date" name="dataVencimento" value="<?php echo $conta['dataVencimento']; ?>" required>
                </div>

                <!-- Container para categoria e forma de pagamento -->
                <div class="input-container">
                    <input type="number" name="categoria" value="<?php echo $conta['categoria']; ?>" required>
                    <input type="number" name="formaPagamento" value="<?php echo $conta['formaPagamento']; ?>" required>
                </div>

                <!-- Container para os botões de ação -->
                <div class="button-container">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="idConta" value="<?php echo $conta['idConta']; ?>">
                        <button type="submit" name="alterar" class="btn-alterar">Alterar</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="idConta" value="<?php echo $conta['idConta']; ?>">
                        <button type="submit" name="deletar" class="btn-deletar">Deletar</button>
                    </form>
                </div>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>

</body>
</html>
