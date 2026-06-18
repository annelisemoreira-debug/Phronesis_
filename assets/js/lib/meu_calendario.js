// assets/js/lib/meu_calendario.js

// Mantém a injeção da classe no topo imediatamente para evitar o "flash" branco
if (localStorage.getItem('site-theme') === 'dark') {
    document.documentElement.classList.add('dark-theme');
}

// Configuração do SweetAlert (Toast)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true
});

document.addEventListener("DOMContentLoaded", function() {
    const btnConectei = document.getElementById('btn-ja-conectei');

    if (btnConectei) {
        btnConectei.addEventListener('click', function(e) {
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