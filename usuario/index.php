<?php
require_once("conexao.php");

$query = $pdo->query("SELECT * FROM usuarios");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);

$senha = '123';
$senha_crip = sha1($senha);

if($linhas == 0){
    $pdo->query("INSERT INTO usuarios SET nome='$nome_sistema', email = '$email_sistema', senha= '', senha_crip= '$senha_crip', nivel = 'Administrador', ativo = 'Sim', foto = 'sem-foto.jpg', telefone = '$telefone_sistema', data = curDate() ");

}

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title><?php echo $nome_sistema ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <script src=//code.jquery.com/jquery-1.11.1.min.js></script>

    <link rel="stylesheet" type="text/css" href="css/style.css">

    <link rel="shortcut icon" type="image/x-icon" href="img/icone.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
</head>
<body>
    <div class="login">
        <div class="form">

            <img src="img/logo.png" class="imagem">

            <form action="autenticar.php" method="post">
                <input type="email" name="usuario" placeholder="Seu Email" required>

                <input type="password" name="senha" placeholder="Senha" required>

                <button>Login</button>            
            </form>
            <br>

            <p class="recuperar"><a title="Clique para recuperar a senha" href="" data-bs-toggle="modal" data-bs-target="#exempleModal">Recuperar Senha</a></p>
        </div>
        <!-- fecha a div da class form -->
    </div> 
    <!-- fecha a div da class login -->
</body>
</html>

<!-- Modal  -->
 <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel", aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content form">
            <form method="post" id="form-recuperar">
                <div class="modal-body">
                    <form method="post" id="form-recuperar">
                        <input placeholder="Digite seu email" class="form-control" type="email" name="email" id="email-recuperar" required>
                        <button type="submit">Recuperar</button>
                    </form>
                </div>
                <!-- Fecha a div da class modal-body -->
                 <br>
                 <small><div id="mensagem-recuperar" align="center"></div></small>
            </form>
        </div>
        <!-- Fecha a div da class modal-content form -->
    </div>
    <!-- Fecha a div da class modal-dialog -->
 </div>
 <!-- Fecha a div da class modal fade -->


 <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>


 <script type="text/javascript">
    $("#form-recuperar").submit(function() {

        $('#mensagem-recuperar').text('Enviando!!!');

        event.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: "recuperar-senha.php",
            type: 'POST',
            data: formData,

            success: function(mensagem){
                $('#mensagem-recuperar').text('');
                $('#mensagem-recuperar').removeClass()
                if (mensagem.trim() == "Recuperado com Sucesso"){
                    $('#email-recuperar').val('');
                    $('#mensagem-recuperar').addClass('text-success')
                    $('#mensagem-recuperar').text('Sua senha foi enviada para o email')

                } else{
                    $('#mensagem-recuperar').addClass('text-danger')
                    $('#mensagem-recuperar').text(mensagem)
                }

            },

            cache: false,
            contentType: false,
            processData: false,

        });

    });
 </script>
