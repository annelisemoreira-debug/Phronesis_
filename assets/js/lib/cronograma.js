// js/scripts.js

const DIAS_MAP = [
    { nome: 'Segunda-Feira', valor: 1 },
    { nome: 'Terça-Feira', valor: 2 },
    { nome: 'Quarta-Feira', valor: 3 },
    { nome: 'Quinta-Feira', valor: 4 },
    { nome: 'Sexta-Feira', valor: 5 },
    { nome: 'Sábado', valor: 6 },
    { nome: 'Domingo', valor: 7 }
];

// Executa imediatamente para definir o tema (evita o "flash" branco no tema escuro)
(function() {
    const savedTheme = localStorage.getItem('site-theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme');
    }

    
})();

// ===== MENU LATERAL (DRAWER) NO CELULAR =====
(function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btnFechar = document.getElementById('btnFecharSidebar');
    if (!sidebar || !overlay || !btnFechar) return;

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

    // Intercepta o hambúrguer do header (só no celular)
    document.addEventListener('click', (e) => {
        if (!ehCelular() || !e.target.closest('#hamburgerBtn')) return;
        e.stopPropagation();
        sidebar.classList.contains('active') ? fecharMenu() : abrirMenu();
    }, true);

    overlay.addEventListener('click', fecharMenu);
    btnFechar.addEventListener('click', fecharMenu);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) fecharMenu();
    });
    window.addEventListener('resize', () => { if (!ehCelular()) fecharMenu(); });
})();

function obterDiasSelecionados() {
    const container = document.getElementById('containerDiasSemana');
    if (container && container.dataset.selecionados) {
        try {
            return JSON.parse(container.dataset.selecionados);
        } catch (e) {
            console.error("Erro ao processar dias selecionados do PHP:", e);
        }
    }
    return [1, 2, 3, 4, 5, 6, 7]; // Fallback padrão
}

function atualizarDiasSemana() {
    const inputInicio = document.getElementById('data_inicio')?.value;
    const inputFim = document.getElementById('data_fim')?.value;
    const container = document.getElementById('containerDiasSemana');
    
    if (!container) return;

    const diasSelecionadosAnteriormente = obterDiasSelecionados();

    // Se as datas não estiverem preenchidas corretamente, renderiza sem restrição (null)
    if (!inputInicio || !inputFim || inputInicio.length !== 10 || inputFim.length !== 10) {
        renderizarDias(diasSelecionadosAnteriormente, null);
        return;
    }

    const d1 = new Date(inputInicio + 'T00:00:00');
    const d2 = new Date(inputFim + 'T00:00:00');

    if (d1.getFullYear() < 1000 || d2.getFullYear() < 1000 || d2 < d1) {
        renderizarDias(diasSelecionadosAnteriormente, null);
        return;
    }

    const diffTempo = Math.abs(d2 - d1);
    const diffDias = Math.ceil(diffTempo / (1000 * 60 * 60 * 24));

    // Regra: Se o intervalo for menor que 6 dias, filtra e bloqueia os dias fora do range
    if (diffDias < 6) {
        let diasValidos = [];
        let dataVar = new Date(d1.getTime());

        for (let i = 0; i <= diffDias; i++) {
            let numDia = dataVar.getDay(); 
            let diaIso = numDia === 0 ? 7 : numDia; // Converte Domingo de 0 para 7
            if (!diasValidos.includes(diaIso)) {
                diasValidos.push(diaIso);
            }
            dataVar.setDate(dataVar.getDate() + 1);
        }
        renderizarDias(diasSelecionadosAnteriormente, diasValidos);
    } else {
        // Se for maior ou igual a 6 dias, todos os dias da semana ficam liberados
        renderizarDias(diasSelecionadosAnteriormente, null);
    }
}

