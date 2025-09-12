<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connection.php';

$method = $_SERVER['REQUEST_METHOD'];

/**
 * Função para redimensionar imagens
 */
function redimensionarImagem($arquivoTmp, $destino, $larguraMax = 300, $alturaMax = 300)
{
    list($larguraOrig, $alturaOrig, $tipo) = getimagesize($arquivoTmp);
    $ratio = min($larguraMax / $larguraOrig, $alturaMax / $alturaOrig);

    $novaLargura = intval($larguraOrig * $ratio);
    $novaAltura = intval($alturaOrig * $ratio);

    // Cria recurso de imagem conforme o tipo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagemOriginal = imagecreatefromjpeg($arquivoTmp);
            break;
        case IMAGETYPE_PNG:
            $imagemOriginal = imagecreatefrompng($arquivoTmp);
            break;
        case IMAGETYPE_GIF:
            $imagemOriginal = imagecreatefromgif($arquivoTmp);
            break;
        default:
            return false; // tipo não suportado
    }

    $novaImagem = imagecreatetruecolor($novaLargura, $novaAltura);

    // Preserva transparência para PNG e GIF
    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
        imagecolortransparent($novaImagem, imagecolorallocatealpha($novaImagem, 0, 0, 0, 127));
        imagealphablending($novaImagem, false);
        imagesavealpha($novaImagem, true);
    }

    // Redimensiona
    imagecopyresampled(
        $novaImagem,
        $imagemOriginal,
        0,
        0,
        0,
        0,
        $novaLargura,
        $novaAltura,
        $larguraOrig,
        $alturaOrig
    );

    // Salva conforme o tipo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($novaImagem, $destino, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($novaImagem, $destino, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($novaImagem, $destino);
            break;
    }

    imagedestroy($imagemOriginal);
    imagedestroy($novaImagem);

    return true;
}

/**
 * Função para salvar a imagem com redimensionamento
 */
function salvarImagem($file)
{
    $target_dir = __DIR__ . "/../img/produtos/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $fileName = uniqid() . "_" . basename($file["name"]);
    $target_file = $target_dir . $fileName;

    // Redimensiona e salva a imagem
    if (redimensionarImagem($file["tmp_name"], $target_file, 300, 300)) {
        return "http://localhost/ab-bombas/img/produtos/" . $fileName;
    }
    return null;
}

switch ($method) {
    case 'GET':
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
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $preco = $_POST['preco'] ?? 0;
        $categoria_id = $_POST['categoria_id'] ?? null;
        $estoque = $_POST['estoque'] ?? 0;
        $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

        $imagem_url = $_POST['current_image_url'] ?? '';

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $novaImagem = salvarImagem($_FILES['imagem']);
            if ($novaImagem) {
                $imagem_url = $novaImagem;
            }
        }

        if ($id) {
            $sql = "UPDATE Produtos 
                    SET nome=?, descricao=?, preco=?, categoria_id=?, estoque=?, imagem_url=? 
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdissi", $nome, $descricao, $preco, $categoria_id, $estoque, $imagem_url, $id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Produto atualizado com sucesso."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erro ao atualizar produto: " . $stmt->error]);
            }
        } else {
            $sql = "INSERT INTO Produtos (nome, descricao, preco, categoria_id, estoque, imagem_url) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiss", $nome, $descricao, $preco, $categoria_id, $estoque, $imagem_url);

            if ($stmt->execute()) {
                echo json_encode([
                    "message" => "Produto criado com sucesso.",
                    "id" => $conn->insert_id,
                    "imagem_url" => $imagem_url
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erro ao criar produto: " . $stmt->error]);
            }
        }
        $stmt->close();
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID não informado."]);
            exit;
        }

        $sql = "DELETE FROM Produtos WHERE id=?";
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
