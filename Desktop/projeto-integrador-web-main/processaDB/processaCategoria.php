<?php

include_once ('../conexao/conexao.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Captura os dados do formulário
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';

    // Debug: Exibe os valores capturados
    echo "Nome: $nome<br/>";

    // Verifica se o campo obrigatório está preenchido
    if (empty($nome)) {
        die("O campo 'nome' é obrigatório.");
    }

    // Prepara a consulta SQL usando prepared statements
    $sql = $conn->prepare("INSERT INTO categoria (nome) VALUES (?)");
    $sql->bind_param("s", $nome);

    // Executa a consulta e verifica o sucesso
    if ($sql->execute()) {
        echo "Categoria cadastrada com sucesso!";
    } else {
        echo "Erro: " . $sql->error;
    }

    // Fecha a consulta e a conexão
    $sql->close();
    $conn->close();
}
?>
