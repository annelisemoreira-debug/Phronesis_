// assets/js/lib/index.js

// =========================================================================
// 1. TEMA E TAMANHO DA FONTE (executa imediatamente, antes de pintar a página)
// =========================================================================
(function () {
    // Aplica o tema escuro se estiver salvo
    const savedTheme = localStorage.getItem('site-theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme');
    }

    // Aplica o tamanho da fonte salvo
    const savedSize = localStorage.getItem('site-font-size') || 'normal';
    document.documentElement.classList.add('font-' + savedSize);
})();

// =========================================================================
// 2. AVISO DE CONTA EXCLUÍDA (vem da página de perfil via sessionStorage)
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    let excluida = false;

    try {
        excluida = sessionStorage.getItem('conta_excluida') === '1';
        sessionStorage.removeItem('conta_excluida'); // mostra só uma vez
    } catch (e) {}

    if (!excluida || typeof Swal === 'undefined') return;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Conta excluída com sucesso.',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
});

// =========================================================================
// 3. CARROSSEL (só roda se existir na página)
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    const track = document.querySelector('.carousel-track');
    const nextBtn = document.querySelector('.next');
    const prevBtn = document.querySelector('.prev');

    // Sem carrossel na página: não faz nada
    if (!track || !nextBtn || !prevBtn) return;

    // Próximo: move o primeiro card para o fim da fila
    nextBtn.addEventListener('click', () => {
        const cards = document.querySelectorAll('.card');
        track.appendChild(cards[0]);
    });

    // Anterior: move o último card para o início da fila
    prevBtn.addEventListener('click', () => {
        const cards = document.querySelectorAll('.card');
        track.insertBefore(cards[cards.length - 1], cards[0]);
    });
});