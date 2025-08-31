<?php
session_start();      // Continua a sessão para poder destruí-la
session_destroy();    // Apaga todos os dados da sessão
header("Location: ../View/Entrada.php"); // Redireciona corretamente
exit;
?>
