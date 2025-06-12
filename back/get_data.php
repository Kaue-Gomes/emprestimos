<?php
header('Content-Type: application/json');
require('conexao.php');

try {
    $emprestimos = $pdo->query("
        SELECT 
            e.id_emprestimo as id,
            a.nome_aluno as studentName,
            a.curso as course,
            c.nome_pc as object,
            DATE(e.data) as loanDate,
            DATE(e.data_devolucao) as returnDate,
            CASE 
                WHEN e.status = 'ocupado' THEN 'emprestado'
                ELSE 'devolvido'
            END as status
        FROM emprestimo e
        JOIN aluno a ON e.id_alu = a.id_aluno
        JOIN computadores c ON e.id_pc = c.id_computador
        ORDER BY e.data DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($emprestimos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
