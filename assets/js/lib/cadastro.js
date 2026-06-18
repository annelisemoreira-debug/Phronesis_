// assets/js/lib/cadastro.js

// Configuração global do SweetAlert2 (Toast)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

// Funções de formatação e máscara para o telefone
function telefoneMask(value) {
    if (!value) return "";
    value = value.replace(/\D/g, "");
    if (value.length > 0) value = "(" + value;
    if (value.length > 3) value = value.substring(0, 3) + ") " + value.substring(3);
    if (value.length > 10) value = value.substring(0, 10) + "-" + value.substring(10, 14);
    return value;
}

function handleTelefone(event) {
    let input = event.target;
    input.value = telefoneMask(input.value);
}

document.addEventListener("DOMContentLoaded", function() {
    const formCadastro = document.getElementById('formCadastro');
    const btnSenha = document.getElementById('btn-senha');
    const inputSenha = document.getElementById('senha');
    const inputTelefone = document.getElementById('telefone');

    // === 1. LEITURA DE ALERTAS DE ERRO VINDOS DO PHP ===
    if (formCadastro) {
        const erroSession = formCadastro.dataset.erro;
        if (erroSession && erroSession.trim() !== "") {
            Toast.fire({
                icon: 'error',
                title: erroSession
            });
        }
    }

    // === 2. MÁSCARA EM TEMPO REAL NO INPUT DE TELEFONE ===
    if (inputTelefone) {
        inputTelefone.addEventListener('input', handleTelefone);
    }

    // === 3. ALTERNAR VISIBILIDADE DA SENHA ===
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

    // === 4. VALIDAÇÕES DO FORMULÁRIO (SUBMIT) ===
    if (formCadastro) {
        formCadastro.addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const senha = inputSenha ? inputSenha.value : '';
            const telefone = inputTelefone ? inputTelefone.value : '';
            
            const senhaRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!nome || !email || !senha || !telefone) {
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
                            <b style="display: block; margin-bottom: 8px;">A senha deve ter no mínimo:</b>
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