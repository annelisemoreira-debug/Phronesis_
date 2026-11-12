// assets/js/lib/cronograma.js

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
(function () {
    const savedTheme = localStorage.getItem('site-theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme');
    }
})();

// ===== MENU LATERAL (DRAWER) NO CELULAR E TABLET =====
(function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btnFechar = document.getElementById('btnFecharSidebar');
    if (!sidebar || !overlay || !btnFechar) return;

    const ehTelaMenor = () => window.matchMedia('(max-width: 1024px)').matches; // celular + tablet

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

    document.getElementById('btnAbrirSidebar')?.addEventListener('click', abrirMenu);

    overlay.addEventListener('click', fecharMenu);
    btnFechar.addEventListener('click', fecharMenu);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) fecharMenu();
    });
    window.addEventListener('resize', () => { if (!ehTelaMenor()) fecharMenu(); });
})();

// ===== DIAS DA SEMANA + MATÉRIAS POR DIA =====
let MATERIAS = [];
let materiasOcultas = new Set(); // ids ocultados pelo filtro do menu hambúrguer

/** Lê o estado atual (do DOM, se já renderizado; senão, dos data-attributes do PHP) */
function obterEstadoDias() {
    const container = document.getElementById('containerDiasSemana');
    const estado = { dias: [], materias: {}, abertos: [] };
    if (!container) return estado;

    try { MATERIAS = JSON.parse(container.dataset.materias || '[]'); } catch (e) { MATERIAS = []; }

    const checksDia = container.querySelectorAll('input[name="dias_semana[]"]');

    // Já renderizado: preserva o que o usuário marcou
    if (checksDia.length) {
        checksDia.forEach(c => { if (c.checked) estado.dias.push(Number(c.value)); });
        container.querySelectorAll('.materia-dia-check:checked').forEach(c => {
            const d = c.dataset.dia;
            (estado.materias[d] = estado.materias[d] || []).push(Number(c.value));
        });
        container.querySelectorAll('.dia-item.aberto').forEach(i => estado.abertos.push(Number(i.dataset.dia)));
        return estado;
    }

    // Primeira renderização: dados vindos do PHP
    try {
        estado.dias = JSON.parse(container.dataset.selecionados || '[]').map(Number);
        estado.materias = JSON.parse(container.dataset.materiasDia || '{}') || {};
    } catch (e) {
        console.error('Erro ao processar dados do PHP:', e);
        estado.dias = []; // por padrão, todos os dias começam desmarcados
    }
    return estado;
}

function atualizarDiasSemana() {
    const inputInicio = document.getElementById('data_inicio')?.value;
    const inputFim = document.getElementById('data_fim')?.value;
    const container = document.getElementById('containerDiasSemana');
    if (!container) return;

    const estado = obterEstadoDias();

    // Datas incompletas: sem restrição de dias
    if (!inputInicio || !inputFim || inputInicio.length !== 10 || inputFim.length !== 10) {
        renderizarDias(estado, null);
        return;
    }

    const d1 = new Date(inputInicio + 'T00:00:00');
    const d2 = new Date(inputFim + 'T00:00:00');

    if (d1.getFullYear() < 1000 || d2.getFullYear() < 1000 || d2 < d1) {
        renderizarDias(estado, null);
        return;
    }

    const diffDias = Math.ceil(Math.abs(d2 - d1) / (1000 * 60 * 60 * 24));

    // Intervalo menor que 6 dias: bloqueia os dias fora do período
    if (diffDias < 6) {
        const diasValidos = [];
        const dataVar = new Date(d1.getTime());
        for (let i = 0; i <= diffDias; i++) {
            const n = dataVar.getDay();
            const diaIso = n === 0 ? 7 : n; // Domingo de 0 para 7
            if (!diasValidos.includes(diaIso)) diasValidos.push(diaIso);
            dataVar.setDate(dataVar.getDate() + 1);
        }
        renderizarDias(estado, diasValidos);
    } else {
        renderizarDias(estado, null);
    }
}

