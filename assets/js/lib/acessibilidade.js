// assets/js/lib/acessibilidade.js

// === 1. INJEÇÃO IMEDIATA DO TEMA (EVITA O FLASH BRANCO) ===
const temaSalvo = localStorage.getItem('site-theme') || 'light';
if (temaSalvo === 'dark') {
    document.documentElement.classList.add('dark-theme');
} else {
    document.documentElement.classList.remove('dark-theme');
}

// --- INJEÇÃO IMEDIATA DO TAMANHO DA FONTE ---
const tamanhoSalvo = localStorage.getItem('site-font-size') || 'normal';
document.documentElement.classList.add(`font-${tamanhoSalvo}`);

document.addEventListener("DOMContentLoaded", () => {
    const btnLight = document.getElementById("btn-light-theme");
    const btnDark = document.getElementById("btn-dark-theme");
    const toggleLibras = document.getElementById("toggle-libras");
    const fontSlider = document.getElementById("font-slider");

    // --- GERENCIAMENTO DE TEMA (FUNÇÃO VISUAL) ---
    function alternarTema(tema) {
        if (tema === 'dark') {
            document.documentElement.classList.add('dark-theme');
            if (btnDark) btnDark.classList.add('active-card');
            if (btnLight) btnLight.classList.remove('active-card');
        } else {
            document.documentElement.classList.remove('dark-theme');
            if (btnLight) btnLight.classList.add('active-card');
            if (btnDark) btnDark.classList.remove('active-card');
        }
        localStorage.setItem('site-theme', tema);
    }

    // Sincroniza o estado visual dos cards/botões na tela
    alternarTema(temaSalvo);

    // Escutadores usando 'pointerdown' e 'click'
    if (btnLight) {
        btnLight.addEventListener("pointerdown", () => alternarTema('light'));
        btnLight.addEventListener("click", () => alternarTema('light'));
    }

    if (btnDark) {
        btnDark.addEventListener("pointerdown", () => alternarTema('dark'));
        btnDark.addEventListener("click", () => alternarTema('dark'));
    }

    // --- GERENCIAMENTO DE TAMANHO DE FONTE ---
    const fontSizes = { 1: 'normal', 2: 'media', 3: 'grande' };
    const fontValues = { 'normal': 1, 'media': 2, 'grande': 3 };

    if (fontSlider) {
        // Define o slider na posição correta baseado no que estava salvo
        fontSlider.value = fontValues[tamanhoSalvo] || 1;

        fontSlider.addEventListener("input", (e) => {
            const valor = e.target.value;
            const tamanhoSelecionado = fontSizes[valor];

            // Altera as classes na tag html raiz
            document.documentElement.classList.remove('font-normal', 'font-media', 'font-grande');
            document.documentElement.classList.add(`font-${tamanhoSelecionado}`);

            // Salva a preferência
            localStorage.setItem('site-font-size', tamanhoSelecionado);
        });
    }

    // Permite que os "ticks" (linhas clicáveis) mudem o valor do slider
    window.setSliderValue = function(val) {
        if (fontSlider) {
            fontSlider.value = val;
            fontSlider.dispatchEvent(new Event('input'));
        }
    }

    // --- 2. INTEGRAÇÃO E CONTROLE DO VLIBRAS ---
    const librasAtivo = localStorage.getItem('vlibras-active') === 'true';
    if (toggleLibras) {
        toggleLibras.checked = librasAtivo;
    }

    if (toggleLibras) {
        toggleLibras.addEventListener("change", (e) => {
            const ativo = e.target.checked;
            localStorage.setItem('vlibras-active', ativo);

            const vlibrasWidgetButton = document.querySelector('[vw-access-button]') || document.querySelector('.vw-access-button');

            if (ativo) {
                if (vlibrasWidgetButton && !document.querySelector('.expanded')) {
                    vlibrasWidgetButton.click();
                }
            } else {
                window.location.reload(); 
            }
        });
    }
    
});