<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require '../config/banco.php';
require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email']);

    $stmt = $pdo->prepare("SELECT * FROM funcionario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['mensagem' => 'Se o email existir, enviamos o link']);
        exit;
    }

    $token = md5($user['codigo_funcionario'] . time() . rand(1000, 9999));
    $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmt = $pdo->prepare("UPDATE funcionario SET token_reset = ?, expira_reset = ? WHERE codigo_funcionario = ?");
    $stmt->execute([$token, $expira, $user['codigo_funcionario']]);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elisistema2026@gmail.com';
        $mail->Password   = 'iacx yprs ybmk mqrx';  // ← 16 dígitos do Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('elisistema2026@gmail.com', 'Dona Elisa');
        $mail->addAddress($email, $user['nome']);

        $resetUrl = "http://localhost/Rotisseria/pages/definicao_nova_senha.html?token=$token";
        
        $mail->isHTML(true);
        $mail->Subject = 'Recuperar Senha';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto;'>
                <h2>🔒 Recuperação de Senha</h2>
                <p>Olá <strong>{$user['nome']}</strong>,</p>
                <p>Clique abaixo para redefinir sua senha:</p>
                <br>
                <a href='$resetUrl' 
                   style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                   REDEFINIR SENHA
                </a>
                <br><br>
                <p style='color: #666; font-size: 14px;'>
                    Este link expira em <strong>15 minutos</strong>.
                </p>
            </div>
        ";

        $mail->send();
        echo json_encode(['mensagem' => '✅ Link enviado! Verifique inbox/spam.']);
        
    } catch (Exception $e) {
        echo json_encode(['erro' => '❌ Erro: ' . $mail->ErrorInfo]);
    }
}
?>