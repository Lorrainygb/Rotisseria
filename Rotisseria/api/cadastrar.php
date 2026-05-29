<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require '../config/banco.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nome = trim($input['nome']);
    $email = trim($input['email']);
    $senha = $input['senha'];

    if (empty($nome) || empty($email) || empty($senha)) {
        echo json_encode(['erro' => 'Preencha todos os campos']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT codigo_funcionario FROM funcionario WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['erro' => 'Email já cadastrado']);
        exit;
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO funcionario (nome, email, senha) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$nome, $email, $senha_hash])) {
        echo json_encode([
            'mensagem' => 'Cadastro realizado!',
            'codigo_funcionario' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['erro' => 'Erro no cadastro']);
    }
}
?>