<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "PhronesisLista"; // Nome do banco conforme seu SQL

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["erro" => "Falha na conexão"]));
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        // Rota para buscar estados (Tabela Forte)
        if(isset($_GET['buscar_estados'])){
            $sql = "SELECT id_estado, nome, sigla FROM estados ORDER BY nome ASC";
            $result = $conn->query($sql);
            $estados = [];
            while($row = $result->fetch_assoc()){
                $estados[] = $row;
            }
            echo json_encode($estados);
        } 
        // Rota para buscar usuários com Join para ver o nome do estado
        else {
            $sql = "SELECT u.*, e.nome as nome_estado FROM usuario u 
                    LEFT JOIN estados e ON u.fk_id_estado = e.id_estado";
            $result = $conn->query($sql);
            $dados = [];
            while($row = $result->fetch_assoc()){
                $dados[] = $row;
            }
            echo json_encode($dados);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $nome = $conn->real_escape_string($data['nome']);
        $email = $conn->real_escape_string($data['email']);
        $senha = password_hash($data['senha'], PASSWORD_DEFAULT); // Boa prática: hash de senha
        $telefone = $conn->real_escape_string($data['telefone']);
        $fk_id_estado = intval($data['fk_id_estado']);

        $sql = "INSERT INTO usuario (nome, email, senha, telefone, fk_id_estado)
                VALUES ('$nome', '$email', '$senha', '$telefone', $fk_id_estado)";

        if($conn->query($sql)){
            echo json_encode(["mensagem" => "Usuário cadastrado com sucesso"]);
        } else {
            echo json_encode(["erro" => "Erro ao inserir: " . $conn->error]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = intval($data['id_usuario']);
        $nome = $conn->real_escape_string($data['nome']);
        $email = $conn->real_escape_string($data['email']);
        $telefone = $conn->real_escape_string($data['telefone']);
        $fk_id_estado = intval($data['fk_id_estado']);

        $sql = "UPDATE usuario SET 
                nome='$nome', email='$email', telefone='$telefone', fk_id_estado=$fk_id_estado
                WHERE id_usuario=$id";

        if($conn->query($sql)){
            echo json_encode(["mensagem" => "Usuário atualizado"]);
        } else {
            echo json_encode(["erro" => "Erro ao atualizar"]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = intval($data['id_usuario']);
        $sql = "DELETE FROM usuario WHERE id_usuario=$id";
        if($conn->query($sql)) echo json_encode(["mensagem" => "Excluído"]);
        break;
}
$conn->close();
?>