function renderizarDias(estado, filtrados) {
    const container = document.getElementById('containerDiasSemana');
    if (!container) return;
    container.innerHTML = '';

    DIAS_MAP.forEach(dia => {
        const disponivel = filtrados === null || filtrados.includes(dia.valor);
        const marcado = disponivel && estado.dias.includes(dia.valor);
        const idsDoDia = (estado.materias[dia.valor] || []).map(Number);
        const aberto = disponivel && estado.abertos.includes(dia.valor);

        const item = document.createElement('div');
        item.className = 'dia-item';
        item.dataset.dia = dia.valor;
        if (!disponivel) item.classList.add('dia-desabilitado');
        if (aberto) item.classList.add('aberto');

        // ----- Cabeçalho: nome | resumo | checkbox | seta -----
        const header = document.createElement('div');
        header.className = 'dia-header';

        const nome = document.createElement('label');
        nome.className = 'dia-nome';
        nome.htmlFor = `dia-chk-${dia.valor}`;
        nome.textContent = dia.nome;

        const resumo = document.createElement('span');
        resumo.className = 'dia-resumo';

        const chkDia = document.createElement('input');
        chkDia.type = 'checkbox';
        chkDia.id = `dia-chk-${dia.valor}`;
        chkDia.name = 'dias_semana[]';
        chkDia.value = dia.valor;
        chkDia.className = 'custom-checkbox';
        chkDia.checked = marcado;
        chkDia.disabled = !disponivel;

        const btnSeta = document.createElement('button');
        btnSeta.type = 'button';
        btnSeta.className = 'dia-toggle';
        btnSeta.setAttribute('aria-label', `Escolher matérias de ${dia.nome}`);
        btnSeta.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        btnSeta.disabled = !disponivel;
        btnSeta.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';

        header.append(nome, resumo, chkDia, btnSeta);

        // ----- Lista de matérias do dia -----
        const lista = document.createElement('div');
        lista.className = 'dia-materias';

        if (!MATERIAS.length) {
            const vazio = document.createElement('div');
            vazio.className = 'dia-materias-vazio';
            vazio.textContent = 'Nenhuma matéria cadastrada pelo administrador.';
            lista.appendChild(vazio);
        } else {
            MATERIAS.forEach(m => {
                const oculta = materiasOcultas.has(String(m.id));

                const row = document.createElement('label');
                row.className = 'materia-dia-row';
                row.dataset.materiaId = m.id;
                if (oculta) row.style.display = 'none';

                const span = document.createElement('span');
                span.textContent = m.nome;

                const chk = document.createElement('input');
                chk.type = 'checkbox';
                chk.name = `materias_dia[${dia.valor}][]`;
                chk.value = m.id;
                chk.className = 'custom-checkbox materia-dia-check';
                chk.dataset.dia = dia.valor;
                chk.checked = !oculta && idsDoDia.includes(Number(m.id));
                chk.disabled = !disponivel;

                row.append(span, chk);
                lista.appendChild(row);
            });
        }

        item.append(header, lista);
        container.appendChild(item);
        atualizarResumoDia(item);
    });
}

/** Atualiza o texto "N matérias" e o destaque de erro do dia */
function atualizarResumoDia(item) {
    const n = item.querySelectorAll('.materia-dia-check:checked').length;
    const diaMarcado = item.querySelector('input[name="dias_semana[]"]').checked;
    const resumo = item.querySelector('.dia-resumo');
    resumo.textContent = n === 0 ? 'Nenhuma matéria' : `${n} matéria${n > 1 ? 's' : ''}`;
    resumo.classList.toggle('vazio', diaMarcado && n === 0);
}

function alternarDia(item, forcarAberto) {
    const abrir = forcarAberto ?? !item.classList.contains('aberto');
    item.classList.toggle('aberto', abrir);
    item.querySelector('.dia-toggle')?.setAttribute('aria-expanded', abrir ? 'true' : 'false');
}

// ===== LOADING (EDIÇÃO / EXCLUSÃO) =====
function dispararLoadingEdicao(event, url) {
    event.preventDefault();
    document.getElementById('loadingText').innerText = "🛠️ Carregando dados para edição...";
    document.getElementById('loadingSubtext').innerText = "Buscando parâmetros do seu cronograma no banco de dados.";
    document.getElementById('loadingScreen').style.display = "flex";
    window.location.href = url;
}

function dispararLoadingExclusao(url) {
    document.getElementById('loadingText').innerText = "🗑️ Removendo cronograma...";
    document.getElementById('loadingSubtext').innerText = "Limpando os eventos vinculados ao seu Google Agenda.";
    document.getElementById('loadingScreen').style.display = "flex";
    window.location.href = url;
}

/**
 * Limita a lista de cronogramas salvos: mostra 5 no PC e 3 no tablet/celular.
 * Do 6º (PC) ou do 4º (tablet/celular) em diante, a lista passa a rolar.
 */
const LIMITE_HISTORICO_PC = 5;
const LIMITE_HISTORICO_MOBILE = 3;

function ajustarAlturaHistorico() {
    const lista = document.querySelector('.history-list');
    if (!lista) return;

    const itens = lista.querySelectorAll('.cronograma-item');
    const ehMobile = window.matchMedia('(max-width: 1024px)').matches;
    const limite = ehMobile ? LIMITE_HISTORICO_MOBILE : LIMITE_HISTORICO_PC;

    lista.style.maxHeight = '';
    lista.scrollTop = 0;
    if (itens.length <= limite) return;

    const fimUltimoVisivel = itens[limite - 1].getBoundingClientRect().bottom;
    const topoLista = lista.getBoundingClientRect().top;
    lista.style.maxHeight = Math.ceil(fimUltimoVisivel - topoLista) + 'px';
}

