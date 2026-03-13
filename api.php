<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "projetofinal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["erro" => "Falha na conexão: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])){
            $id = intval($_GET['id']);
            // Ajustado para id_cadastro
            $sql = "SELECT * FROM cadastro WHERE id_cadastro = $id";
        } else {
            $sql = "SELECT * FROM cadastro";
        }
        
        $result = $conn->query($sql);
        $dados = [];

        if ($result) {
            while($row = $result->fetch_assoc()){
                $dados[] = $row;
            }
        }
        echo json_encode($dados);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if(!empty($data)){
            $nome = $conn->real_escape_string($data['nome']);
            $email = $conn->real_escape_string($data['email']);
            $senha = $conn->real_escape_string($data['senha']);
            $telefone = $conn->real_escape_string($data['telefone']);
            $observacao = $conn->real_escape_string($data['observacao']);
        
        

            $sql = "INSERT INTO cadastro (nome, email, senha, telefone, observacao) VALUES ('$nome', '$email', '$senha', '$telefone', '$observacao')";

            if($conn->query($sql)){
                echo json_encode(["mensagem" => "Inserido com sucesso"]);
            } else {
                echo json_encode(["erro" => "Erro ao inserir: " . $conn->error]);
            }
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        // Padronizado para id_cadastro
        $id = intval($data['id_cadastro']); 
        $nome = $conn->real_escape_string($data['nome']);
        $email = $conn->real_escape_string($data['email']);
        $senha = $conn->real_escape_string($data['senha']);
        $telefone = $conn->real_escape_string($data['telefone']);
        $observacao = $conn->real_escape_string($data['observacao']);


        $sql = "UPDATE cadastro SET nome='$nome', email='$email', senha='$senha', telefone='$telefone', observacao='$observacao' WHERE id_cadastro=$id";

        if($conn->query($sql)){
            echo json_encode(value: ["mensagem" => "Atualizado com sucesso"]);
        } else {
            echo json_encode(["erro" => "Erro ao atualizar: " . $conn->error]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        // Padronizado para id_cadastro
        $id = intval($data['id_cadastro']);

        $sql = "DELETE FROM cadastro WHERE id_cadastro=$id";

        if($conn->query($sql)){
            echo json_encode(["mensagem" => "Excluído com sucesso"]);
        } else {
            echo json_encode(["erro" => "Erro ao excluir: " . $conn->error]);
        }
        break;
} // Faltava este fechamento do switch

$conn->close();
?>