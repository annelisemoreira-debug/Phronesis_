// assets/js/lib/cadastro.js

// Configuração global do SweetAlert2 (Toast)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

const LIMITES = { nome: 100, email: 254, senhaMin: 6, senhaMax: 64 };

// === MÁSCARA DO TELEFONE ===
function telefoneMask(value) {
    if (!value) return "";
    value = value.replace(/\D/g, "").substring(0, 11);
    if (value.length > 0) value = "(" + value;
    if (value.length > 3) value = value.substring(0, 3) + ") " + value.substring(3);
    if (value.length > 10) value = value.substring(0, 10) + "-" + value.substring(10);
    return value;
}

function handleTelefone(event) {
    event.target.value = telefoneMask(event.target.value);
}

// === ALERTAS DA SENHA ===

// Toast original (validação no navegador)
function toastRequisitosSenha() {
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

// Janela grande original (erro vindo do PHP/servidor)
function modalSenhaServidor() {
    Swal.fire({
        icon: 'error',
        title: 'Ops! Algo deu errado com a senha.',
        html: `
            <div style="text-align: left;">
                <p>A senha digitada não cumpre os requisitos mínimos:</p>
                <ul>
                    <li>6 dígitos;</li>
                    <li>uma letra;</li>
                    <li>um número;</li>
                    <li>um símbolo.</li>
                </ul>
            </div>
        `,
        confirmButtonColor: '#001a8b'
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const formCadastro = document.getElementById('formCadastro');
    const btnSenha = document.getElementById('btn-senha');
    const inputSenha = document.getElementById('senha');
    const inputTelefone = document.getElementById('telefone');

    // === 1. ALERTAS VINDOS DO PHP ===
    if (formCadastro) {
        const status = formCadastro.dataset.status;

        if (status === "email_duplicado") {
            Toast.fire({ icon: 'error', title: 'Este e-mail já está cadastrado!' });
        } else if (status === "senha_incorreta" || status === "erro_senha") {
            modalSenhaServidor();
        } else if (status === "erro_geral") {
            Toast.fire({ icon: 'error', title: 'Não foi possível concluir a ação. Tente novamente.' });
        }
    }

    // === 2. MÁSCARA EM TEMPO REAL NO TELEFONE ===
    if (inputTelefone) {
        inputTelefone.addEventListener('input', handleTelefone);
    }

    // === 3. ALTERNAR VISIBILIDADE DA SENHA ===
    if (btnSenha && inputSenha) {
        btnSenha.addEventListener('click', () => {
            const oculto = inputSenha.type === 'password';
            inputSenha.type = oculto ? 'text' : 'password';
            btnSenha.classList.replace(oculto ? 'fa-eye' : 'fa-eye-slash',
                                       oculto ? 'fa-eye-slash' : 'fa-eye');
        });
    }

    // === 4. VALIDAÇÕES DO FORMULÁRIO (SUBMIT) ===
    if (formCadastro) {
        formCadastro.addEventListener('submit', function (e) {
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const senha = inputSenha ? inputSenha.value : '';
            const telefone = inputTelefone ? inputTelefone.value : '';

            const senhaRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,64}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
            const digitosTel = telefone.replace(/\D/g, "").length;

            const bloquear = (icon, title) => {
                e.preventDefault();
                Toast.fire({ icon, title });
            };

            if (!nome || !email || !senha || !telefone) {
                bloquear('warning', "Por favor, preencha todos os campos.");
            } else if (nome.length > LIMITES.nome) {
                bloquear('warning', `O nome deve ter no máximo ${LIMITES.nome} caracteres.`);
            } else if (email.length > LIMITES.email) {
                bloquear('warning', `O e-mail deve ter no máximo ${LIMITES.email} caracteres.`);
            } else if (!emailRegex.test(email)) {
                bloquear('warning', "E-mail inválido. Ele precisa conter @ e um domínio (ex.: nome@email.com).");
            } else if (senha.length > LIMITES.senhaMax) {
                bloquear('warning', `A senha deve ter no máximo ${LIMITES.senhaMax} caracteres.`);
            } else if (!senhaRegex.test(senha)) {
                e.preventDefault();
                toastRequisitosSenha();
            } else if (digitosTel < 10) {
                bloquear('warning', "Telefone incompleto.");
            }
        });
    }
});