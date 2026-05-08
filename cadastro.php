<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faça parte do Phronesis!</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
</head>
<body>
    <img src="assets/img/fundoRoxo.png" class="bg-html" alt="Fundo">

    <main>
        <div class="container">
            <div class="glass-card">
                <div class="logo-section">
                    <img src="assets/img/phronesisLogo.png" alt="Logo oficial da Phronesis">
                </div>
                
                <div class="mainContainer">
                    <h1 class="mainTitulo">Faça seu cadastro!</h1>
                    <p style="color: #031768">Já possui uma conta? <a class="botaoLogin" href="login.php"> Entrar </a></p>

                    <?php if(isset($_SESSION['erro_cad'])): ?>
                        <p style="color: #ff4d4d; font-weight: bold; font-family: Arial, Helvetica, sans-serif"><?php echo $_SESSION['erro_cad']; unset($_SESSION['erro_cad']); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="cad.php">
                        <input type="hidden" name="acao" value="cadastro">

                        <div class="inserirInfo">
                            <label>Nome: </label> 
                            <input type="text" name="nome" placeholder="Digite seu nome completo" required>
                        </div>

                        <div class="inserirInfo">
                            <label>E-mail: </label> 
                            <input type="email" name="email" placeholder="exemplo@email.com" required>
                        </div>
                    
                        <div class="linha_dupla">
                            <div class="inserirInfo">
                                <label>Senha: </label> 
                                <input type="password" name="senha" 
                                    placeholder="Mínimo 6 caracteres (letra, número e símbolo)" 
                                    required
                                    pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}"
                                    title="A senha deve ter no mínimo 6 caracteres, contendo pelo menos uma letra, um número e um caractere especial (@, !, #, etc.)">
                            </div>
                     

                            <div class="inserirInfo">
                                <label>Telefone: </label> 
                                <input type="text" 
                                    name="telefone" 
                                    id="telefone" 
                                    placeholder="(00) 00000-0000" 
                                    maxlength="15" 
                                    oninput="handleTelefone(event)"
                                    required>
                            </div>
                        </div>

                        <button class="entrarLogin" type="submit"> Cadastrar </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

                            <script>
                                function handleTelefone(event) {
                                    let input = event.target;
                                    input.value = telefoneMask(input.value);
                                }

                                function telefoneMask(value) {
                                    if (!value) return "";
                                    
                                    // Remove tudo que não for número
                                    value = value.replace(/\D/g, "");

                                    // (00) 00000-0000
                                    if (value.length > 0) {
                                        // Adiciona o parêntese inicial e os dois primeiros dígitos
                                        value = "(" + value;
                                    }
                                    if (value.length > 3) {
                                        // Fecha o parêntese após o DDD
                                        value = value.substring(0, 3) + ") " + value.substring(3);
                                    }
                                    if (value.length > 10) {
                                        // Adiciona o hífen após os 5 dígitos do celular (formato 9 dígitos)
                                        value = value.substring(0, 10) + "-" + value.substring(10, 14);
                                    }
                                    
                                    return value;
                                }
                          </script>
</body>
</html>