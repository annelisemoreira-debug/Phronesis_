// assets/js/lib/senhaPerdida.js

// Configuração padrão do Toast (SweetAlert2)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

const LIMITES = { email: 254, senhaMax: 64 };

document.addEventListener("DOMContentLoaded", function() {
    const formRecuperar = document.getElementById('formRecuperar');
    const btnSenha = document.getElementById('btn-senha');
    const inputSenha = document.getElementById('senha');

    // === 1. CAPTURA ERROS VINDOS DA SESSION PHP ===
    if (formRecuperar) {
        const erroSession = formRecuperar.dataset.erro;
        if (erroSession && erroSession.trim() !== "") {
            Toast.fire({
                icon: 'error',
                title: erroSession
            });
        }
    }

    // === 2. MOSTRAR / OCULTAR SENHA (OLHO) ===
    if (btnSenha && inputSenha) {
        btnSenha.addEventListener('click', () => {
            const oculto = inputSenha.type === 'password';
            inputSenha.type = oculto ? 'text' : 'password';
            btnSenha.classList.replace(oculto ? 'fa-eye' : 'fa-eye-slash',
                                       oculto ? 'fa-eye-slash' : 'fa-eye');
        });
    }

    // === 3. VALIDAÇÃO MANUAL DO FORMULÁRIO ===
    if (formRecuperar) {
        formRecuperar.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const senha = inputSenha ? inputSenha.value : '';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

            // Regra de senha forte (6 a 64 caracteres, letra, número e símbolo)
            const senhaRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,64}$/;

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
            } else if (!senhaRegex.test(senha)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    html: `
                        <div style="text-align: left; font-family: Arial, sans-serif;">
                            <b style="display: block; margin-bottom: 8px;">A nova senha deve ter no mínimo:</b>
                            <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
                                <li>6 dígitos;</li>
                                <li>uma letra;</li>
                                <li>um número;</li>
                                <li>um símbolo.</li>
                            </ul>
                        </div>
                    `
                });
            }
        });
    }
});