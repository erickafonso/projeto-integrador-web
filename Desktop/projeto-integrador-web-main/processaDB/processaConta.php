<?php

include_once('conexao.php');
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Captura os dados do formulário
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : '';
    $numero = isset($_POST['numero']) ? $_POST['numero'] : '';
    $fone = isset($_POST['fone']) ? $_POST['fone'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $cpf = isset($_POST['cpf']) ? $_POST['cpf'] : '';
    $rg = isset($_POST['rg']) ? $_POST['rg'] : '';

    // Debug: Exibe os valores capturados
    echo "Nome: $nome<br/>";
    echo "Endereço: $endereco<br/>";
    echo "Número: $numero<br/>";
    echo "Telefone: $fone<br/>";
    echo "Email: $email<br/>";
    echo "CPF: $cpf<br/>";
    echo "RG: $rg<br/>";

    // Verifica se todos os campos obrigatórios estão preenchidos
    if (empty($nome) || empty($endereco) || empty($numero) || empty($fone) || empty($email) || empty($cpf) || empty($rg)) {
        die("Todos os campos são obrigatórios.");
    }

    // Prepara a consulta SQL usando prepared statements
    $sql = $conn->prepare("INSERT INTO conta (nome, endereco, numero, fone, email, cpf, rg) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("ssissss", $nome, $endereco, $numero, $fone, $email, $cpf, $rg);

    // Executa a consulta e verifica o sucesso
    if ($sql->execute()) {
        echo "Cadastrado com sucesso!";
    } else {
        echo "Erro: " . $sql->error;
    }

    // Fecha a consulta e a conexão
    $sql->close();
    $conn->close();
}
?>
