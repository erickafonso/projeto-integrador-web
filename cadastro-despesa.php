<?php
include_once('conexao/conexao.php'); // Inclui a conexão com o banco de dados

$mensagem = ''; // Variável para armazenar mensagens de feedback
$exibirMensagem = false; // Variável para controlar a exibição do alert

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtém os dados do formulário
    $nome = $_POST['nome'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $data_pagamento = $_POST['data_pagamento'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $forma_pagamento = $_POST['forma_pagamento'] ?? '';

    // Validação
    if (empty($nome) || empty($valor) || empty($descricao) || empty($data_pagamento) || empty($categoria) || empty($forma_pagamento)) {
        $mensagem = "Todos os campos são obrigatórios.";
    } else {

        $idFormaPagamento = getIdFormaPagamento($conn, $forma_pagamento);
        
        $idCategoria = getIdCategoria($conn, $categoria);
        
        // Prepara a consulta SQL usando prepared statements
        $sql = $conn->prepare("INSERT INTO despesa (nome, valor, descricao, dataPagamento, categoria, formaPagamento) VALUES (?, ?, ?, ?, ?, ?)");

        // Verifica se a preparação foi bem-sucedida
        if (!$sql) {
            echo "Erro ao preparar a consulta: " . $conn->error;
            exit;
        }

        // Vincula os parâmetros
        $sql->bind_param("sdssss", $nome, $valor, $descricao, $data_pagamento, $idCategoria, $idFormaPagamento);

        // Executa a consulta e verifica o sucesso
        if ($sql->execute()) {
            $mensagem = "Despesa cadastrada com sucesso!";
        } else {
            $mensagem = "Erro: " . $sql->error;
        }

        // Fecha a consulta
        $sql->close();
    }

    // Fecha a conexão
   // $conn->close();
    $exibirMensagem = true;
    if ($exibirMensagem) {
        echo "<script>alert('$mensagem');</script>";
    }
}

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Contador de despesa</title>
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
    <div class="form-container"id="formContainer">
        <h2>Cadastro de despesa</h2>
        <form action="" method="post">
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
                <label for="categoria">Categoria:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['nome']; ?>"><?php echo $cat['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="forma-pagamento">Forma de Pagamento:</label>
                <select id="forma-pagamento" name="forma_pagamento" required>
                    <option value="">Selecione uma forma de pagamento</option>
                    <?php foreach ($formas_pagamento as $fp): ?>
                        <option value="<?php echo $fp['nome']; ?>"><?php echo $fp['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Cadastrar</button>
            </div>
        </form>
        
    </div>
    <script>
        window.onload = function () {
            const formContainer = document.getElementById('formContainer');
            formContainer.classList.add('show'); // Adiciona a classe para a transição
        };
    </script>
</body>
</html>