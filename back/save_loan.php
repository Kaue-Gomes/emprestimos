<?php
header('Content-Type: application/json');
require('conexao.php');

function logError($message) {
    $logFile = __DIR__ . '/error_log.sql.log';
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Verifica se o aluno existe
    $sql = "SELECT id_aluno FROM aluno WHERE nome_aluno = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['studentName']]);
    $aluno = $stmt->fetch();

    if (!$aluno) {
        // Insere novo aluno
        $sql = "INSERT INTO aluno (nome_aluno, curso) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['studentName'], $data['course']]);
        $alunoId = $pdo->lastInsertId();
    } else {
        $alunoId = $aluno['id_aluno'];
    }

    // Obter ID do computador
    $sql = "SELECT id_computador FROM computadores WHERE nome_pc = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['object']]);
    $computador = $stmt->fetch();

    if (!$computador) {
        throw new Exception("Computador não encontrado");
    }

    // Inserir empréstimo
    $sql = "
        INSERT INTO emprestimo 
        (id_alu, id_pc, data, data_devolucao, status) 
        VALUES (?, ?, ?, ?, 'ocupado')
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $alunoId,
        $computador['id_computador'],
        $data['loanDate'],
        $data['returnDate'],
       
    ]);

    echo json_encode([
        'id' => $pdo->lastInsertId(),
        ...$data,
        'status' => 'emprestado'
    ]);
} catch (PDOException $e) {
    // Loga erro PDO com SQL (se disponível)
    $errorMessage = "PDOException: " . $e->getMessage();
    logError($errorMessage);
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    // Loga erro genérico
    logError("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
