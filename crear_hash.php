<?php
$contrasena = "bibliotecautvt";
$hash = password_hash($contrasena, PASSWORD_DEFAULT);
echo "Hash generado: " . $hash;
?>
