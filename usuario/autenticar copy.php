<?php
@session_start();
require_once("conexao.php");

// Pegando os dados do usuário
$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

// Efetuando consulta ao BD
$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
$query->bindValue(":email", $usuario);
$query->execute();

$res = $query->fetch(PDO::FETCH_ASSOC); // Busca apenas um usuário
$linhas = @count($res); // Verifica se o usuário existe

// Tomada de decisão
if ($linhas > 0) {
    if (!password_verify($senha, $res[0]['senha'])) { // Verifica a senha
		
        echo '<script>window.alert("Dados Incorretos!!")</script>'; 
        echo '<script>window.location="index.php"</script>';  
        exit;
    }

    if ($res['ativo'] != 'Sim') {
        echo '<script>window.alert("Seu acesso foi desativado!!")</script>'; 
        echo '<script>window.location="index.php"</script>';  
        exit;
    }

    $_SESSION['nome'] = $res['nome'];
    $_SESSION['id'] = $res['id'];
    $_SESSION['nivel'] = $res['nivel'];

    echo '<script>window.location="../index.html"</script>';
} else {
    echo '<script>window.alert("Dados Incorretos!!")</script>'; 
    echo '<script>window.location="index.php"</script>';  
}
?>
