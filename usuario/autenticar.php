<?php
@session_start();
require_once("conexao.php");

// Pegando os dados do usuário
$usuario = $_POST['usuario'];
$senha  = $_POST['senha'];

// Efetuando consulta ao BD
$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
$query->bindValue(":email", $usuario);
$query->execute();

$res = $query->fetch(PDO::FETCH_ASSOC); // Use fetch ao invés de fetchAll

// Verifica se o resultado não é vazio
if ($res) {
    // Verifica a senha
    if (!password_verify($senha, $res['senha'])) {
        // Senha incorreta
        echo '<script>window.alert("Dados Incorretos!!")</script>';
        echo '<script>window.location="index.php"</script>';
        exit;
    }

    // Verifica se o acesso está ativo
    if ($res['ativo'] != 'Sim') {
        echo '<script>window.alert("Seu acesso foi desativado!!")</script>';
        echo '<script>window.location="index.php"</script>';
        exit;
    }

    // Armazena informações da sessão
    $_SESSION['nome'] = $res['nome'];
    $_SESSION['idUsuario'] = $res['idUsuario'];
    $_SESSION['nivel'] = $res['nivel'];

    // Redireciona para a página principal após o login
    header('Location: ../index.html'); 
    exit;
} else {
    // Caso o usuário não exista
    echo '<script>window.alert("Dados Incorretos!!")</script>';
    echo '<script>window.location="index.php"</script>';
    exit;
}
?>
