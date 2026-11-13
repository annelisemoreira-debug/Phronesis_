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