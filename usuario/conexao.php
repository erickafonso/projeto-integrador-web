<?php
    
    //definir fuso horário
    date_default_timezone_set('America/Sao_Paulo');

    $url_sistema = "http://$_SERVER[HTTP_HOST]/";
    $url = explode("//", $url_sistema);
    if($url[1] == 'localhost/'){
        $url_sistema = "http://$_SERVER[HTTP_HOST]/Projeto/";
    }



    //Dados conexão bd local
    $servidor = 'localhost';
    $banco = 'contador';
    $usuario = 'root';
    $senha = '';

    try{
        $pdo = new PDO("mysql:dbname=$banco;host=$servidor; charset=utf8", "$usuario", "$senha");
    }catch (Exception $e){
        echo 'Erro ao conectar ao banco de dados! <br>';
        echo $e;
    }


    //Variáveis Globais
    $nome_sistema = 'Nome Sitema';
    $email_sistema = 'cralves@gmail.com';
    $telefone_sistema = '(51) 99149-9128';

   /* $query = $pdo->query("SELECT * FROM config");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $linhas = @count($res);
    if($linhas == 0){
        $pdo->query("INSERT INTO config SET nome = '$nome_sistema', email= '$email_sistema', telefone = '$telefone_sistema', endereco= '', instagram = '', logo = 'logo.png', icone = 'icone.png', logo_rel = 'logo.jpg',  ativo='Sim' ");
    }else{
        $nome_sistema = $res[0]['nome'];
        $email_sistema = $res[0]['email'];
        $telefone_sistema = $res[0]['telefone'];
        $endereco_sistema = $res[0]['endereco'];
        $instagram_sistema = $res[0]['instagram'];
        $logo_sistema = $res[0]['logo'];
        $icone_sistema = $res[0]['icone'];
        $logo_rel = $res[0]['logo_rel'];
        // $id = $res[0]['id'];
        $ativo_sistema = $res[0]['ativo'];

        if($ativo_sistema != 'Sim' and $ativo_sistema != ''){?>
            <style type="text/css">
                @media only screen and (max-width: 700px) {
                    .imgsistema_mobile{
                        width: 300px;
                    }
                }
            </style>
            <div style="text-align: center; margin-top: 100px">
                <img src="img/bloqueio.png" class="imgsistema_mobile">
            </div>
        <?php
        exit();
        }

    }*/
    ?>