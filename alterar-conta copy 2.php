<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    // Caso não esteja logado, redireciona para a página de login
    header('Location: usuario/index.php');  // Troque "login.php" pelo arquivo correto de login
    exit;
}
include('usuario/conexao.php');  // Inclui o arquivo de conexão com o banco de dados
include('modelo/Conta.php'); // Inclui o modelo Conta
include('modelo/Categoria.php'); // Inclui o modelo Conta
include('modelo/FormaPagamento.php'); // Inclui o modelo Conta

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

// Supondo que você tenha a conexão PDO já estabelecida, como $pdo
$categoria = new Categoria($pdo);
$formaPagamentoModel = new FormaPagamento($pdo);
// Chama a função para obter os nomes das categorias com os IDs como índice
$nomesCategorias = $categoria->getNomesComIds();
$formas = $formaPagamentoModel->getFormasComIds();
// Exibe os nomes das categorias com os IDs como índice

$contaModel = new Conta($pdo);

// Processa a alteração ou exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        // Lógica de alteração da conta
        if (isset($_POST['idConta'], $_POST['nome'], $_POST['valor'], $_POST['descricao'], 
                  $_POST['dataPagamento'], $_POST['dataVencimento'], $_POST['categoria'], $_POST['formaPagamento'])) {

            // Validar categoria, etc.
            // ...

            // Chamar método de alteração
            $contaModel->alterar(
                $_POST['idConta'],
                $_POST['nome'],
                $_POST['valor'],
                $_POST['descricao'],
                $_POST['dataPagamento'],
                $_POST['dataVencimento'],
                $_POST['categoria'], // Agora o valor vem diretamente do formulário
                $_POST['formaPagamento']
            );
        } else {
            echo "Erro: Todos os campos devem ser preenchidos!";
        }
    } elseif (isset($_POST['deletar'])) {
        // Lógica para deletar a conta
        if (isset($_POST['idConta']) && is_numeric($_POST['idConta'])) {
            $idConta = (int) $_POST['idConta'];  // Garantir que idConta seja numérico
            
            // Chamar método de deletação
            $contaModel->deletar($idConta);
        } else {
            echo "Erro: ID da conta inválido!";
        }
    }
}


$categoriaSelecionada = isset($_POST['categoria']) ? (int) $_POST['categoria'] : (isset($conta['categoria']) ? $conta['categoria'] : null);
// Lista todas as contas
$contas = $contaModel->listar();
echo '<pre>';
//var_dump($contas);  // Exibe o array de categorias com mais detalhes
echo '</pre>';
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
    
<form method="POST" action="alterar-conta.php">
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
            <td>
                <?php
                // Encontrar a categoria baseada no idCategoria
                foreach ($nomesCategorias as $categoriaItem) {
                    if ($categoriaItem->idCategoria == $conta['categoria']) {
                        echo $categoriaItem->nome; // Exibe o nome da categoria
                        break;
                    }
                }
                ?>
            </td>
            <td>
                
                <?php
                // Encontrar a categoria baseada no idCategoria
                foreach ($formas as $formaPagamentoItem) {
                    if ($formaPagamentoItem->idFormaPagamento == $conta['formaPagamento']) {
                        echo $formaPagamentoItem->nome; // Exibe o nome da categoria
                        break;
                    }
                }
                ?>
            </td>
            <td class="acoes">
                <!-- Formulário de alteração (mantenha o formulário para editar) -->
                <form method="POST" action="alterar-conta.php">
                    <div class="input-group">
                        <!-- Conjunto de campos (nome, valor, descrição) -->
                        <div class="input-container">
                            <input type="text" name="nome" placeholder="Nome:" value="<?php echo $conta['nome']; ?>" required>
                            <input type="number" name="valor" placeholder="Valor:" value="<?php echo $conta['valor']; ?>" step="0.01" required>
                            <input type="text" name="descricao" placeholder="Descrição:" value="<?php echo $conta['descricao']; ?>">
                        </div>

                        <!-- Conjunto de campos para as datas -->
                        <div class="input-container">
                            <input type="date" name="dataPagamento" value="<?php echo $conta['dataPagamento']; ?>" required>
                            <input type="date" name="dataVencimento" value="<?php echo $conta['dataVencimento']; ?>" required>
                        </div>

                        <!-- Conjunto de campos para categoria e forma de pagamento -->
                        <div class="input-container">
                            <select id="categoria" name="categoria" required>
                                <option value="">Selecione uma categoria</option>
                                <?php
                                // Preencher as opções do select com categorias
                                foreach ($nomesCategorias as $categoriaItem) {
                                    // Marca a categoria selecionada
                                    $selected = ($categoriaItem->idCategoria == $conta['categoria']) ? 'selected' : '';
                                    echo '<option value="' . $categoriaItem->idCategoria . '" ' . $selected . '>' . $categoriaItem->nome . '</option>';
                                }
                                ?>
                            </select>
                            <select id="formaPagamento" name="formaPagamento" required>
                                <option value="">Selecione uma Forma de pagamento</option>
                                <?php
                                // Preencher as opções do select com categorias
                                foreach ($formas as $formaPagamentoItem) {
                                    // Marca a categoria selecionada
                                    $selected = ($formaPagamentoItem->idFormaPagamento == $conta['formaPagamento']) ? 'selected' : '';
                                    echo '<option value="' . $formaPagamentoItem->idFormaPagamento . '" ' . $selected . '>' . $formaPagamentoItem->nome . '</option>';
                                }
                                ?>
                            </select>
                            
                           
                        </div>

                        <!-- Botão para Alterar -->
                        <div class="button-container">
                            <input type="hidden" name="idConta" value="<?php echo $conta['idConta']; ?>">
                            <button type="submit" name="alterar" class="btn-alterar">Alterar</button>
                            <!-- Botão para Deletar -->
                            <button type="submit" name="deletar" class="btn-deletar">Deletar</button>
                        </div>
                    </div>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</form>

</div>

</body>
</html>
