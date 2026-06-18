// assets/js/lib/livros.js

// === INJEÇÃO IMEDIATA (EVITA FLASH BRANCO E DE FONTE) ===
const temaSalvo = localStorage.getItem('site-theme') || 'light';
if (temaSalvo === 'dark') {
    document.documentElement.classList.add('dark-theme');
} else {
    document.documentElement.classList.remove('dark-theme');
}

const tamanhoSalvo = localStorage.getItem('site-font-size') || 'normal';
document.documentElement.classList.remove('font-normal', 'font-media', 'font-grande');
document.documentElement.classList.add(`font-${tamanhoSalvo}`);

document.addEventListener("DOMContentLoaded", function() {
    
    // === 1. LÓGICA DAS JANELAS MODAIS MÚLTIPLAS ===
    const containersVest = document.querySelectorAll('.containerVest');

    containersVest.forEach(container => {
        const triggers = container.querySelectorAll('.open-modal-trigger');
        const modal = container.querySelector('dialog');
        const btnClose = container.querySelector('.close-button');

        if (modal) {
            if (triggers.length > 0) {
                triggers.forEach(trigger => {
                    trigger.addEventListener("click", () => {
                        modal.showModal();
                    });
                });
            }

            if (btnClose) {
                btnClose.addEventListener("click", () => {
                    modal.close();
                });
            }

            // Fechar ao clicar fora do modal (no backdrop)
            modal.addEventListener("click", (e) => {
                const dialogDimensions = modal.getBoundingClientRect();
                if (
                    e.clientX < dialogDimensions.left ||
                    e.clientX > dialogDimensions.right ||
                    e.clientY < dialogDimensions.top ||
                    e.clientY > dialogDimensions.bottom
                ) {
                    modal.close();
                }
            });
        }
    });

    // === 2. TROCA DE IMAGENS DO MODO ESCURO ===
    if (localStorage.getItem('site-theme') === 'dark') {
        const fuv = document.getElementById('logoFuvest');
        const vun = document.getElementById('logoVunesp');
        const uni = document.getElementById('logoUnicamp');
        const ene = document.getElementById('logoEnem');

        if (fuv) fuv.src = "../assets/img/fuvestescuro.webp";
        if (vun) vun.src = "../assets/img/vunespescura.webp";
        if (uni) uni.src = "../assets/img/unicampescuro.webp";
        if (ene) ene.src = "../assets/img/enemescuro.webp";
    }
});