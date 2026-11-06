// assets/js/lib/meu_calendario.js
// Tela de vínculo com o Google Agenda

// ==========================================================================
// 1. INJEÇÃO IMEDIATA DE TEMA E FONTE (evita "flash" branco e de fonte)
// ==========================================================================
const temaSalvo = localStorage.getItem('site-theme') || 'light';
document.documentElement.classList.toggle('dark-theme', temaSalvo === 'dark');

const tamanhoSalvo = localStorage.getItem('site-font-size') || 'normal';
document.documentElement.classList.remove('font-normal', 'font-media', 'font-grande');
document.documentElement.classList.add(`font-${tamanhoSalvo}`);

// ==========================================================================
// 2. CONFIGURAÇÃO DO SWEETALERT (aviso no canto da tela)
// ==========================================================================
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

// ==========================================================================
// 3. BOTÃO "JÁ CONECTEI"
// Só age se o botão existir na página: avisa o usuário caso ele tente
// continuar sem ter vinculado a conta Google.
// ==========================================================================
document.addEventListener("DOMContentLoaded", function () {
    const btnConectei = document.getElementById('btn-ja-conectei');

    if (btnConectei) {
        btnConectei.addEventListener('click', function (e) {
            // Lê o valor do atributo data-logado e converte para booleano
            const estaLogadoNoGoogle = btnConectei.dataset.logado === 'true';

            if (!estaLogadoNoGoogle) {
                e.preventDefault();
                Toast.fire({
                    icon: 'warning',
                    title: 'Você precisa clicar em "Vincular Google Agenda" primeiro!'
                });
            }
        });
    }
});