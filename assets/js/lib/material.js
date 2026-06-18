// assets/js/lib/material.js

// Mantém a injeção da classe no topo imediatamente para evitar o "flash" branco
if (localStorage.getItem('site-theme') === 'dark') {
    document.documentElement.classList.add('dark-theme');
}

// === 1. LÓGICA DO VÍDEO VISTO (MUDANÇA VISUAL FLUIDA E SELEÇÃO DE RELOAD) ===
function enviarDadosVisto(vId, card, seenBtn, titleCard, deveAtualizarPagina = true) {
    const formData = new FormData();
    formData.append('v_id', vId);

    // Altera visualmente na hora (Instatâneo para o usuário ver)
    if (titleCard) titleCard.classList.toggle('video-visto');
    if (seenBtn) {
        if (seenBtn.classList.contains('seen-off')) {
            seenBtn.classList.remove('seen-off');
            seenBtn.classList.add('seen-on');
        } else {
            seenBtn.classList.remove('seen-on');
            seenBtn.classList.add('seen-off');
        }
    }

    fetch('visto.php', { method: 'POST', body: formData })
        .then(response => { 
            // Se o clique veio do botão de check (✓), atualiza a página para consolidar.
            // Se veio do Link externo, NÃO atualiza a página para não quebrar a abertura da aba!
            if (deveAtualizarPagina) {
                location.reload(); 
            }
        })
        .catch(err => {
            console.error("Erro ao salvar visto:", err);
            // Em caso de erro estrito, reverte o visual
            if (titleCard) titleCard.classList.toggle('video-visto');
        });
}

// === LÓGICA DO FAVORITO ===
function toggleFav(vId, vTit, btn) {
    const formData = new FormData();
    formData.append('v_id', vId);
    formData.append('v_tit', vTit);

    fetch('fav.php', { method: 'POST', body: formData })
        .then(response => { location.reload(); })
        .catch(err => console.error("Erro no favorito:", err));
}

// === 2. LÓGICA DO CRONÔMETRO ===
function createTimer(id, targetDate) {
    const target = new Date(targetDate).getTime();
    const element = document.getElementById(id);
    if (!element) return;
    setInterval(() => {
        const now = new Date().getTime();
        const diff = target - now;
        if (diff <= 0) {
            element.innerHTML = "PROVA EM ANDAMENTO";
            return;
        }
        const d = Math.floor(diff / (1000 * 60 * 60 * 24));
        const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);
        element.innerHTML = `${d}d ${h.toString().padStart(2, '0')}h ${m.toString().padStart(2, '0')}min ${s.toString().padStart(2, '0')}s`;
    }, 1000);
}

// Inicialização dos cronômetros
createTimer("timer-enem", "Nov 1, 2026 13:00:00");
createTimer("timer-fuvest", "Nov 15, 2026 13:00:00");
createTimer("timer-unicamp", "Oct 18, 2026 13:00:00");
createTimer("timer-unesp", "Nov 15, 2026 13:00:00");

// === 3. CONTROLE DE CLIQUES APÓS O DOM CARREGAR ===
document.addEventListener("DOMContentLoaded", function() {
    const videoGrid = document.getElementById("videoGrid");
    
    const linksMaterias = document.querySelectorAll(".menuSidebar nav a");
    linksMaterias.forEach(link => {
        link.addEventListener("click", function() {
            if (videoGrid) { videoGrid.classList.add("loading"); }
        });
    });

    const searchForm = document.querySelector(".search-box form");
    if (searchForm) {
        searchForm.addEventListener("submit", function() {
            if (videoGrid) { videoGrid.classList.add("loading"); }
        });
    }

    // GERENCIADOR DINÂMICO DE INTERCEPTAÇÃO
    document.querySelectorAll('.video-card').forEach(card => {
        const seenBtn = card.querySelector('.seen-btn');
        const titleCard = card.querySelector('.video-title-card');
        const titleLink = card.querySelector('.link-detectavel');
        const videoId = card.getAttribute('data-id'); 

        if (!videoId) return;

        // Opção A: Clicar no botão de check manual (✓) -> Salva e Atualiza a página
        if (seenBtn) {
            seenBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                enviarDadosVisto(videoId, card, seenBtn, titleCard, true);
            });
        }

        // Opção B: Clicar no Link do título -> Salva silenciosamente e deixa ir pro YouTube
        if (titleLink) {
            titleLink.addEventListener('click', function(e) {
                // Se o vídeo já estiver visto, não precisa reenviar o comando de inserção
                if (!titleCard.classList.contains('video-visto')) {
                    // Passamos 'false' no final para NÃO dar reload na página atual!
                    enviarDadosVisto(videoId, card, seenBtn, titleCard, false);
                }
            });
        }
    });
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