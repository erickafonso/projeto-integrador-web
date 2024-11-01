<?php
include ('usuario/conexao.php'); // Inclui o arquivo de conexão

include ('modelo/Categoria.php'); // Inclui o modelo Categoria

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$categoriaModel = new Categoria($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $categoriaModel->alterar($_POST['idCategoria'], $_POST['nome']);
    } elseif (isset($_POST['deletar'])) {
        $categoriaModel->deletar($_POST['idCategoria']);
    }
}

$categorias = $categoriaModel->listar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/styles.css">
   <!-- <link rel="stylesheet" href="css/inside-pages.css">-->

    <title>Manutenção de Categorias</title>
</head>
<body>
    <h1>Manutenção de Categorias</h1>
    <table border="1">
        <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($categorias as $categoria): ?>
        <tr>
            <td><?php echo $categoria['idCategoria']; ?></td>
            <td><?php echo $categoria['nome']; ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idCategoria" value="<?php echo $categoria['idCategoria']; ?>">
                    <input type="text" name="nome" value="<?php echo $categoria['nome']; ?>">
                    <button type="submit" name="alterar">Alterar</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="idCategoria" value="<?php echo $categoria['idCategoria']; ?>">
                    <button type="submit" name="deletar">Deletar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
