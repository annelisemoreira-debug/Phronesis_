// assets/js/lib/material.js
// Usado por material.php e favoritos.php

// === INJEÇÃO IMEDIATA (EVITA FLASH BRANCO E DE FONTE) ===
document.documentElement.classList.toggle('dark-theme', (localStorage.getItem('site-theme') || 'light') === 'dark');
const tamanhoSalvo = localStorage.getItem('site-font-size') || 'normal';
document.documentElement.classList.remove('font-normal', 'font-media', 'font-grande');
document.documentElement.classList.add('font-' + tamanhoSalvo);

document.addEventListener('DOMContentLoaded', function () {

    const ehFavoritos = document.body.dataset.pagina === 'favoritos';
    const ehTelaMenor = () => window.matchMedia('(max-width: 1024px)').matches; // celular + tablet

    // === 1. COMUNICAÇÃO COM O SERVIDOR ===
    async function enviar(url, dados, extra = {}) {
        const fd = new FormData();
        Object.entries(dados).forEach(([k, v]) => fd.append(k, v));
        const r = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', ...extra });
        const json = await r.json();
        if (!r.ok || !json.ok) throw new Error(json.erro || 'Falha na requisição');
        return json;
    }

    // Atualiza todos os cards com o mesmo vídeo (se houver mais de um na página)
    const cardsDo = id => [...document.querySelectorAll('.video-card')].filter(c => c.dataset.id === id);

    function marcarVisto(card, visto) {
        card.querySelector('.video-title-card').classList.toggle('video-visto', visto);
        const b = card.querySelector('.seen-btn');
        b.classList.toggle('seen-on', visto);
        b.classList.toggle('seen-off', !visto);
    }

    function marcarFav(card, fav) {
        const b = card.querySelector('.fav-btn');
        b.classList.toggle('star-on', fav);
        b.classList.toggle('star-off', !fav);
    }

    function verificarVazio() {
        if (!ehFavoritos) return;
        const msg = document.getElementById('semFavoritos');
        const total = document.querySelectorAll('#listaFavoritos .video-card').length;
        if (msg) msg.hidden = total > 0;
    }

    // === 2. CLIQUES NOS CARDS (delegação) ===
    document.addEventListener('click', async (e) => {

        // 2.1 Miniatura -> troca por player do YouTube
        const thumb = e.target.closest('.video-thumb');
        if (thumb) {
            const iframe = document.createElement('iframe');
            iframe.src = `https://www.youtube.com/embed/${encodeURIComponent(thumb.dataset.yt)}?autoplay=1&rel=0`;
            iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            iframe.allowFullscreen = true;
            iframe.title = 'Vídeo do YouTube';
            thumb.replaceChildren(iframe);
            thumb.classList.add('tocando');
            return;
        }

        // 2.2 Botão visto
        const seenBtn = e.target.closest('.seen-btn');
        if (seenBtn) {
            const card = seenBtn.closest('.video-card');
            const id = card.dataset.id;
            try {
                const r = await enviar('visto.php', { v_id: id });
                cardsDo(id).forEach(c => marcarVisto(c, r.status === 'adicionado'));
            } catch (err) { console.error('Erro ao salvar visto:', err); }
            return;
        }

        // 2.3 Botão favorito
        const favBtn = e.target.closest('.fav-btn');
        if (favBtn) {
            const card = favBtn.closest('.video-card');
            const id = card.dataset.id;
            try {
                const r = await enviar('fav.php', { v_id: id, v_tit: card.dataset.titulo });
                if (ehFavoritos && !r.favorito) {
                    card.remove();          // desfavoritou: some da lista de salvos
                    verificarVazio();
                } else {
                    cardsDo(id).forEach(c => marcarFav(c, r.favorito));
                }
            } catch (err) { console.error('Erro no favorito:', err); }
            return;
        }

        // 2.4 Link do título -> marca como visto silenciosamente e deixa abrir o YouTube
        const link = e.target.closest('.link-detectavel');
        if (link) {
            const card = link.closest('.video-card');
            const titulo = card.querySelector('.video-title-card');
            if (!titulo.classList.contains('video-visto')) {
                marcarVisto(card, true);
                enviar('visto.php', { v_id: card.dataset.id }, { keepalive: true })
                    .catch(() => marcarVisto(card, false));
            }
        }
    });

    // === 3. CONTAGEM REGRESSIVA ===
    const relogios = document.querySelectorAll('[data-alvo]');
    const dois = n => String(n).padStart(2, '0');

    function tick() {
        relogios.forEach(el => {
            const diff = new Date(el.dataset.alvo) - Date.now();
            if (isNaN(diff)) { el.textContent = '--'; return; }
            if (diff <= 0) { el.textContent = 'Já realizada'; return; }
            const s = Math.floor(diff / 1000);
            const d = Math.floor(s / 86400);
            const h = Math.floor((s % 86400) / 3600);
            const m = Math.floor((s % 3600) / 60);
            el.textContent = `${d}d ${dois(h)}h ${dois(m)}m ${dois(s % 60)}s`;
        });
    }
    tick();
    setInterval(tick, 1000);

    // === 4. MENU LATERAL (DRAWER) NO CELULAR E TABLET ===
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btnFechar = document.getElementById('btnFecharSidebar');

    function abrirMenu() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.classList.add('menu-open');
    }
    function fecharMenu() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('menu-open');
    }

    // Intercepta o hambúrguer do header (celular/tablet) e abre a sidebar
    document.addEventListener('click', (e) => {
        if (!ehTelaMenor() || !e.target.closest('#hamburgerBtn')) return;
        e.stopPropagation();
        sidebar.classList.contains('active') ? fecharMenu() : abrirMenu();
    }, true);

    overlay.addEventListener('click', fecharMenu);
    btnFechar.addEventListener('click', fecharMenu);
    sidebar.querySelectorAll('a').forEach(a =>
        a.addEventListener('click', () => { if (ehTelaMenor()) fecharMenu(); }));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) fecharMenu();
    });
    window.addEventListener('resize', () => { if (!ehTelaMenor()) fecharMenu(); });

        // === 5. ROLAGEM + "MOSTRAR MAIS" (material.php) ===
    const area = document.getElementById('videoScroll');
    const grade = document.getElementById('videoGrid');

    if (area && grade) {
        const LINHAS = 2;
        const cartoes = [...grade.querySelectorAll('.video-card')];
        const maisWrap = document.getElementById('maisWrap');
        const btnMais = document.getElementById('btnMais');
        const ehPC = () => window.matchMedia('(min-width: 1025px)').matches;

        // Vídeos por lote = colunas atuais x 2 linhas (3x2 PC, 2x2 tablet, 1x2 celular)
        const porLote = () =>
            getComputedStyle(grade).gridTemplateColumns.split(' ').length * LINHAS;

        let visiveis = 0;

        function render() {
            cartoes.forEach((c, i) => { c.hidden = i >= visiveis; });
            grade.classList.add('pronto');
            maisWrap.hidden = visiveis >= cartoes.length;
        }

        // No PC, limita a altura da área a exatamente 2 linhas de vídeos
        function ajustarAltura() {
            area.style.maxHeight = '';
            if (!ehPC()) return;
            const lote = porLote();
            if (cartoes.length <= lote) return;          // cabe tudo: sem rolagem
            const mostrados = cartoes.filter(c => !c.hidden);
            const ref = mostrados[Math.min(lote, mostrados.length) - 1];
            const altura = ref.getBoundingClientRect().bottom
                         - area.getBoundingClientRect().top + 8;
            area.style.maxHeight = altura + 'px';
        }

        function iniciar() {
            visiveis = Math.min(porLote(), cartoes.length);
            render();
            ajustarAltura();
        }

        btnMais.addEventListener('click', () => {
            const primeiroNovo = cartoes[visiveis];
            visiveis = Math.min(visiveis + porLote(), cartoes.length);
            render();
            if (ehPC() && primeiroNovo) {
                const topo = primeiroNovo.getBoundingClientRect().top
                           - area.getBoundingClientRect().top + area.scrollTop - 4;
                area.scrollTo({ top: topo, behavior: 'smooth' });
            }
        });

        let tRedim;
        window.addEventListener('resize', () => {
            clearTimeout(tRedim);
            tRedim = setTimeout(() => {
                // mantém o que já foi revelado, mas garante pelo menos 1 lote
                visiveis = Math.max(visiveis, Math.min(porLote(), cartoes.length));
                render();
                ajustarAltura();
            }, 150);
        });

        window.addEventListener('load', ajustarAltura);
        iniciar();
    }
});

