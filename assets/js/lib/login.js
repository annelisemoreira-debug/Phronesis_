// assets/js/lib/login.js

// Configuração global do SweetAlert2 (Toast)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

const LIMITES = { email: 100, senhaMax: 64 };

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
            const oculto = inputSenha.type === 'password';
            inputSenha.type = oculto ? 'text' : 'password';
            btnSenha.classList.replace(oculto ? 'fa-eye' : 'fa-eye-slash',
                                       oculto ? 'fa-eye-slash' : 'fa-eye');
        });
    }

    // === 3. VALIDAÇÕES DE SUBMIT DO FORMULÁRIO ===
    if (formLogin) {
        formLogin.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const senha = inputSenha ? inputSenha.value : '';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

            if (!email || !senha) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Por favor, preencha todos os campos."
                });
            } else if (email.length > LIMITES.email) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: `O e-mail deve ter no máximo ${LIMITES.email} caracteres.`
                });
            } else if (!emailRegex.test(email)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Formato de e-mail inválido."
                });
            } else if (senha.length > LIMITES.senhaMax) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: `A senha deve ter no máximo ${LIMITES.senhaMax} caracteres.`
                });
            }
        });
    }
});