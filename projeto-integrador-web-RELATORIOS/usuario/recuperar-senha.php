<?php
require_once("conexao.php");

$email = $_POST['email'];

$query = $pdo->query("SELECT * FROM usuarios WHERE email= '$email' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
    $telefone = $res[0] ['telefone'];

        $token = hash('sha256', time() );

        $q = $pdo->prepare("UPDATE usuarios SET token=? WHERE email=?");
        $q->execute([$token,$email]);

        $reset_link = $url_sistema.'resetar-senha.php'.'?email='.$email.'&token='.$token;

    // Envio do email
    $destinatario = $email;
    $assunto = $nome_sistema.' - Recuperação de Senha';
    $mensagem = 'Clique no link ao lado para atualizar sua senha: '.$reset_link;
    $cabecalhos = "From: ".$email_sistema;

    @mail($destinatario, $assunto, $mensagem, $cabecalhos);

    echo 'Recuperado com sucesso!';
}else{
    echo 'Esse email não está Cadastrado no Sistema!';
}

?>