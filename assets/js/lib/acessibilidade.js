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
        fontSlider.value = fontValues[tamanhoSalvo] || 1;

        fontSlider.addEventListener("input", (e) => {
            const valor = e.target.value;
            const tamanhoSelecionado = fontSizes[valor];

            document.documentElement.classList.remove('font-normal', 'font-media', 'font-grande');
            document.documentElement.classList.add(`font-${tamanhoSelecionado}`);

            localStorage.setItem('site-font-size', tamanhoSelecionado);
        });
    }

    // Permite que os "ticks" mudem o valor do slider
    window.setSliderValue = function (val) {
        if (fontSlider) {
            fontSlider.value = val;
            fontSlider.dispatchEvent(new Event('input'));
        }
    };

    // OBS: o controle do Libras (switch) agora fica SOMENTE no vlibras.php
});