<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Lógica para autenticar o único usuário admin
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';
    $senha = $data['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Email e senha são obrigatórios."]);
        exit;
    }

    $sql = "SELECT id, senha FROM Usuarios WHERE email = ? AND tipo = 'admin' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            echo json_encode(["message" => "Login bem-sucedido."]);
        } else {
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "Credenciais inválidas."]);
        }
    } else {
        http_response_code(404); // Not Found
        echo json_encode(["message" => "Usuário não encontrado."]);
    }
    $stmt->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método não permitido."]);
}

$conn->close();
?>