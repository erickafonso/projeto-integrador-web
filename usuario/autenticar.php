<?php
@session_start();
require_once("conexao.php");

// Pegando os dados do usuário
$usuario = $_POST['usuario'];
$senha  = $_POST['senha'];
// $senha_crip = password_hash($senha,PASSWORD_DEFAULT,['cost'=>10]);
$senha_crip = sha1($senha);


//Efetuando consulta ao BD

$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND senha_crip = :senha");// Cria a consulta
$query->bindValue(":email", "$usuario");
$query->bindValue(":senha", "$senha_crip");
$query->execute();

$res = $query->fetchAll(PDO::FETCH_ASSOC);// Retorna null se não houver linhas, ou zero
// $senha_correta = password_verify($senha, $res[0]['senha_crip']);
$linhas = @count($res);// Testar passando valores corretos que retorna o total de linhas = 1 e erradas = 0


//Precisamos trabalhar com a tomada de decisão e direcionar o usuário
if($linhas > 0){

	if($res[0]['ativo'] != 'Sim'){
		echo '<script>window.alert("Seu acesso foi desativado!!")</script>'; 
		echo '<script>window.location="index.php"</script>';  
	}

	$_SESSION['nome'] = $res[0]['nome'];
	$_SESSION['id'] = $res[0]['id'];
	$_SESSION['nivel'] = $res[0]['nivel'];

	echo '<script>window.location="painel"</script>';
}else{
	echo '<script>window.alert("Dados Incorretos!!")</script>'; 
	echo '<script>window.location="index.php"</script>';  
}

?>