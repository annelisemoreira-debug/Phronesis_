// assets/js/lib/index.js

// Configuração global do SweetAlert2 (Toast)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

document.addEventListener("DOMContentLoaded", function() {
    const formLogin = document.getElementById('formLogin');
    const btnSenha = document.getElementById('btn-senha');
    const inputSenha = document.getElementById('senha');

    // === 1. LEITURA DE ALERTAS DO PHP (ERRO E SUCESSO) ===
    if (formLogin) {
        const erroSession = formLogin.dataset.erro;
        const sucessoSession = formLogin.dataset.sucesso;

        if (erroSession && erroSession.trim() !== "") {
            Toast.fire({
                icon: 'error',
                title: erroSession
            });
        }

        if (sucessoSession && sucessoSession.trim() !== "") {
            Toast.fire({
                icon: 'success',
                title: sucessoSession
            });
        }
    }

    // === 2. MUDAR VISIBILIDADE DA SENHA (OLHO) ===
    if (btnSenha && inputSenha) {
        btnSenha.addEventListener('click', () => {
            if (inputSenha.type === 'password') {
                inputSenha.setAttribute('type', 'text');
                btnSenha.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inputSenha.setAttribute('type', 'password');
                btnSenha.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    }

    // === 3. VALIDAÇÕES DE SUBMIT DO FORMULÁRIO ===
    if (formLogin) {
        formLogin.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const senha = inputSenha ? inputSenha.value : '';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!email || !senha) {
                e.preventDefault(); // Impede o envio do form
                Toast.fire({
                    icon: 'warning',
                    title: "Por favor, preencha todos os campos."
                });
            } else if (!emailRegex.test(email)) {
                e.preventDefault(); // Impede o envio do form
                Toast.fire({
                    icon: 'warning',
                    title: "Formato de e-mail inválido."
                });
            }
        });
    }
});

// ==========================================
// FUNÇÃO DE PREENCHIMENTO CONFORME O BANCO DE DADOS
// ==========================================
function preencherCampos(perfil) {
    // Perfis baseados estritamente nos administradores definidos no seu SQL
    const perfis = {
        1: { nome: "João Phronesis", email: "joao@phronesis.com", senha: "Senha@123", tel: "11999999999" },
        2: { nome: "Manuella Phronesis", email: "manuella@phronesis.com", senha: "Senha@123", tel: "11988888888" },
        3: { nome: "Annelise Phronesis", email: "annelise@phronesis.com", senha: "Senha@123", tel: "11977777777" },
        4: { nome: "Dukye Phronesis", email: "dukye@phronesis.com", senha: "Senha@123", tel: "11966666666" },
        5: { nome: "Narutomig Phronesis", email: "narutomig@phronesis.com", senha: "Senha@123", tel: "11955555555" },
        6: { nome: "Raoni Phronesis", email: "raoni@phronesis.com", senha: "Senha@123", tel: "11944444444" }
    };

    const dados = perfis[perfil];

    // Seleção dos elementos HTML por ID
    const inputNome = document.getElementById('nome');
    const inputEmail = document.getElementById('email');
    const inputSenha = document.getElementById('senha');
    const inputTelefone = document.getElementById('telefone');

    // Preenche apenas os campos existentes na página atual (Login ou Cadastro)
    if (inputNome) inputNome.value = dados.nome;
    if (inputEmail) inputEmail.value = dados.email;
    if (inputSenha) inputSenha.value = dados.senha;
    
    if (inputTelefone) {
        inputTelefone.value = dados.tel;
        // Dispara o evento para sua função 'handleTelefone' formatar com a máscara (xx) xxxxx-xxxx
        inputTelefone.dispatchEvent(new Event('input'));
    }
}