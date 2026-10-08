// assets/js/lib/livros.js

// === INJEÇÃO IMEDIATA (EVITA FLASH BRANCO E DE FONTE) ===
const temaSalvo = localStorage.getItem('site-theme') || 'light';
document.documentElement.classList.toggle('dark-theme', temaSalvo === 'dark');

const tamanhoSalvo = localStorage.getItem('site-font-size') || 'normal';
document.documentElement.classList.remove('font-normal', 'font-media', 'font-grande');
document.documentElement.classList.add(`font-${tamanhoSalvo}`);

document.addEventListener('DOMContentLoaded', function () {

    const cards = [...document.querySelectorAll('.livro-card')];
    const vazio = document.getElementById('semResultados');
    const campoBusca = document.getElementById('busca');
    const filtros = { status: 'todas', vest: 'todos', q: '' };

    // === 1. FILTROS (status, vestibular, busca) ===
    function passaStatus(card) {
        const st = card.dataset.status;
        switch (filtros.status) {
            case 'andamento':     return st === 'em_andamento';
            case 'concluidas':    return st === 'concluida';
            case 'nao_iniciadas': return !st;
            case 'favoritas':     return card.dataset.fav === '1';
            default:              return true;
        }
    }

    function aplicarFiltros() {
        let visiveis = 0;
        cards.forEach(card => {
            const okVest = filtros.vest === 'todos' || card.dataset.vests.split(' ').includes(filtros.vest);
            const okBusca = !filtros.q || card.dataset.busca.includes(filtros.q);
            const mostrar = passaStatus(card) && okVest && okBusca;
            card.hidden = !mostrar;
            if (mostrar) visiveis++;
        });
        if (cards.length > 0) {
            vazio.hidden = visiveis > 0;
            vazio.textContent = 'Nenhuma obra encontrada com esses filtros.';
        }
    }

    document.querySelectorAll('[data-status-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            filtros.status = btn.dataset.statusFilter;
            // sincroniza abas e menu lateral
            document.querySelectorAll('[data-status-filter]').forEach(b =>
                b.classList.toggle('ativo', b.dataset.statusFilter === filtros.status));
            aplicarFiltros();
        });
    });

    document.querySelectorAll('[data-vest-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            filtros.vest = btn.dataset.vestFilter;
            document.querySelectorAll('[data-vest-filter]').forEach(b =>
                b.classList.toggle('ativo', b === btn));
            aplicarFiltros();
        });
    });

    campoBusca.addEventListener('input', () => {
        filtros.q = campoBusca.value.trim().toLowerCase();
        aplicarFiltros();
    });

    // === 2. COMUNICAÇÃO COM O SERVIDOR ===
    async function enviar(dados) {
        const fd = new FormData();
        Object.entries(dados).forEach(([k, v]) => fd.append(k, v));
        const r = await fetch('livros_acao.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const json = await r.json();
        if (!r.ok || !json.ok) throw new Error(json.erro || 'Falha na requisição');
        return json;
    }

    function atualizarCard(card) {
        card.classList.remove('st-em_andamento', 'st-concluida');
        if (card.dataset.status) card.classList.add('st-' + card.dataset.status);
        card.classList.toggle('is-fav', card.dataset.fav === '1');
    }

    async function alternarFavorito(card) {
        const r = await enviar({ acao: 'favoritar', id_livro: card.dataset.id });
        card.dataset.fav = r.favorito ? '1' : '0';
        atualizarCard(card);
        aplicarFiltros(); // se estiver na aba Favoritos, o card some ao desfavoritar
    }

    async function definirStatus(card, status) {
        const r = await enviar({ acao: 'progresso', id_livro: card.dataset.id, status });
        card.dataset.status = r.status;
        atualizarCard(card);
        aplicarFiltros();
    }

    // === 3. CARDS ===
    cards.forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.btn-fav')) return;
            abrirModal(card);
        });
        card.addEventListener('keydown', (e) => {
            if (e.target !== card) return;
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); abrirModal(card); }
        });
        card.querySelector('.btn-fav').addEventListener('click', async (e) => {
            e.stopPropagation();
            try { await alternarFavorito(card); } catch (err) { console.error(err); }
        });
    });

    // === 4. MODAL ===
    const modal = document.getElementById('modalLivro');
    const mCapa = document.getElementById('mCapa');
    const mTitulo = document.getElementById('mTitulo');
    const mAutor = document.getElementById('mAutor');
    const mTags = document.getElementById('mTags');
    const mResumo = document.getElementById('mResumo');
    const mFav = document.getElementById('mFav');
    const mErro = document.getElementById('mErro');
    let cardAberto = null;

    function sincronizarModal() {
        if (!cardAberto) return;
        modal.querySelectorAll('[data-set-status]').forEach(b =>
            b.classList.toggle('ativo', b.dataset.setStatus === (cardAberto.dataset.status || 'nao_iniciada')));
        const fav = cardAberto.dataset.fav === '1';
        mFav.classList.toggle('ativo', fav);
        mFav.querySelector('span').textContent = fav ? 'Favoritado' : 'Favoritar';
    }

    function abrirModal(card) {
        cardAberto = card;
        mErro.hidden = true;

        const img = card.querySelector('.livro-capa img');
        mCapa.innerHTML = img ? `<img src="${img.src}" alt="">` : '<i class="fa-solid fa-book"></i>';

        mTitulo.textContent = card.querySelector('.livro-titulo').textContent;
        mAutor.textContent = card.querySelector('.livro-autor').textContent;
        mTags.innerHTML = card.querySelector('.livro-tags').innerHTML;
        mResumo.innerHTML = card.querySelector('.livro-resumo').innerHTML;

        sincronizarModal();
        modal.showModal();
    }

    modal.querySelectorAll('[data-set-status]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!cardAberto) return;
            try {
                mErro.hidden = true;
                await definirStatus(cardAberto, btn.dataset.setStatus);
                sincronizarModal();
            } catch (err) { mErro.hidden = false; }
        });
    });

    mFav.addEventListener('click', async () => {
        if (!cardAberto) return;
        try {
            mErro.hidden = true;
            await alternarFavorito(cardAberto);
            sincronizarModal();
        } catch (err) { mErro.hidden = false; }
    });

    modal.querySelector('.close-button').addEventListener('click', () => modal.close());
    modal.addEventListener('click', (e) => {
        const r = modal.getBoundingClientRect();
        if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) modal.close();
    });

    // === 5. CONTAGEM REGRESSIVA ===
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
});

    // === 6. MENU LATERAL (DRAWER) NO CELULAR ===
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btnFechar = document.getElementById('btnFecharSidebar');
    const ehCelular = () => window.matchMedia('(max-width: 768px)').matches;

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

    // Intercepta o hambúrguer do header (só no celular) e abre a sidebar
    document.addEventListener('click', (e) => {
        if (!ehCelular() || !e.target.closest('#hamburgerBtn')) return;
        e.stopPropagation();
        sidebar.classList.contains('active') ? fecharMenu() : abrirMenu();
    }, true);

    overlay.addEventListener('click', fecharMenu);
    btnFechar.addEventListener('click', fecharMenu);

    // Ao escolher um filtro no menu, ele fecha
    sidebar.querySelectorAll('[data-status-filter], [data-vest-filter]').forEach(b =>
        b.addEventListener('click', () => { if (ehCelular()) fecharMenu(); }));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) fecharMenu();
    });

    // Se a tela crescer até o PC/tablet, fecha o menu
    window.addEventListener('resize', () => { if (!ehCelular()) fecharMenu(); });