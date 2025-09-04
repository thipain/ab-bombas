<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connection.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Lógica para listar categorias
        $sql = "SELECT * FROM Categorias";
        $result = $conn->query($sql);
        $categorias = array();
        while($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        echo json_encode($categorias);
        break;

    case 'POST':
        // Lógica para criar uma nova categoria
        $data = json_decode(file_get_contents("php://input"), true);
        $nome = $data['nome'];
        $descricao = $data['descricao'];
        $cor = $data['cor'];

        $sql = "INSERT INTO Categorias (nome, descricao, cor) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $descricao, $cor);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Categoria criada com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao criar categoria: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        // Lógica para atualizar uma categoria existente
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $nome = $data['nome'];
        $descricao = $data['descricao'];
        $cor = $data['cor'];

        $sql = "UPDATE Categorias SET nome = ?, descricao = ?, cor = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nome, $descricao, $cor, $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Categoria atualizada com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao atualizar categoria: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // Lógica para deletar uma categoria
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];

        $sql = "DELETE FROM Categorias WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Categoria deletada com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao deletar categoria: " . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método não permitido."]);
        break;
}

$conn->close();
?>