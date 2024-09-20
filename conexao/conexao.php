<?php
$host = 'localhost'; // ou outro host se necessário
$user = 'root'; // seu usuário do MySQL
$senha = ''; // sua senha do MySQL
$banco = 'contador'; // seu banco de dados

// Cria a conexão
$conn = new mysqli($host, $user, $senha, $banco);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
