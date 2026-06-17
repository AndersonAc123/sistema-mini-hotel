<?php
// 1. Trava o PHP no horário oficial do Brasil
date_default_timezone_set('America/Sao_Paulo');

$host = 'localhost';
$dbname = 'mini_hotel';
$usuario = 'root';
$senha = ''; // Se você usa senha no XAMPP/MySQL, coloque aqui

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Trava o Banco de Dados (MySQL) no fuso horário do Brasil (UTC -3)
    $pdo->exec("SET time_zone = '-03:00'");
    
} catch (PDOException $e) {
    die("Erro de Conexão: " . $e->getMessage());
}
?>