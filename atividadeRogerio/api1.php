<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
//configuração conexão
$host = "localhost";
$user = "root";
$pass = "Home@spSENAI2025!";
$db   = "projetofinal";
//conexão
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["erro" => "Falha na conexão"]));
}
//obtém método POST (CAD), GET (CONS), PUT (ALT) E DELETE (REMOVER)
$method = $_SERVER['REQUEST_METHOD'];
// executa ação de acordo com o método indicado pelo usuário no formulário
switch($method){
    // 🔹 LISTAR TODOS ou UM
    case 'GET':
        if(isset($_GET['id'])){
            $id = intval($_GET['id']);// id de consulta
            $sql = "SELECT * FROM cadastro WHERE idcadastro = $id";// consulta
        } else {
            $sql = "SELECT * FROM cadastro";//mostra todos
        }
        $result = $conn->query($sql);// executa consulta
        $dados = [];// obtém dados
        // percorre resposta
        while($row = $result->fetch_assoc()){
            $dados[] = $row;
        }
        // envia resposta (codifica para Json)
        echo json_encode($dados);
        break;


    // 🔹 INSERIR
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        $nome = $conn->real_escape_string($data['nome']);
        $email = $conn->real_escape_string($data['email']);
        $senha = $conn->real_escape_string($data['senha']);
        $telefone = $conn->real_escape_string($data['telefone']);

        $sql = "INSERT INTO cadastro (nome, email, senha, telefone)
                VALUES ('$nome', '$email', '$senha', '$telefone')";

        if($conn->query($sql)){
            echo json_encode(["mensagem" => "Funcionário inserido com sucesso"]);
        } else {
            echo json_encode(["erro" => "Erro ao inserir"]);
        }
        break;


    // 🔹 ATUALIZAR
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        $id = intval($data['idcadastro']);
        $nome = $conn->real_escape_string($data['nome']);
        $email = $conn->real_escape_string($data['email']);
        $senha = $conn->real_escape_string($data['senha']);
        $telefone = $conn->real_escape_string($data['telefone']);

        $sql = "UPDATE cadastro SET 
                nome='$nome',
                email='$email',
                senha='$senha',
                telefone='$telefone'
                WHERE idcadastro=$id";

        if($conn->query($sql)){
            echo json_encode(["mensagem" => "Funcionário atualizado"]);
        } else {
            echo json_encode(["erro" => "Erro ao atualizar"]);
        }
        break;


    // 🔹 EXCLUIR
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = intval($data['idcadastro']);

        $sql = "DELETE FROM cadastro WHERE idcadastro=$id";

        if($conn->query($sql)){
            echo json_encode(["mensagem" => "Funcionário excluído"]);
        } else {
            echo json_encode(["erro" => "Erro ao excluir"]);
        }
        break;
}

$conn->close();
?>