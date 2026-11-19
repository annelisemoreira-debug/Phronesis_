<script>
    // Decide ANTES de pintar a página se o aviso precisa aparecer
    (function () {
        try {
            if (!localStorage.getItem('site-cookie-consent')) {
                document.documentElement.classList.add('cookie-pendente');
            }
        } catch (e) {
            document.documentElement.classList.add('cookie-pendente');
        }
    })();
</script>

<style>
    /* ================= AVISO DE COOKIES ================= */
    .cookie-overlay {
        --ck-panel: #ffffff;
        --ck-ink: #3b0f7a;
        --ck-ink-soft: #7a4fd6;
        --ck-purple: #8654f3;
        --ck-purple-hover: #6d3fe0;
        --ck-gold: #f5b100;
        --ck-btn-text: #ffffff;

        display: none;
        position: fixed;
        inset: 0;
        z-index: 5000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(30, 10, 80, 0.55);
        -webkit-backdrop-filter: blur(4px);
        backdrop-filter: blur(4px);
        font-family: 'Open Sans', 'Segoe UI', Roboto, sans-serif;
        box-sizing: border-box;
    }

    html.dark-theme .cookie-overlay {
        --ck-panel: #1d1b26;
        --ck-ink: #f1ecff;
        --ck-ink-soft: #c3a9ff;
        --ck-purple: #bfa2ff;
        --ck-purple-hover: #d1bbff;
        --ck-gold: #f1c40f;
        --ck-btn-text: #1a1824;
        background: rgba(10, 8, 20, 0.7);
    }

    .cookie-overlay *,
    .cookie-overlay *::before,
    .cookie-overlay *::after {
        box-sizing: border-box;
    }

    html.cookie-pendente .cookie-overlay {
        display: flex;
    }

    /* Trava a rolagem enquanto não houver escolha */
    html.cookie-pendente,
    html.cookie-pendente body {
        overflow: hidden;
    }

    .cookie-card {
        position: relative;
        width: 100%;
        max-width: 480px;
        background: var(--ck-panel);
        border-radius: 24px;
        padding: 36px 32px 28px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(30, 10, 80, 0.35);
        animation: cookieEntrada 0.35s ease both;
    }

    .cookie-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        width: 52px;
        height: 5px;
        transform: translateX(-50%);
        border-radius: 0 0 4px 4px;
        background-color: var(--ck-gold);
    }

    .cookie-icone {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--ck-purple);
        color: var(--ck-btn-text);
    }

    .cookie-icone svg {
        width: 34px;
        height: 34px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .cookie-card h2 {
        color: var(--ck-ink);
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.3px;
        margin: 0 0 12px;
    }

    .cookie-card h2 .destaque {
        color: var(--ck-purple);
        text-decoration: underline wavy;
        text-decoration-thickness: 2px;
        text-underline-offset: 5px;
        text-decoration-skip-ink: none;
    }

    .cookie-card p {
        color: var(--ck-ink-soft);
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.55;
        margin: 0 0 24px;
    }

    .cookie-acoes {
        display: flex;
        gap: 12px;
    }

    .cookie-btn {
        flex: 1;
        font-family: inherit;
        font-size: 1rem;
        font-weight: 700;
        padding: 13px 20px;
        border-radius: 999px;
        cursor: pointer;
        border: 2px solid var(--ck-purple);
        transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
    }

    .cookie-btn:hover {
        transform: translateY(-2px);
    }

    .cookie-btn:focus-visible {
        outline: 3px solid var(--ck-gold);
        outline-offset: 2px;
    }

    .cookie-btn-pri {
        background-color: var(--ck-purple);
        color: var(--ck-btn-text);
        box-shadow: 0 8px 18px rgba(134, 84, 243, 0.3);
    }

    .cookie-btn-pri:hover {
        background-color: var(--ck-purple-hover);
        border-color: var(--ck-purple-hover);
    }

    .cookie-btn-sec {
        background-color: transparent;
        color: var(--ck-purple);
    }

    .cookie-btn-sec:hover {
        background-color: var(--ck-purple);
        color: var(--ck-btn-text);
    }

    @keyframes cookieEntrada {
        from { opacity: 0; transform: translateY(16px) scale(0.97); }
        to   { opacity: 1; transform: none; }
    }

    @media (max-width: 480px) {
        .cookie-overlay {
            align-items: flex-end;
            padding: 12px;
        }

        .cookie-card {
            padding: 32px 20px 20px;
            border-radius: 22px;
        }

        .cookie-card h2 {
            font-size: 1.3rem;
        }

        .cookie-acoes {
            flex-direction: column-reverse; /* "Aceitar" em cima */
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .cookie-card {
            animation: none;
        }

        .cookie-btn {
            transition: none;
        }
    }
</style>

<div class="cookie-overlay" id="cookieOverlay" role="dialog" aria-modal="true"
     aria-labelledby="cookieTitulo" aria-describedby="cookieTexto">
    <div class="cookie-card">
        <div class="cookie-icone" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M21 12.5A9 9 0 1 1 11.5 3a4 4 0 0 0 4.5 4.5 3 3 0 0 0 5 5z"/>
                <circle cx="9" cy="10" r="1"/>
                <circle cx="8.5" cy="15" r="1"/>
                <circle cx="14" cy="14.5" r="1"/>
            </svg>
        </div>
        <h2 id="cookieTitulo">Antes de começar, uma palavrinha sobre <span class="destaque">cookies</span></h2>
        <p id="cookieTexto">
            O Phronesis usa cookies e armazenamento do navegador para manter você conectado,
            lembrar o tema escuro e o tamanho da fonte e melhorar a sua experiência de estudo.
        </p>
        <div class="cookie-acoes">
            <button type="button" class="cookie-btn cookie-btn-sec" data-cookie="refused">Recusar</button>
            <button type="button" class="cookie-btn cookie-btn-pri" data-cookie="accepted">Aceitar</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var overlay = document.getElementById('cookieOverlay');
        var root = document.documentElement;

        // Já houve escolha: não faz nada
        if (!overlay || !root.classList.contains('cookie-pendente')) return;

        var botoes = overlay.querySelectorAll('.cookie-btn');
        var aceitar = overlay.querySelector('[data-cookie="accepted"]');

        // Bloqueia o resto da página (clique, Tab e leitores de tela)
        var bloqueados = Array.prototype.filter.call(document.body.children, function (el) {
            return el !== overlay && el.tagName !== 'SCRIPT' && el.tagName !== 'STYLE';
        });
        bloqueados.forEach(function (el) { el.setAttribute('inert', ''); });

        aceitar.focus();

        // Foco preso dentro do aviso; Esc não fecha
        function teclado(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                return;
            }
            if (e.key !== 'Tab') return;

            var primeiro = botoes[0];
            var ultimo = botoes[botoes.length - 1];

            if (e.shiftKey && document.activeElement === primeiro) {
                e.preventDefault();
                ultimo.focus();
            } else if (!e.shiftKey && document.activeElement === ultimo) {
                e.preventDefault();
                primeiro.focus();
            }
        }
        document.addEventListener('keydown', teclado);

        // Salva a escolha e libera o site (aceitar ou recusar abrem igual)
        function escolher(valor) {
            try {
                localStorage.setItem('site-cookie-consent', valor);
            } catch (e) {} // sem storage: o site abre mesmo assim

            root.classList.remove('cookie-pendente');
            bloqueados.forEach(function (el) { el.removeAttribute('inert'); });
            document.removeEventListener('keydown', teclado);
        }

        botoes.forEach(function (btn) {
            btn.addEventListener('click', function () {
                escolher(btn.getAttribute('data-cookie'));
            });
        });
    });
</script>