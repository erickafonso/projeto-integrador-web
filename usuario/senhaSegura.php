<?php

// Antigamente funcionava
$senha ='123';
$senhaMD5_1 = md5($senha);
$senhaMD5_2 = md5($senha);
$senhaSHA = sha1($senha);

//Hoje em dia

$senhaSegura1 = password_hash($senha,PASSWORD_DEFAULT,['cost'=>10]);
$senhaSegura2 = password_hash($senha,PASSWORD_DEFAULT,['cost'=>10]);
$senhaSegura3 = password_hash($senha,PASSWORD_DEFAULT,['cost'=>10]);


// Verificando se é a mesma senha
$senhaCorreta = password_verify('123',$senhaSegura1);

var_dump(($senha));
echo '<br>';
var_dump(($senhaMD5_1));
echo '<br>';
var_dump(($senhaMD5_2));
echo '<br>';
var_dump(($senhaSHA));
echo '<br>';
echo '<br>';
var_dump($senhaSegura1);
echo '<br>';
var_dump($senhaSegura2);
echo '<br>';
var_dump($senhaSegura3);
echo '<br>';
var_dump($senhaCorreta);




?>