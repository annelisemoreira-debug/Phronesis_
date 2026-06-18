 document.addEventListener("DOMContentLoaded", function() {
        // --- Lógica do Carrossel ---
        const track = document.querySelector('.carousel-track');
        const nextBtn = document.querySelector('.next');
        const prevBtn = document.querySelector('.prev');

        if (nextBtn && prevBtn) {
            nextBtn.addEventListener('click', () => {
                const cards = document.querySelectorAll('.card');
                track.appendChild(cards[0]);
            });
            prevBtn.addEventListener('click', () => {
                const cards = document.querySelectorAll('.card');
                track.insertBefore(cards[cards.length - 1], cards[0]);
            });
        }
    });


    (function () {
        // Aplica o Tema Escuro imediatamente se estiver salvo
        const savedTheme = localStorage.getItem('site-theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-theme');
        }

        // Aplica o Tamanho da Fonte imediatamente se estiver salvo
        const savedSize = localStorage.getItem('site-font-size') || 'normal';
        document.documentElement.classList.add('font-' + savedSize);
    })();
