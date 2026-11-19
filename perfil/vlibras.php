<div vw class="enabled" id="vlibras-wrapper">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var KEY = 'phronesis-libras';
    var PLUGIN_SRC = 'https://vlibras.gov.br/app/vlibras-plugin.js';
    var APP_URL = 'https://vlibras.gov.br/app';
    var html = document.documentElement;

    function log() {
        try { console.log.apply(console, ['[Libras]'].concat([].slice.call(arguments))); } catch (e) {}
    }

    // 1. CSS forte: quando <html> tem "libras-off", o widget some
    if (!document.getElementById('libras-off-style')) {
        var st = document.createElement('style');
        st.id = 'libras-off-style';
        var base = 'html.libras-off.libras-off.libras-off ';
        st.textContent =
            base + '[vw],' +
            base + '[vw-access-button],' +
            base + '[vw-plugin-wrapper],' +
            base + '.vw-plugin-top-wrapper' +
            '{display:none !important;visibility:hidden !important;}';
        document.head.appendChild(st);
    }

    // 2. Preferência (padrão: ligado)
    function lerAtivo() {
        try {
            var v = localStorage.getItem(KEY);
            if (v === null) {
                localStorage.setItem(KEY, 'true');
                return true;
            }
            return v === 'true';
        } catch (e) {
            return true;
        }
    }

    function salvar(valor) {
        try { localStorage.setItem(KEY, valor ? 'true' : 'false'); } catch (e) {}
    }

        // --- Mantém a posição de rolagem (janela + card .content) ---
    var SCROLL_KEY = 'phronesis-libras-scroll';

    function posicaoAtual() {
        var c = document.querySelector('.content');
        return { w: window.scrollY, c: c ? c.scrollTop : 0 };
    }

    function aplicarPosicao(pos) {
        var c = document.querySelector('.content');
        if (c && Math.abs(c.scrollTop - pos.c) > 1) c.scrollTop = pos.c;
        if (Math.abs(window.scrollY - pos.w) > 1) window.scrollTo(window.scrollX, pos.w);
    }

    // Restaura várias vezes durante ~2s (o widget e as imagens mudam a altura
    // da página); para assim que a pessoa rolar por conta própria.
    function manterPosicao(pos) {
        var cancelado = false;
        var parar = function () { cancelado = true; };
        ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(function (ev) {
            window.addEventListener(ev, parar, { once: true, passive: true });
        });

        [0, 50, 150, 400, 800, 1400, 2000].forEach(function (ms) {
            setTimeout(function () {
                if (!cancelado) aplicarPosicao(pos);
            }, ms);
        });
    }

    // Depois de um reload: restaura a posição guardada antes dele
    (function restaurarAposReload() {
        var salva = null;
        try {
            salva = sessionStorage.getItem(SCROLL_KEY);
            sessionStorage.removeItem(SCROLL_KEY);
        } catch (e) {}
        if (!salva) return;

        var pos;
        try { pos = JSON.parse(salva); } catch (e) { return; }

        var iniciar = function () { manterPosicao(pos); };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', iniciar);
        } else {
            iniciar();
        }
        window.addEventListener('load', iniciar);
    })();
    
    // 3. Plugin: só é carregado se estiver ligado
    function pluginJaNaPagina() {
        return !!document.querySelector('script[src*="vlibras-plugin.js"]');
    }

    function carregarPlugin() {
        if (pluginJaNaPagina()) return;

        var s = document.createElement('script');
        s.src = PLUGIN_SRC;
        s.onload = function () {
            try {
                new window.VLibras.Widget(APP_URL);
                log('widget iniciado');
            } catch (err) {
                log('erro ao iniciar o widget:', err);
            }
        };
        s.onerror = function () {
            log('não foi possível carregar o plugin (rede ou bloqueador de anúncios?)');
        };
        document.body.appendChild(s);
    }

    // 4. Switch
    function sincronizarSwitch() {
        var toggle = document.getElementById('toggle-libras');
        if (toggle) toggle.checked = lerAtivo();
    }

    // 5. Aplica o estado atual
    function aplicar() {
        var ativo = lerAtivo();
        html.classList.toggle('libras-off', !ativo);
        if (ativo) carregarPlugin();
        sincronizarSwitch();
        log('estado:', ativo ? 'LIGADO' : 'DESLIGADO');
    }

    aplicar();

    // 6. Liga o evento do switch (só existe na página de acessibilidade)
    function ligarSwitch() {
        var toggle = document.getElementById('toggle-libras');
        if (!toggle) return;

        sincronizarSwitch();

        toggle.addEventListener('change', function (e) {
            var ativo = e.target.checked;
            var pos = posicaoAtual();          // guarda onde a pessoa está
            salvar(ativo);
            log('switch alterado para:', ativo ? 'LIGADO' : 'DESLIGADO');

            // Plugin já carregado não dá para descarregar: recarrega a página.
            // Antes do reload, guarda a posição para restaurar depois.
            if (!ativo && pluginJaNaPagina()) {
                try { sessionStorage.setItem(SCROLL_KEY, JSON.stringify(pos)); } catch (err) {}
                window.location.reload();
                return;
            }

            aplicar();
            manterPosicao(pos);                // evita o pulo ao iniciar o widget
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ligarSwitch);
    } else {
        ligarSwitch();
    }

    // Garante que nenhum outro script desmarque o switch depois
    window.addEventListener('load', function () {
        sincronizarSwitch();
        setTimeout(sincronizarSwitch, 300);
    });

    // 7. Sincroniza abas abertas e o botão "voltar"
    window.addEventListener('storage', function (e) {
        if (e.key === KEY) aplicar();
    });
    window.addEventListener('pageshow', aplicar);
})();
</script>