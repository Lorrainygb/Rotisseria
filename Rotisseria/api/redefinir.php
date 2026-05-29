<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require '../config/banco.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'];
    $novaSenha = $input['novaSenha'];

    $stmt = $pdo->prepare("
        SELECT * FROM funcionario WHERE token_reset = ? AND expira_reset > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['erro' => 'Link inválido ou expirado']);
        exit;
    }

    $senha_hash = password_hash($novaSenha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE funcionario SET senha = ?, token_reset = NULL, expira_reset = NULL WHERE codigo_funcionario = ?
    ");
    
    if ($stmt->execute([$senha_hash, $user['codigo_funcionario']])) {
        echo json_encode(['mensagem' => 'Senha redefinida!']);
    }
}
?>