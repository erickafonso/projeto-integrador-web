<?php
require_once("conexao.php");

if( !isset($_REQUEST['email']) || !isset($_REQUEST['token']) ){
    header('location: '.$url_sistema);
    exit;
}

$statement = $pdo->prepare("SELECT * FROM usuarios WHERE email=? AND token=?");
$statement->execute( [$_REQUEST['email'], $_REQUEST['token'] ]);
$result = $statement->fetchAll();
$tot = $statement->rowCount();
if($tot == 0) {
    header('location: '.$url_sistema);
    exit;
}

$_SESSION['temp_reset_email'] = $_REQUEST['email'];
$_SESSION['temp_reset_token'] = $_REQUEST['token'];

?>

<!DOCTYPE html>
<html lang="en">
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
                <br>
                <small>
                    <div id="mensagem-recuperar"></div>
                </small>
                <br>
                <form method="post" id="form-recuperar">
                    <div class="mb-3">
                        <input type="password" name="senha" id="senha" placeholder="Digite uma nova senha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="re_senha" id="re_senha" placeholder="Repetir senha" class="form-control" required>
                    </div>

                    <input type="hidden" name="token" id="token" value="">

                    <input type="hidden" name="email0" id="email" value="<?php echo $_REQUEST['email']  ?>">

                    <button type="submit">Alterar Senha</button>

                </form>

            </div>
            <!-- fecha a div class form -->

        </div>
        <!-- fecha a div class login -->
    </body>
</html>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script type="text/javascript">
    $("#form-recuperar").submit(function(){

        $('#mensagem-recuperar').text("Alterando!")

        event.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: "alterar-senha.php",
            type: 'POST',
            data: formData,

            success: function(mensagem){
                $('#mensagem-recuperar').text('');
                $('#mensagem-recuperar').removeClass()
                if(mensagem.trim() == "Senha alterada com sucesso") {

                    $('#senha').val('');
                    $('#re_senha').val('');
                    alert('Sua senha foi alterada com Sucesso!');
                    window.location="index.php";
                }else{
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