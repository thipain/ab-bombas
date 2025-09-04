<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connection.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Lógica para listar produtos
        $sql = "SELECT p.*, c.nome AS categoria_nome 
                FROM Produtos p
                LEFT JOIN Categorias c ON p.categoria_id = c.id";
        $result = $conn->query($sql);
        $produtos = array();
        while($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
        echo json_encode($produtos);
        break;

    case 'POST':
        // Lógica para criar um novo produto
        $data = json_decode(file_get_contents("php://input"), true);
        $nome = $data['nome'];
        $descricao = $data['descricao'];
        $preco = $data['preco'];
        $categoria_id = $data['categoria_id'];
        $estoque = $data['estoque'];
        $imagem_url = $data['imagem_url'];

        $sql = "INSERT INTO Produtos (nome, descricao, preco, categoria_id, estoque, imagem_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $nome, $descricao, $preco, $categoria_id, $estoque, $imagem_url);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Produto criado com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao criar produto: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        // Lógica para atualizar um produto existente
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $nome = $data['nome'];
        $descricao = $data['descricao'];
        $preco = $data['preco'];
        $categoria_id = $data['categoria_id'];
        $estoque = $data['estoque'];
        $imagem_url = $data['imagem_url'];

        $sql = "UPDATE Produtos SET nome = ?, descricao = ?, preco = ?, categoria_id = ?, estoque = ?, imagem_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisis", $nome, $descricao, $preco, $categoria_id, $estoque, $imagem_url, $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Produto atualizado com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao atualizar produto: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // Lógica para deletar um produto
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];

        $sql = "DELETE FROM Produtos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Produto deletado com sucesso."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao deletar produto: " . $stmt->error]);
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