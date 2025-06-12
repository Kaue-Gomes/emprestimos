<?php
header('Content-Type: application/json');
require('conexao.php');

// Obter lista de computadores
$computadores = $pdo->query("SELECT id_computador, nome_pc FROM computadores ORDER BY nome_pc")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($computadores);