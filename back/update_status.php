<?php
require_once 'conexao.php';

header('Content-Type: application/json');

// Função para registrar erros no log
function logError($message) {
    $logFile = __DIR__ . '/error_log.sql.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Recebe e decodifica os dados JSON
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    
    // Log para depuração (registra os dados recebidos)
    logError("Dados recebidos: " . print_r($data, true));

    // Validação do ID do empréstimo (agora usando 'id' em vez de 'id_emprestimo')
    if (!isset($data['id'])) {
        throw new Exception("ID do empréstimo é obrigatório.");
    }

    // Valida se o ID é numérico
    if (!is_numeric($data['id'])) {
        throw new Exception("ID do empréstimo deve ser um valor numérico.");
    }

    // Prepara e executa a query
    $sql = "
        UPDATE emprestimo
        SET status = 'disponivel',
            data_devolucao = NOW()
        WHERE id_emprestimo = :id
    ";

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        ':id' => $data['id']
    ]);

    // Verifica se a atualização foi bem sucedida
    if (!$success || $stmt->rowCount() === 0) {
        throw new Exception("Nenhum registro foi atualizado. Verifique se o ID existe.");
    }

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado para disponível.',
        'id' => $data['id']
    ]);

} catch (PDOException $e) {
    // Erros específicos do PDO
    logError("Erro PDO: " . $e->getMessage() . "\nSQL: " . ($sql ?? ''));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // Outros erros
    logError("Erro Geral: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}