function renderizarDias(selecionados, filtrados) {
    const container = document.getElementById('containerDiasSemana');
    if (!container) return;
    container.innerHTML = '';

    DIAS_MAP.forEach(dia => {
        const label = document.createElement('label');
        label.className = 'row-item';

        // Se o dia não estiver na lista de filtrados, ele será desabilitado
        const estaDisponivel = filtrados === null || filtrados.includes(dia.valor);
        if (!estaDisponivel) {
            label.classList.add('dia-desabilitado');
        }

        const span = document.createElement('span');
        span.textContent = dia.nome;

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'dias_semana[]';
        checkbox.value = dia.valor;
        checkbox.className = 'custom-checkbox';
        
        // Mantém marcado se estivesse selecionado anteriormente E estiver disponível
        if (estaDisponivel && selecionados.map(Number).includes(dia.valor)) {
            checkbox.checked = true;
        }
        
        // Bloqueia o input se ele não fizer parte do intervalo de datas
        if (!estaDisponivel) {
            checkbox.disabled = true;
            checkbox.checked = false; // Garante que dias fora do range não fiquem marcados
        }

        label.appendChild(span);
        label.appendChild(checkbox);
        container.appendChild(label);
    });
}

// Exibe tela de loading customizada para a Edição
function dispararLoadingEdicao(event, url) {
    event.preventDefault();
    document.getElementById('loadingText').innerText = "🛠️ Carregando dados para edição...";
    document.getElementById('loadingSubtext').innerText = "Buscando parâmetros do seu cronograma no banco de dados.";
    document.getElementById('loadingScreen').style.display = "flex";
    window.location.href = url;
}

// Exibe tela de loading customizada para a Exclusão
function dispararLoadingExclusao(url) {
    document.getElementById('loadingText').innerText = "🗑️ Removendo cronograma...";
    document.getElementById('loadingSubtext').innerText = "Limpando os eventos vinculados ao seu Google Agenda.";
    document.getElementById('loadingScreen').style.display = "flex";
    window.location.href = url;
}

function confirmDelete(event, id) {
    event.preventDefault();
    Swal.fire({
        title: 'Tem certeza?',
        text: "Isso removerá a agenda criada no Google Agenda definitivamente!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            dispararLoadingExclusao(`?deletar_salvo=${id}`);
        }
    });
}

/**
 * Abre e fecha as pastas da checklist
 */
