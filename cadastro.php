<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Faça parte do Phronesis!</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <img src="assets/img/fundoRoxo.webp" class="bg-html" alt="Fundo">

    <main>
        <div class="container">
            <div class="glass-card">
                <div class="logo-section">
                    <img src="assets/img/phronesisLogo.webp" alt="Logo oficial da Phronesis">
                </div>

                <div class="mainContainer">
                    <h1 class="mainTitulo">Faça seu cadastro!</h1>
                    <p style="color: #031768; margin-bottom: 2px;">Já possui uma conta? <a class="botaoLogin" href="login.php"> Entrar </a></p>

                    <form id="formCadastro" method="POST" action="cad.php" novalidate style="display: flex; flex-direction: column; gap: 5px;">
                        <input type="hidden" name="acao" value="cadastro">

                        <div class="inserirInfo">
                            <label>Nome: </label>
                            <input type="text" name="nome" id="nome" placeholder="Digite seu nome completo" required style="padding: 8px 15px;">
                        </div>

                        <div class="inserirInfo">
                            <label>E-mail: </label>
                            <input type="email" name="email" id="email" placeholder="exemplo@email.com" required style="padding: 8px 15px;">
                        </div>

                        <div class="linha_dupla" style="gap: 10px;">
                            <div class="inserirInfo">
                                <label>Senha: </label>
                                <div class="input-group-password">
                                    <input type="password" name="senha" id="senha" placeholder="Mínimo 6 caracteres" required style="padding: 8px 15px;">
                                    <i class="fa-solid fa-eye toggle-password" id="btn-senha"></i>
                                </div>
                            </div>

                            <div class="inserirInfo">
                                <label>Telefone: </label>
                                <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" maxlength="15" oninput="handleTelefone(event)" required style="padding: 8px 15px;">
                            </div>
                        </div>

                        <button class="entrarLogin" type="submit" style="margin-top: 17px; padding: 10px 60px;"> Cadastrar </button>
                    </form>

                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin: 2px 0;">
                        <div style="flex: 1; height: 1px; background-color: rgba(0, 26, 139, 0.2);"></div>
                        <span style="color: #001a8b; font-size: 11px; font-weight: bold;">OU</span>
                        <div style="flex: 1; height: 1px; background-color: rgba(0, 26, 139, 0.2);"></div>
                    </div>

                    <div id="g_id_onload"
                        data-client_id="904732005700-hhrsns4i5bl2ll22u57bsr9p0ijgkm4e.apps.googleusercontent.com"
                        data-login_uri="http://localhost/Phronesis_/cad.php"
                        data-auto_prompt="false">
                    </div>

                    <div class="google-btn-container" style="display: flex; justify-content: center;">
                        <div class="g_id_signin"
                             data-type="standard"
                             data-size="medium"
                             data-theme="outline"
                             data-text="signup_with"
                             data-shape="filled_blue"
                             data-logo_alignment="left"
                             data-width="280">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/lib/cadastro.js" defer></script>

    <?php include 'perfil/vlibras.php'; ?>
</body>
</html>