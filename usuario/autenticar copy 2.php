<?php
@session_start();
require_once("conexao.php");

// Pegando os dados do usuário
$usuario = $_POST['usuario'];
$senha  = $_POST['senha'];

$hash = password_hash($senha, PASSWORD_DEFAULT);

// Exibe o hash
echo "Hash da senha: " . $hash;

// Efetuando consulta ao BD
$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
$query->bindValue(":email", $usuario);
$query->execute();

$res = $query->fetch(PDO::FETCH_ASSOC); // Use fetch ao invés de fetchAll
// Verifica se o resultado não é vazio
if ($res) {
    // Verifica a senha
    if (!password_verify($senha, $res['senha'])) {
		echo password_verify($senha, $res['senha']);
     //   echo '<script>window.alert("Dados Incorretos!!")</script>'; 
    //    echo '<script>window.location="index.php"</script>';  
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
    $idUsuarioLogado = $_SESSION['idUsuario'];
  //  echo $idUsuarioLogado . "<br>";
  //  echo "ID do Usuário Logado: " . $idUsuarioLogado . PHP_EOL;
   // var_dump($idUsuarioLogado);
    echo '<script>window.location="../index.html"</script>';
} else {
    echo '<script>window.alert("Dados Incorretos!!")</script>'; 
    echo '<script>window.location="index.php"</script>';  
}



//if ($linhas > 0) {
	/*
		if($res[0]['ativo'] != 'Sim'){
			echo '<script>window.alert("Seu acesso foi desativado!!")</script>'; 
			echo '<script>window.location="index.php"</script>';  
		}

		$_SESSION['nome'] = $res[0]['nome'];
		$_SESSION['id'] = $res[0]['id'];
		$_SESSION['nivel'] = $res[0]['nivel'];*/
//	echo $res[0]['senha'];
	//echo '<script>window.location="../index.html"</script>';
	//echo '<script>window.location="painel"</script>';
//} else {
	//var_dump($res);
	//echo '<script>window.alert("Dados Incorretos!!")</script>'; 
	//echo '<script>window.location="index.php"</script>';  
//}

?> 









