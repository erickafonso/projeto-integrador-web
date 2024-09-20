<?php
include_once ('conexao/conexao.php'); // Inclui a conexão com o banco de dados

$mensagem = ''; // Variável para armazenar mensagens de feedback
$exibirMensagem = false; // Variável para controlar a exibição do alert

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';

    // Validação
    if (empty($nome)) {
        $mensagem = "O campo 'nome' é obrigatório.";
    } else {
        // Prepara a consulta SQL usando prepared statements
        $sql = $conn->prepare("INSERT INTO categoria (nome) VALUES (?)");

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

    <script>
        // Função para mostrar o pop-up
        function mostrarMensagem(mensagem) {
            alert(mensagem);
        }
    </script>
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
        <form action="" method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Cadastrar</button>
            </div>
        </form>
        <?php if ($exibirMensagem): ?>
            <script>
                mostrarMensagem("<?php echo $mensagem; ?>");
            </script>
        <?php endif; ?>
    </div>
</body>
</html>