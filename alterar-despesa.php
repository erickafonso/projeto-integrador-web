<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contador";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verifica se o ID da despesa foi passado
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM despesa WHERE idDespesa=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $despesa = $result->fetch_assoc();
    } else {
        echo "Despesa não encontrada.";
        exit;
    }
} else {
    echo "ID não fornecido.";
    exit;
}

// Atualiza a despesa se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $despesaObj = new Despesa(
        $id,
        $_POST['nome'],
        $_POST['valor'],
        $_POST['descricao'],
        $_POST['dataPagamento'],
        $_POST['categoria'],
        $_POST['formaPagamento']
    );

    if ($despesaObj->atualizar($conn)) {
        echo "Despesa atualizada com sucesso!";
    } else {
        echo "Erro ao atualizar despesa.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Despesa</title>
</head>
<body>
    <h1>Atualizar Despesa</h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $despesa['id']; ?>">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" value="<?php echo $despesa['nome']; ?>" required><br>

        <label for="valor">Valor:</label>
        <input type="number" name="valor" value="<?php echo $despesa['valor']; ?>" required><br>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao" required><?php echo $despesa['descricao']; ?></textarea><br>

        <label for="dataPagamento">Data de Pagamento:</label>
        <input type="date" name="dataPagamento" value="<?php echo $despesa['dataPagamento']; ?>" required><br>

        <label for="categoria">Categoria:</label>
        <input type="text" name="categoria" value="<?php echo $despesa['categoria']; ?>" required><br>

        <label for="formaPagamento">Forma de Pagamento:</label>
        <input type="text" name="formaPagamento" value="<?php echo $despesa['formaPagamento']; ?>" required><br>

        <input type="submit" value="Atualizar Despesa">
    </form>
</body>
</html>

<?php
$conn->close();
?>
