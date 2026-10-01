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
            salvar(ativo);
            log('switch alterado para:', ativo ? 'LIGADO' : 'DESLIGADO');

            // Plugin já carregado não dá para descarregar: recarrega a página.
            // Com a preferência desligada, ele nem é baixado.
            if (!ativo && pluginJaNaPagina()) {
                window.location.reload();
                return;
            }
            aplicar();
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