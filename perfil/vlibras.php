<div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');

    document.addEventListener("DOMContentLoaded", () => {
        const vLibrasWidget = document.querySelector('[vw]');
        const toggleLibras = document.getElementById("toggle-libras");

        // 1. Define Ligado por Padrão caso o utilizador nunca tenha acedido ao site
        if (localStorage.getItem('libras-active') === null) {
            localStorage.setItem('libras-active', 'true');
        }

        // 2. Controla a exibição inicial do bonequinho com base na preferência salva
        function aplicarPreferenciaLibras() {
            const librasStatus = localStorage.getItem('libras-active');
            if (vLibrasWidget) {
                if (librasStatus === 'true') {
                    vLibrasWidget.style.display = 'block';
                } else {
                    vLibrasWidget.style.display = 'none';
                }
            }
        }

        // Executa a verificação assim que a página carrega
        aplicarPreferenciaLibras();

        // 3. Se estivermos na página de configuração (onde o switch existe), sincroniza os cliques
        if (toggleLibras) {
            toggleLibras.checked = localStorage.getItem('libras-active') === 'true';
            
            toggleLibras.addEventListener("change", (e) => {
                localStorage.setItem('libras-active', e.target.checked);
                aplicarPreferenciaLibras();
            });
        }
    });
</script>