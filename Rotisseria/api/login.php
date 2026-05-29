<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require '../config/banco.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email']);
    $senha = $input['senha'];

    $stmt = $pdo->prepare("SELECT * FROM funcionario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        $token = base64_encode($user['codigo_funcionario'] . ':' . time());
        
        echo json_encode([
            'mensagem' => 'Login OK!',
            'token' => $token,
            'funcionario' => [
                'codigo' => $user['codigo_funcionario'],
                'nome' => $user['nome'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode(['erro' => 'Email ou senha incorretos']);
    }
}
?>