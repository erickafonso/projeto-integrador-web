<?php
include_once ('conexao/conexao.php'); // Inclui a conexão com o banco de dados

$mensagem = ''; // Variável para armazenar mensagens de feedback
$exibirMensagem = false; // Variável para controlar a exibição do alert

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    // Validação
    if (empty($nome)) {
        $mensagem = "O campo 'nome' é obrigatório.";
    } else {
        // Prepara a consulta SQL usando prepared statements
        $sql = $conn->prepare("INSERT INTO categoria (nome, valor, descricao, 
        data-pagamento, data-vencimento, categoria, forma-pagamento ) VALUES (?, ? , ?, ?, ?, ?, ?)");

        // Verifica se a preparação foi bem-sucedida
        if (!$sql) {
            echo "Erro ao preparar a consulta: " . $conn->error;
            exit;
        }

        $sql->bind_param("s", $nome);

        // Executa a consulta e verifica o sucesso
        if ($sql->execute()) {
            $mensagem = "Categoria cadastrada com sucesso!";
        } else {
            $mensagem = "Erro: " . $sql->error;
        }

        // Fecha a consulta
        $sql->close();
    }

    // Fecha a conexão
    $conn->close();
    $exibirMensagem = true;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Contador de gastos</title>
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
    <div class="form-container">
        <h2>Cadastro de Pagamento</h2>
        <form action="/enviar" method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="valor">Valor:</label>
                <input type="number" id="valor" name="valor" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <input type="text" id="descricao" name="descricao" required>
            </div>
            <div class="form-group">
                <label for="data-pagamento">Data de Pagamento:</label>
                <input type="date" id="data-pagamento" name="data_pagamento" required>
            </div>
            <div class="form-group">
                <label for="data-vencimento">Data de Vencimento:</label>
                <input type="date" id="data-vencimento" name="data_vencimento" required>
            </div>
            <div class="form-group">
                <label for="categoria">Categoria:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecione uma categoria</option>
                    <option value="alimentacao">Alimentação</option>
                    <option value="transporte">Transporte</option>
                    <option value="saude">Saúde</option>
                    <option value="entretenimento">Entretenimento</option>
                </select>
            </div>
            <div class="form-group">
                <label for="forma-pagamento">Forma de Pagamento:</label>
                <select id="forma-pagamento" name="forma_pagamento" required>
                    <option value="">Selecione uma forma de pagamento</option>
                    <option value="credito">Crédito</option>
                    <option value="debito">Débito</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Cadastrar</button>
            </div>
        </form>
    </div>
</body>
</html>