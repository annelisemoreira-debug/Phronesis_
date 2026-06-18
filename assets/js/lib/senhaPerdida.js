// assets/js/lib/recuperar.js

// Configuração padrão do Toast (SweetAlert2)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

document.addEventListener("DOMContentLoaded", function() {
    const formRecuperar = document.getElementById('formRecuperar');

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

    // === 2. VALIDAÇÃO MANUAL DO FORMULÁRIO ===
    if (formRecuperar) {
        formRecuperar.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Regra de senha forte (Mínimo 6 caracteres, letra, número e símbolo)
            const senhaRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/;

            if (!email || !senha) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Por favor, preencha todos os campos."
                });
            } else if (!emailRegex.test(email)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: "Formato de e-mail inválido."
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