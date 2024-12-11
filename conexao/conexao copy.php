<?php
//$host = 'localhost'; // ou outro host se necessário
//$user = 'root'; // seu usuário do MySQL
//$senha = ''; // sua senha do MySQL
//$banco = 'contador'; // seu banco de dados

// Cria a conexão
//$conn = new mysqli($host, $user, $senha, $banco);

// Verifica a conexão
//if ($conn->connect_error) {
//    die("Falha na conexão: " . $conn->connect_error);
//}


$host = 'sql304.infinityfree.com'; // ou outro host se necessário
$user = 'if0_37852537'; // seu usuário do MySQL
$senha = 'DJ0FFf61uIYZUXw'; // sua senha do MySQL
$banco = 'if0_37852537_contador'; // seu banco de dados

// Cria a conexão
$conn = new mysqli($host, $user, $senha, $banco);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>