<?php
// Inicia a sessão do PHP (isso precisa ser a PRIMEIRA linha)
session_start();

require_once 'conexao.php';
header('Content-Type: application/json');

$dados = json_decode(file_get_contents("php://input"));

if (!empty($dados->login) && !empty($dados->senha)) {
    try {
        // Busca o usuário no banco
        $sql = "SELECT id_usuario, nome, nivel_acesso FROM usuario WHERE login = :login AND senha = :senha";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':login', $dados->login);
        $stmt->bindParam(':senha', $dados->senha); // Em um projeto real, usaríamos password_hash() aqui!
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Se encontrou o usuário, guarda os dados dele na Sessão do navegador
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['nivel'] = $usuario['nivel_acesso'];

            echo json_encode(['sucesso' => true, 'mensagem' => 'Login aprovado!', 'nivel' => $usuario['nivel_acesso']]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário ou senha incorretos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
}
?>