/** Fecha o modal de confirmação mantendo tudo o que foi preenchido no formulário */
function fecharModalConfirmacao() {
    const modal = document.getElementById('confirmModal');
    if (modal) modal.style.display = 'none';
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
document.addEventListener("DOMContentLoaded", function () {

    // ----- Filtro de matérias pelo menu hambúrguer (esconde a matéria em todos os dias) -----
    const checksMenu = document.querySelectorAll('.mm-check');
    function aplicarFiltroMaterias() {
        materiasOcultas = new Set();
        checksMenu.forEach(chk => { if (!chk.checked) materiasOcultas.add(String(chk.value)); });

        document.querySelectorAll('#containerDiasSemana .materia-dia-row').forEach(row => {
            const oculta = materiasOcultas.has(String(row.dataset.materiaId));
            row.style.display = oculta ? 'none' : '';
            if (oculta) row.querySelector('input').checked = false;
        });
        document.querySelectorAll('#containerDiasSemana .dia-item').forEach(atualizarResumoDia);
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

    // ==========================================================================
    // LIMITAÇÃO DE DATAS (início / fim)
    // ==========================================================================
    const inputInicio = document.getElementById('data_inicio');
    const inputFim = document.getElementById('data_fim');

    const formatarData = (d) =>
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

    function avisoData(texto) {
        Swal.fire({
            icon: 'warning',
            title: 'Data inválida',
            text: texto,
            confirmButtonColor: '#7c3aed'
        });
    }

    function aplicarLimitesDatas(avisar) {
        if (!inputInicio || !inputFim) return;

        const hoje = inputInicio.min; // yyyy-mm-dd vindo do PHP
        inputInicio.classList.remove('input-erro');
        inputFim.classList.remove('input-erro');

        // Início não pode ser antes de hoje
        if (inputInicio.value && hoje && inputInicio.value < hoje) {
            inputInicio.value = hoje;
            inputInicio.classList.add('input-erro');
            if (avisar) avisoData('A data de início não pode ser anterior a hoje.');
        }

        if (inputInicio.value) {
            // Fim só pode ser a partir do início, e no máximo 1 ano depois
            inputFim.min = inputInicio.value;
            const limite = new Date(inputInicio.value + 'T00:00:00');
            limite.setDate(limite.getDate() + 366);
            inputFim.max = formatarData(limite);
        } else {
            inputFim.min = hoje;
            inputFim.removeAttribute('max');
        }

        // Fim menor que o início (ou acima do limite): bloqueia e limpa o campo
        if (inputFim.value && inputInicio.value) {
            const menorQueInicio = inputFim.value < inputInicio.value;
            const acimaDoLimite = inputFim.max && inputFim.value > inputFim.max;

            if (menorQueInicio || acimaDoLimite) {
                inputFim.value = '';
                inputFim.classList.add('input-erro');
                if (avisar) {
                    avisoData(menorQueInicio
                        ? 'A data final não pode ser menor que a data de início.'
                        : 'O período máximo de um cronograma é de 1 ano.');
                }
            }
        }
    }

    if (inputInicio) {
        inputInicio.addEventListener('change', () => {
            aplicarLimitesDatas(true);
            atualizarDiasSemana();
        });
    }
    if (inputFim) {
        inputFim.addEventListener('change', () => {
            aplicarLimitesDatas(true);
            atualizarDiasSemana();
        });
    }

    // ----- Dias da semana: expandir matérias / atualizar resumo (delegação de eventos) -----
    const containerDias = document.getElementById('containerDiasSemana');
    if (containerDias) {
        containerDias.addEventListener('click', (e) => {
            const btn = e.target.closest('.dia-toggle');
            if (btn) alternarDia(btn.closest('.dia-item'));
        });

        containerDias.addEventListener('change', (e) => {
            const item = e.target.closest('.dia-item');
            if (!item) return;

            // Escolheu matéria => o dia passa a ser estudado
            if (e.target.classList.contains('materia-dia-check') && e.target.checked) {
                item.querySelector('input[name="dias_semana[]"]').checked = true;
            }
            // Marcou o dia sem matérias: já abre a lista para escolher
            if (e.target.name === 'dias_semana[]' && e.target.checked &&
                !item.querySelector('.materia-dia-check:checked')) {
                alternarDia(item, true);
            }
            atualizarResumoDia(item);
        });
    }

    aplicarLimitesDatas(false); // aplica ao carregar a página
    atualizarDiasSemana();      // desenha os dias + matérias (usa os dados vindos do PHP)

    // ==========================================================================
    // VALIDAÇÃO ANTES DE ENVIAR (datas + dias/matérias + limite de 24h)
    // ==========================================================================
    const formCronograma = document.getElementById('formCronograma');

    if (formCronograma) {
        formCronograma.addEventListener('submit', function (e) {

            // --- Datas ---
            const di = formCronograma.querySelector('[name="data_inicio"]').value;
            const df = formCronograma.querySelector('[name="data_fim"]').value;

            if (!df || df < di) {
                e.preventDefault();
                if (inputFim) inputFim.classList.add('input-erro');
                avisoData('A data final não pode ser menor que a data de início.');
                return;
            }

            // --- Dias e matérias por dia ---
            const diasMarcados = [...formCronograma.querySelectorAll('input[name="dias_semana[]"]:checked')];
            if (!diasMarcados.length) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione um dia',
                    text: 'Marque ao menos um dia da semana.',
                    confirmButtonColor: '#7c3aed'
                });
                return;
            }

            let maxMateriasDia = 0;
            for (const chk of diasMarcados) {
                const item = chk.closest('.dia-item');
                const qtd = item.querySelectorAll('.materia-dia-check:checked').length;
                if (qtd === 0) {
                    e.preventDefault();
                    alternarDia(item, true);
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    Swal.fire({
                        icon: 'warning',
                        title: 'Faltam matérias',
                        text: `Escolha ao menos uma matéria para ${item.querySelector('.dia-nome').textContent}.`,
                        confirmButtonColor: '#7c3aed'
                    });
                    return;
                }
                maxMateriasDia = Math.max(maxMateriasDia, qtd);
            }

            // --- Limite de 24h (usa o dia com mais matérias) ---
            const horaInicio = formCronograma.querySelector('[name="hora_inicio"]').value;
            const horas = parseFloat(formCronograma.querySelector('[name="horas_estudo"]').value);
            const qtdMaterias = maxMateriasDia;

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
                erro = `O limite de horas permitidas é abaixo de 24h. Com ${Math.max(qtdMaterias, 1)} matéria(s) de ${horas}h começando às ${horaInicio}, o estudo terminaria às ${fimTxt} do dia seguinte. Reduza a duração, o número de matérias do dia ou adiante o horário de início.`;
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

    // Vincula elementos com classe .check-topico à função unificada de progresso
    document.querySelectorAll(".check-topico").forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            salvarProgressoTopico(this);
        });
    });

    // Altura da lista de cronogramas salvos (recalcula ao carregar e ao redimensionar)
    ajustarAlturaHistorico();
    window.addEventListener('load', ajustarAlturaHistorico);
    window.addEventListener('resize', ajustarAlturaHistorico);

    // Fecha o modal de confirmação com Esc ou clicando fora da caixa
    document.getElementById('confirmModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'confirmModal') fecharModalConfirmacao();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') fecharModalConfirmacao();
    });

    const modalForm = document.querySelector("#confirmModal form");
    const confirmModal = document.getElementById("confirmModal");
    const loadingScreen = document.getElementById("loadingScreen");

    if (modalForm) {
        modalForm.addEventListener("submit", function () {
            if (confirmModal) confirmModal.style.display = "none";
            document.getElementById('loadingText').innerText = "🗓️ Sincronizando com o Google Agenda...";
            document.getElementById('loadingSubtext').innerText = "Isso pode levar alguns segundos dependendo do tamanho do seu cronograma.";
            if (loadingScreen) loadingScreen.style.display = "flex";
        });
    }

    const btnToggleMaterias = document.getElementById('btnToggleMaterias');
    const conteinerMaterias = document.getElementById('conteinerMaterias');

    if (btnToggleMaterias && conteinerMaterias) {
        btnToggleMaterias.addEventListener('click', function () {
            const isVisible = conteinerMaterias.style.display === 'flex';
            conteinerMaterias.style.display = isVisible ? 'none' : 'flex';

            const icon = this.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (icon) {
                icon.classList.toggle('fa-chevron-down', isVisible);
                icon.classList.toggle('fa-chevron-up', !isVisible);
            }
        });
    }

    document.querySelectorAll('.materia-accordion-header').forEach(header => {
        header.addEventListener('click', function () {
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
                    if (item.classList.contains('active')) {
                        header.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 310);
            }
        });
    });
});

// Escala de fonte salva
(function () {
    const savedSize = localStorage.getItem('site-font-size') || 'normal';
    document.documentElement.classList.add('font-' + savedSize);
})();