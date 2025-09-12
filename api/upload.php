<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connection.php';

$method = $_SERVER['REQUEST_METHOD'];

// Para lidar com PUT e DELETE via FormData
if ($method == 'PUT' || $method == 'DELETE') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $data = $_PUT;
} else {
    $data = $_REQUEST;
}

switch ($method) {
    case 'GET':
        // Lógica para listar produtos
        $sql = "SELECT p.*, c.nome AS categoria_nome 
                FROM Produtos p
                LEFT JOIN Categorias c ON p.categoria_id = c.id";
        $result = $conn->query($sql);
        $produtos = array();
        while ($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
        echo json_encode($produtos);
        break;

    case 'POST':
        // Lógica para criar um novo produto
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = $_POST['preco'];
        $categoria_id = $_POST['categoria_id'];
        $estoque = $_POST['estoque'];

        $imagem_url = '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $target_dir = "../img/produtos/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["imagem"]["name"]);
            if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
                $imagem_url = 'http://localhost/ab-bombas/img/produtos/' . basename($_FILES["imagem"]["name"]);
            }
        }

        $sql = "INSERT INTO Produtos (nome, descricao, preco, categoria_id, estoque, imagem_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $nome, $descricao, $preco, $categoria_id, $estoque, $imagem_url);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Produto criado com sucesso.", "id" => $conn->insert_id, "imagem_url" => $imagem_url]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao criar produto: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        // Lógica para atualizar um produto
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = $_POST['preco'];
        $categoria_id = $_POST['categoria_id'];
        $estoque = $_POST['estoque'];
        $imagem_url = $_POST['imagem_url'];

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $target_dir = "../img/produtos/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["imagem"]["name"]);
            if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
                $imagem_url = 'http://localhost/ab-bombas/img/produtos/' . basename($_FILES["imagem"]["name"]);
            }
        }

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
}

$conn->close();