function toggleChecklistFolder(idMateria) {
    const content = document.getElementById(`chk-content-${idMateria}`);
    const chevron = document.getElementById(`chk-chevron-${idMateria}`);
    
    if (content) {
        const isVisible = content.style.display === 'block';
        content.style.display = isVisible ? 'none' : 'block';
        
        if (chevron) {
            chevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

/**
 * Salva o progresso do tópico assincronamente via Fetch API
 */
function salvarProgressoTopico(element) {
    const idCronograma = element.getAttribute('data-cronograma');
    const idMateria = element.getAttribute('data-materia');
    const idTopico = element.getAttribute('data-topico');
    const status = element.checked ? 1 : 0;

    element.disabled = true;

    const formData = new FormData();
    formData.append('id_cronograma', idCronograma);
    formData.append('id_materia', idMateria);
    formData.append('id_topico', idTopico);
    formData.append('status', status);

    fetch('atualizar_progresso.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            // 1. Atualização via .materia-container (Layout Alternativo)
            const containerMateria = document.querySelector(`.materia-container[data-materia-id="${idMateria}"]`);
            if (containerMateria) {
                const textoPorcentagem = containerMateria.querySelector(".texto-porcentagem");
                const barraProgresso = containerMateria.querySelector(".barra-progresso");
                if (textoPorcentagem) textoPorcentagem.textContent = `${Math.round(data.porcentagemMateria)}%`;
                if (barraProgresso) barraProgresso.style.width = `${data.porcentagemMateria}%`;
            }

            // 2. Atualização via IDs diretos (Layout Padrão / Checklist)
            const barraMateria = document.getElementById(`chk-fill-${idMateria}`) || document.getElementById(`barra-materia-${idMateria}`);
            const textoMateria = document.getElementById(`chk-text-${idMateria}`) || document.getElementById(`texto-materia-${idMateria}`);
            
            if (barraMateria) barraMateria.style.width = data.porcentagemMateria + '%';
            if (textoMateria) textoMateria.textContent = Math.round(data.porcentagemMateria) + '%';

            // 3. Atualização da porcentagem Geral do Cronograma
            const barraGeral = document.getElementById(`barra-geral-${idCronograma}`);
            const textoGeral = document.getElementById("porcentagem-geral-cronograma") || document.getElementById(`texto-geral-${idCronograma}`);
            
            if (barraGeral) barraGeral.style.width = data.porcentagemGeral + '%';
            if (textoGeral) textoGeral.textContent = Math.round(data.porcentagemGeral) + '%';
        } else {
            element.checked = !element.checked;
            console.error("Erro no servidor: ", data.erro);
            Swal.fire('Oops!', 'Não foi possível salvar o progresso. Tente novamente.', 'error');
        }
    })
    .catch(error => {
        element.checked = !element.checked;
        console.error("Erro na requisição Fetch:", error);
        Swal.fire('Erro de Conexão', 'Houve um problema ao se comunicar com o servidor.', 'error');
    })
    .finally(() => {
        element.disabled = false;
    });
}

// Inicialização dos Event Listeners após o carregamento do DOM
document.addEventListener("DOMContentLoaded", function() {

    // ----- Dropdown de matérias (mobile) -----
const btnDrop = document.getElementById('btnDropMaterias');
const listaMat = document.getElementById('listaMaterias');
const labelMat = document.getElementById('labelMaterias');

function atualizarLabelMaterias() {
    if (!labelMat || !listaMat) return;
    const n = listaMat.querySelectorAll('input:checked').length;
    labelMat.textContent = n === 0
        ? 'Selecione as matérias que deseja estudar…'
        : `${n} matéria${n > 1 ? 's' : ''} selecionada${n > 1 ? 's' : ''}`;
}
if (btnDrop && listaMat) {
    btnDrop.addEventListener('click', () => {
        btnDrop.classList.toggle('aberto');
        listaMat.classList.toggle('aberto');
    });
    listaMat.addEventListener('change', atualizarLabelMaterias);
    atualizarLabelMaterias();
}

// ----- Filtro de matérias pelo menu hambúrguer -----
const checksMenu = document.querySelectorAll('.mm-check');
function aplicarFiltroMaterias() {
    checksMenu.forEach(chk => {
        const row = document.querySelector(`#listaMaterias [data-materia-id="${chk.value}"]`);
        if (!row) return;
        row.style.display = chk.checked ? '' : 'none';
        if (!chk.checked) row.querySelector('input').checked = false;
    });
    atualizarLabelMaterias();
}
checksMenu.forEach(chk => chk.addEventListener('change', aplicarFiltroMaterias));
document.getElementById('btnVerTodas')?.addEventListener('click', () => {
    checksMenu.forEach(c => c.checked = true);
    aplicarFiltroMaterias();
});

// ----- Visualização do Google Agenda (celular/tablet) -----
const iframeAgenda = document.querySelector('.calendar-embed');
const botoesModo = document.querySelectorAll('.view-toggle button');
if (iframeAgenda && botoesModo.length) {
    const base = iframeAgenda.src.replace(/&mode=\w+/, '');
    const definirModo = (modo) => {
        iframeAgenda.src = base + '&mode=' + modo;
        botoesModo.forEach(b => b.classList.toggle('ativo', b.dataset.mode === modo));
    };
    botoesModo.forEach(b => b.addEventListener('click', () => definirModo(b.dataset.mode)));
    // No celular o modo "Agenda" (lista) é bem mais legível que o mês
    definirModo(window.innerWidth < 700 ? 'AGENDA' : 'MONTH');
}

        // --- VALIDAÇÃO DO LIMITE DE 24H ANTES DE ENVIAR ---
    const formCronograma = document.getElementById('formCronograma');

    if (formCronograma) {
        formCronograma.addEventListener('submit', function (e) {
            const horaInicio = formCronograma.querySelector('[name="hora_inicio"]').value;
            const horas = parseFloat(formCronograma.querySelector('[name="horas_estudo"]').value);
            const qtdMaterias = formCronograma.querySelectorAll('input[name="materias_id[]"]:checked').length;

            if (!horaInicio || isNaN(horas)) return;

            const [h, m] = horaInicio.split(':').map(Number);
            const inicioMin = h * 60 + m;
            const totalMin = Math.round(horas * 60 * Math.max(qtdMaterias, 1));
            const fimMin = inicioMin + totalMin;

            let erro = null;

            if (horas <= 0) {
                erro = 'A duração deve ser maior que zero.';
            } else if (horas >= 24) {
                erro = 'O limite de horas permitidas é abaixo de 24h.';
            } else if (fimMin > 1440) {
                const fimReal = fimMin % 1440;
                const fimTxt = String(Math.floor(fimReal / 60)).padStart(2, '0') + ':' + String(fimReal % 60).padStart(2, '0');
                erro = `O limite de horas permitidas é abaixo de 24h. Com ${Math.max(qtdMaterias, 1)} matéria(s) de ${horas}h começando às ${horaInicio}, o estudo terminaria às ${fimTxt} do dia seguinte. Reduza a duração, o número de matérias ou adiante o horário de início.`;
            }

            if (erro) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Limite de horas excedido',
                    text: erro,
                    confirmButtonColor: '#7c3aed'
                });
            }
        });
    }
    const inputInicio = document.getElementById('data_inicio');
    const inputFim = document.getElementById('data_fim');
    
    if (inputInicio) inputInicio.addEventListener('change', atualizarDiasSemana);
    if (inputFim) inputFim.addEventListener('change', atualizarDiasSemana);

    // Roda uma vez no início para aplicar bloqueios se já houver datas vindas do PHP
    atualizarDiasSemana();

    // Vincula elementos com classe .check-topico à função unificada de progresso
    document.querySelectorAll(".check-topico").forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            salvarProgressoTopico(this);
        });
    });

    const modalForm = document.querySelector("#confirmModal form");
    const confirmModal = document.getElementById("confirmModal");
    const loadingScreen = document.getElementById("loadingScreen");

    if (modalForm) {
        modalForm.addEventListener("submit", function() {
            if (confirmModal) confirmModal.style.display = "none";
            document.getElementById('loadingText').innerText = "🗓️ Sincronizando com o Google Agenda...";
            document.getElementById('loadingSubtext').innerText = "Isso pode levar alguns segundos dependendo do tamanho do seu cronograma.";
            if (loadingScreen) loadingScreen.style.display = "flex";
        });
    }

    const btnToggleMaterias = document.getElementById('btnToggleMaterias');
    const conteinerMaterias = document.getElementById('conteinerMaterias');

    if (btnToggleMaterias && conteinerMaterias) {
        btnToggleMaterias.addEventListener('click', function() {
            const isVisible = conteinerMaterias.style.display === 'flex';
            conteinerMaterias.style.display = isVisible ? 'none' : 'flex';
            
            const icon = this.querySelector('.fa-chevron-down, .fa-chevron-up');
            if(icon) {
                icon.classList.toggle('fa-chevron-down', isVisible);
                icon.classList.toggle('fa-chevron-up', !isVisible);
            }
        });
    }

    document.querySelectorAll('.materia-accordion-header').forEach(header => {
        header.addEventListener('click', function() {
            const item = this.parentElement;
            item.classList.toggle('active');
            const content = item.querySelector('.materia-accordion-content');
            if (content) {
                if (item.classList.contains('active')) {
                    content.style.maxHeight = content.scrollHeight + "px";
                } else {
                    content.style.maxHeight = "0px";
                }
                
                setTimeout(() => {
                    if(item.classList.contains('active')) {
                        header.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 310);
            }
        });
    });
});

(function () {
    const savedSize = localStorage.getItem('site-font-size') || 'normal';
    document.documentElement.classList.add('font-' + savedSize);
})();