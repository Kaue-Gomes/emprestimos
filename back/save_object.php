<?php
require('conexao.php');

try {
    // Verifica se recebeu o nome do objeto via POST
    if (isset($_POST['nome_pc']) && !empty(trim($_POST['nome_pc']))) {
        $nome_pc = trim($_POST['nome_pc']);

        // Inserção no banco
        $stmt = $pdo->prepare("INSERT INTO computadores (nome_pc) VALUES (:nome_pc)");
        $stmt->bindParam(':nome_pc', $nome_pc);
        $stmt->execute();

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nome do objeto vazio']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
